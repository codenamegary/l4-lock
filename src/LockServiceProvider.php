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
        $this->cookie = $this->app['cookie'];
        $this->config = $this->app['config'];
        $this->router = $this->app['router'];
        $this->view = $this->app['view'];
        $this->registerRoutes();
        $this->registerSessionDriver();
    }
    
    public function boot()
    {
        $this->config = $this->app['config'];
        $this->router = $this->app['router'];
        $this->view = $this->app['view'];
        
        $this->view->addNamespace('lock', __DIR__ . '/../views');
        $this->config->addNamespace('lock', __DIR__ . '/../config');
        
        $this->app->bindShared('lock.validator', function($app){
            $users = $app['config']->get('lock::lock.users', array());
            return new Validator($users);
        });
        
        $this->app->bindShared('lock', function($app){
            $enabled = $app['config']->get('lock::lock.enabled', true);
            $sessionKey = $app['config']->get('lock::lock.session.key', 'lock');
            $expiry = $app['config']->get('lock::lock.expiry', 300);
            return new Lock($app['session.store'], $app['request'], $app['lock.validator'], $enabled, $sessionKey, $expiry);            
        });
        
        $this->app->alias('lock', 'codenamegary\Lock\LockInterface');
        
        $this->app->bindShared('lock.filter', function($app){
            return new LockFilter(
                $app['lock'],
                $app['redirect'],
                $app['url']
            );
        });
        
        $app = $this->app;
        if($app['lock']->enabled())
        {
            
            if($app['config']->get('lock::lock.global', true))
            {
                $app['app']->before(function($request)use($app){
                    $path = $request->path();
                    $exceptions = array_values($app['config']->get('lock::lock.urls'));
                    if(!in_array($path, $exceptions)) return $app['lock.filter']->auth();
                });
            } else {
                $this->router->filter('lock.auth', function()use($app){
                    return $app['lock.filter']->auth();
                });
            }
                        
            $app['app']->before(function()use($app){
                $app['lock.filter']->tick();
            });
            
        }
    }
    
    public function registerRoutes()
    {
        $app = $this->app;
        $config = $app['config'];
        $router = $app['router'];
        
        $config->addNamespace('lock', __DIR__ . '/../config');

        $router->get($config->get('lock::lock.urls.login'), array(
            'as' => 'lock.login',
            'uses' => 'codenamegary\Lock\LockController@getLogin',
        ));
        
        $router->post($config->get('lock::lock.urls.login'), 'codenamegary\Lock\LockController@postLogin');
        
        $router->get($config->get('lock::lock.urls.logout'), array(
            'as' => 'lock.logout',
            'uses' => 'codenamegary\Lock\LockController@getLogout',
        ));

    }

    /**
     * Register the session driver instance.
     *
     * @return void
     */
    protected function registerSessionDriver()
    {
        if(!$cookie = $this->cookie->get('lock'))
        $this->app->bindShared('lock.session', function($app)
        {
            // First, we will create the session manager which is responsible for the
            // creation of the various session drivers when they are needed by the
            // application instance, and will resolve them on a lazy load basis.
            $manager = $app['session'];

            return $manager->driver();
        });
    }
    
}
