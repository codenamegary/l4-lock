<?php

/**
 * The lock filter is used to check auth status and either redirect or continue.
 * 
 * @category   codenamegary
 * @package    l4-lock
 * @author     Gary Saunders <gary@codenamegary.com>
 * @copyright  2014 Gary Saunders
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @link       http://github.com/codenamegary/l4-lock
 */

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
    
    /**
     * Dependencies are constructed and injected through the LockServiceProvider.
     * 
     * @param codenamegary\Lock\Lock $lock
     * @param Illuminate\Routing\Redirector $redirector
     * @param Illuminate\Routing\UrlGenerator $url
     */
    public function __construct(LockInterface $lock, Redirector $redirector, UrlGenerator $url)
    {
        $this->lock = $lock;
        $this->redirector = $redirector;
        $this->url = $url;
    }
    
    /**
     * When a filter is triggered, this method saves the intended URL to
     * the lock and returns a redirect to the lock login URL.
     * 
     * @return Illuminate\Http\RedirectResponse
     */
    protected function redirect()
    {
        $this->lock->setIntended($this->url->full());
        return $this->redirector->route('l4-lock.login');
    }
    
    /**
     * This is the method used to check whether or not
     * the user should be allowed to continue or
     * prompted for login.
     * 
     * @return Illuminate\Http\RedirectResponse
     */
    public function auth()
    {
        if($this->lock->check()) return;
        return $this->redirect();
    }
    
}
