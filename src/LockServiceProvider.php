<?php

/**
 * The lock service provider constructs all of the necessary dependencies
 * and registers lock related bindings in the container.
 * 
 * @category   codenamegary
 * @package    l4-lock
 * @author     Gary Saunders <gary@codenamegary.com>
 * @copyright  2014 Gary Saunders
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @link       http://github.com/codenamegary/l4-lock
 */
 
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
    
    /**
     * Registers the proper configuration and view directory
     * hints for the l4-lock prefix used by the Laravel
     * config and view interfaces.
     */
    protected function registerNamespaces()
    {
        $this->app['config']->addNamespace('l4-lock', app_path('config/packages/codenamegary/l4-lock'));
        $this->app['view']->addNamespace('l4-lock', __DIR__ . '/views');
    }
    
    /**
     * Registers the login and logout routes for the Lock.
     */
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
     * Registers the binding for the default, config file based
     * username and password validator.
     */
    protected function registerValidator()
    {
        $this->app->bindShared('l4-lock.validator', function($app){
            $users = $app['config']->get('l4-lock::config.lock.users', array());
            return new Validator($users);
        });
    }
    
    /**
     * Constructs all of the dependencies for the Lock
     * object and binds it to the container. Also
     * aliases lock to the LockInterface.
     */
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
    
    /**
     * Binds the lock filter to the container.
     */
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
    
    /**
     * Configures the lock filters based on the lock configuration.
     */
    protected function configureFilters()
    {
        if(!$this->app['l4-lock']->enabled()) return;
        $app = $this->app;
        if($this->app['config']->get('l4-lock::config.lock.global', true))
        {
            $this->app['app']->before(function($request, $route)use($app){
                $path = $request->path();
                $exceptions = array_values($app['config']->get('l4-lock::config.lock.urls'));
                $exceptions = array_merge($exceptions, $app['config']->get('l4-lock::config.lock.exceptions'));
                $excepted = false;
                foreach($exceptions as $exception)
                {
                    $isPattern = substr($exception, 0, 1) == '/';
                    switch($isPattern)
                    {
                        case true:
                            if(preg_match($exception, $path) === 1) return;
                            break;
                        case false:
                            if($path == $exception) return;
                            break;
                    }
                }
                return $app['l4-lock.filter']->auth();
            });
        } else {
            $this->app['router']->filter('l4-lock.auth', function()use($app){
                return $app['l4-lock.filter']->auth();
            });
        }
    }
    
}
