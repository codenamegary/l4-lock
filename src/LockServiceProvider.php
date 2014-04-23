<?php

namespace codenamegary\Lock;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\MessageBag;
use Illuminate\Cookie\CookieJar;

class LockServiceProvider extends ServiceProvider {
    
    protected $defer = false;
    
    /**
     * @var Illuminate\Cookie\CookieJar
     */
    protected $cookie;
    
    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;
    
    /**
     * @var Illuminate\Routing\Router
     */
    protected $router;
    
    /**
     * @var Illuminate\View\Environment
     */
    protected $view;
    
    public function register()
    {
        $this->package('codenamegary/l4-lock');
        $this->registerNamespaces();
        $this->registerRoutes();
        $this->registerValidator();
        $this->registerSessionManager();
        $this->registerSessionDriver();
    }
    
    public function boot()
    {
        $this->package('codenamegary/l4-lock');
        $this->registerNamespaces();
        $this->registerLock();
        $this->registerFilter();
        $this->configureFilters();
        $this->registerMiddleware();
    }
    
    protected function registerMiddleware()
    {
        $this->app->bindShared('l4-lock.middleware', function($app){
           return new Middleware($app, $app['l4-lock.session']);
        });
        $this->app->middleware($this->app['l4-lock.middleware']);
    }
    
    protected function registerNamespaces()
    {
        $this->app['config']->addNamespace('l4-lock', app_path('config/packages/codenamegary/l4-lock'));
        $this->app['view']->addNamespace('l4-lock', __DIR__ . '/views');
    }
    
    public function registerRoutes()
    {
        $this->app['router']->get($this->app['config']->get('l4-lock::config.lock.urls.login'), array(
            'as' => 'l4-lock.login',
            'uses' => 'codenamegary\Lock\LockController@getLogin',
        ));
        
        $this->app['router']->post($this->app['config']->get('l4-lock::config.lock.urls.login'), 'codenamegary\Lock\LockController@postLogin');
        
        $this->app['router']->get($this->app['config']->get('l4-lock::config.lock.urls.logout'), array(
            'as' => 'l4-lock.logout',
            'uses' => 'codenamegary\Lock\LockController@getLogout',
        ));
    }

    /**
     * Register the session driver instance.
     *
     * @return void
     */
    protected function registerSessionManager()
    {
        $this->app->bindShared('l4-lock.session', function($app)
        {
            return new LockSessionManager($app);
        });
    }
    
    protected function registerSessionDriver()
    {
        $this->app->bindShared('l4-lock.session.store', function($app){
            $manager = $app['l4-lock.session'];
            return $manager->driver();
        });
        dd($this->app['l4-lock.session.store']);
    }
    
    protected function registerValidator()
    {
        $this->app->bindShared('l4-lock.validator', function($app){
            $users = $app['config']->get('l4-lock::config.lock.users', array());
            return new Validator($users);
        });
    }
    
    protected function registerLock()
    {
        $this->app->bindShared('l4-lock', function($app){
            $enabled = $app['config']->get('l4-lock::config.lock.enabled', true);
            $sessionKey = $app['config']->get('l4-lock::config.lock.session.key', 'l4-lock');
            return new Lock($app['l4-lock.session.store'], $app['request'], $app['l4-lock.validator'], $enabled, $sessionKey);            
        });
        $this->app->alias('l4-lock', 'codenamegary\Lock\LockInterface');
    }
    
    protected function registerFilter()
    {
        $this->app->bindShared('l4-lock.filter', function($app){
            return new LockFilter(
                $app['l4-lock'],
                $app['redirect'],
                $app['url']
            );
        });
    }
    
    protected function configureFilters()
    {
        if(!$this->app['l4-lock']->enabled()) return;
        $app = $this->app;
        if($this->app['config']->get('l4-lock::config.lock.global', true))
        {
            $this->app['app']->before(function($request)use($app){
                $path = $request->path();
                $exceptions = array_values($app['config']->get('l4-lock::config.lock.urls'));
                if(!in_array($path, $exceptions)) return $app['l4-lock.filter']->auth();
            });
        } else {
            $this->app['router']->filter('l4-lock.auth', function()use($app){
                return $app['l4-lock.filter']->auth();
            });
        }
    }
    
}
