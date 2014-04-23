<?php

namespace codenamegary\Lock;

use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;

class LockFilter {
    
    /**
     * @var codenamegary\Lock\Lock
     */
    protected $lock;

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;
    
    /**
     * @var codenamegary\Utils\ResponseFactoryInterface
     */
    protected $responseFactory;
    
    /**
     * @var Illuminate\Routing\UrlGenerator
     */
    protected $url;
    
    /**
     * @var Illuminate\Routing\Redirector
     */
    protected $redirector;
    
    public function __construct(
        Lock $lock,
        Redirector $redirector,
        UrlGenerator $url
    )
    {
        $this->lock = $lock;
        $this->redirector = $redirector;
        $this->url = $url;
    }
    
    public function redirect()
    {
        $this->lock->setIntended($this->url->full());
        return $this->redirector->route('l4-lock.login');
    }
    
    public function auth()
    {
        if($this->lock->check()) return;
        return $this->redirect();
    }
    
    public function tick()
    {
        $this->lock->tick();
    }
    
}
