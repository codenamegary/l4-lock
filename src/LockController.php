<?php

/**
 * The login/logout controller.
 * 
 * There isn't much too this controller, it just logs users in and out
 * using the lock object.
 *
 * @category   codenamegary
 * @package    l4-lock
 * @author     Gary Saunders <gary@codenamegary.com>
 * @copyright  2014 Gary Saunders
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @link       http://github.com/codenamegary/l4-lock
 */
 
namespace codenamegary\Lock;

use Illuminate\Routing\Controller;
use Illuminate\Support\MessageBag;
use Illuminate\Routing\Redirector;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request as Request;
use Illuminate\View\Environment as View;

class LockController extends Controller {

    /**
     * @var Illuminate\View\Environment
     */
    protected $view;
    
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;
    
    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;
    
    /**
     * @var Illuminate\Routing\Redirector
     */
    protected $redirector;
    
    /**
     * @var codenamegary\Lock\Lock
     */
    protected $lock;
    
    /**
     * Dependencies here are automatically injected by Laravel.
     * 
     * @param Illuminate\View\Environment $view
     * @param Illuminate\Http\Request $request
     * @param Illuminate\Config\Repository $config
     * @param Illuminate\Routing\Redirector $redirector
     * @param codenamegary\Lock\LockInterface $lock
     */
    public function __construct(View $view, Request $request, Config $config, Redirector $redirector, LockInterface $lock)
    {
        $this->view = $view;
        $this->request = $request;
        $this->config = $config;
        $this->redirector = $redirector;
        $this->lock = $lock;
    }
    
    /**
     * Displays the login form.
     */
    public function getLogin()
    {
        $viewConfig = $this->config->get('l4-lock::config.lock.views');
        if($this->view->exists($viewConfig['foot-note'])) $viewConfig['foot-note'] = $this->view->make($viewConfig['foot-note'])->render();
        return $this->view->make($this->config->get('l4-lock::config.lock.views.login'), array('view' => $viewConfig))->render();
    }
    
    /**
     * Processes a post from the login form.
     */
    public function postLogin()
    {
        $input = $this->request->only(array('username', 'password'));
        if(!$this->lock->attempt($input['username'], $input['password']))
        {
            $errors = new MessageBag;
            $errors->add('error', 'Sorry, invalid username or password.');
            return $this->redirector->to($this->config->get('l4-lock::config.lock.urls.login'))->withErrors($errors);
        }
        return $this->redirector->to($this->lock->intended());
    }
    
    /**
     * Logs the user out and redirects to '/'.
     */
    public function getLogout()
    {
        $this->lock->logout();
        return $this->redirector->to('/');
    }
    
}
