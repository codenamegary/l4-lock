<?php

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
    
    public function __construct(View $view, Request $request, Config $config, Redirector $redirector, LockInterface $lock)
    {
        $this->view = $view;
        $this->request = $request;
        $this->config = $config;
        $this->redirector = $redirector;
        $this->lock = $lock;
        $disabled = $this->lock->disabled();
    }
    
    public function getLogin()
    {
        $viewConfig = $this->config->get('l4-lock::config.lock.views');
        if($this->view->exists($viewConfig['foot-note'])) $viewConfig['foot-note'] = $this->view->make($viewConfig['foot-note'])->render();
        return $this->view->make($this->config->get('l4-lock::config.lock.views.login'), array('view' => $viewConfig))->render();
    }
    
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
    
    public function getLogout()
    {
        $this->lock->logout();
        return $this->redirector->to('/');
    }
    
}
