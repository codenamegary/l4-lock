<?php

namespace codenamegary\Lock;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\MessageBag;

class LockServiceProvider extends ServiceProvider {
    
    protected $defer = false;
    
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
        $this->registerRoutes();
    }
    
    public function boot()
    {
        $this->config = $this->app['config'];
        $this->router = $this->app['router'];
        $this->view = $this->app['view'];
        
        $this->view->addNamespace('lock', __DIR__ . '/Views');
        
        $this->app->bindShared('lock.validator', function($app){
            $users = $app['config']->get('lock.users', array());
            return new Validator($users);
        });
        
        $this->app->bindShared('lock', function($app){
            $enabled = $app['config']->get('lock.enabled', true);
            $sessionKey = $app['config']->get('lock.session.key', 'lock');
            $expiry = $app['config']->get('lock.expiry', 300);
            return new Lock($app['session.store'], $app['request'], $app['lock.validator'], $enabled, $sessionKey, $expiry);            
        });
        
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
            
            if($app['config']->get('lock.global', true))
            {
                $app['app']->before(function($request)use($app){
                    $path = $request->path();
                    $exceptions = array_values($app['config']->get('lock.urls'));
                    if(!in_array($path, $exceptions)) $app['lock.filter']->auth();
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

        $router->get($config->get('lock.urls.login'), array(
            'as' => 'lock.login',
            function($request)use($app, $config, $router){
                return $app['view']->make($config->get('lock.views.login'), array(
                    'title' => $config->get('lock.views.title'),
                    'prompt' => $config->get('lock.views.prompt'),
                    'foot-note' => $config->get('lock.views.foot-note'),
                ))->render();
            }
        ));
        
        $router->post($config->get('lock.urls.login'), function($request)use($app, $config, $router){
            $lock = $app['lock'];
            if($lock->expired())
            {
                $lock->logout();
                $errors = new MessageBag;
                $errors->add('error', 'Your login session has timed out. Please login again to continue.');
                return $app['redirect']->to($config->get('lock.urls.login'))->withErrors($errors);
            }
            $input = $app['input']->only(array('username', 'password'));
            if(!$lock->attempt($input['username'], $input['password']))
            {
                $errors = new MessageBag;
                $errors->add('error', 'Sorry, invalid username or password.');
                return $app['redirect']->to($config->get('lock.urls.login'))->withErrors($errors);
            }
            return $app['redirect']->to($lock->intended());
        });
        
        $router->get($config->get('lock.urls.logout'), array(
            'as' => 'lock.logout',
            function($request)use($app, $config, $router){
                $lock = $app['lock'];
                $lock->logout();
                return $app['redirect']->to('/');
            }
        ));

    }
    
}
