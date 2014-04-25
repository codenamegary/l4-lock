<?php

namespace codenamegary\Lock;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\MessageBag;
use Illuminate\Cookie\CookieJar;

class LockServiceProvider extends ServiceProvider {
    
    protected $defer = false;
    
    public function register()
    {
        $this->package('codenamegary/l4-lock');
        $this->registerNamespaces();
        $this->registerRoutes();
        $this->registerValidator();
    }
    
    public function boot()
    {
        $this->package('codenamegary/l4-lock');
        $this->registerNamespaces();
        $this->registerLock();
        $this->registerFilter();
        $this->configureFilters();
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
            $validatorBinding = $app['config']->get('l4-lock::config.lock.validator', 'l4-lock.validator');
            return new Lock($app['session.store'], $app['request'], $app[$validatorBinding], $enabled, $sessionKey);            
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
