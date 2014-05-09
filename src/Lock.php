<?php

/**
 * This is the default lock implementation.
 * 
 * Lock is the core object used by the filters to validate usernames and
 * passwords. It also stores the URL the user intended to visit.
 *
 * @category   codenamegary
 * @package    l4-lock
 * @author     Gary Saunders <gary@codenamegary.com>
 * @copyright  2014 Gary Saunders
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @link       http://github.com/codenamegary/l4-lock
 */

namespace codenamegary\Lock;

use Illuminate\Session\Store;
use Illuminate\Http\Request;

class Lock implements LockInterface {
    
    /**
     * @var Illuminate\Session\Store
     */
    protected $session;
    
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;
    
    /**
     * Session key to use to persist various info for the lock class.
     * 
     * @var string
     */
    protected $sessionKey;
    
    /**
     * Lock user validator.
     * 
     * @var codenamegary\Lock\ValidatorInterface
     */
    protected $validator;
    
    /**
     * Whether or not the lock is enabled.
     * 
     * @var boolean
     */
    protected $enabled;
    
    /**
     * The Lock is constructed during startup by the LockServiceProvider. Dependencies are
     * created and injected by the registerLock() function in the service provider. To
     * swap in your own implementation of the ValidatorInterface, refer to the readme
     * and update the lock config to point at your custom validator binding.
     * 
     * @param Illuminate\Session\Store $session
     * @param Illuminate\Http\Request $request
     * @param codenamegary\Lock\ValidatorInterface $validator
     * @param bool $enabled
     * @param string $sessionKey
     */
    public function __construct(Store $session, Request $request, ValidatorInterface $validator, $enabled, $sessionKey)
    {
        $this->session = $session;
        $this->request = $request;
        $this->validator = $validator;
        $this->enabled = $enabled;
        $this->sessionKey = $sessionKey;
    }
    
    /**
     * Looks up a value from the session using the lock's $sessionKey property
     * as a prefix.
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getSession($key, $default = false)
    {
        return $this->session->get($this->sessionKey . '.' . $key, $default);
    }
    
    /**
     * Clears a value from the session using the lock's $sessionKey property
     * as a prefix.
     * 
     * @param string $key
     * @return codenamegary\Lock\Lock
     */
    protected function forgetSession($key)
    {
        $this->session->forget($this->sessionKey . '.' . $key);
        return $this;
    }

    /**
     * Adds a value to the session using the lock's $sessionKey property
     * as a prefix.
     * 
     * @param string $key
     * @param mixed $value
     * @return codenamegary\Lock\Lock
     */
    protected function putSession($key, $value)
    {
        $this->session->set($this->sessionKey . '.' . $key, $value);
        return $this;
    }
    
    /**
     * Returns true if the lock is enabled, false if not.
     * 
     * @return bool
     */
    public function enabled()
    {
        return $this->enabled;
    }
    
    /**
     * Returns true if the lock is disabled, false if enabled.
     * 
     * @return bool
     */
    public function disabled()
    {
        return !$this->enabled;
    }
    
    /**
     * Just enables the lock.
     * 
     * @return codenamegary\Lock\Lock
     */
    public function enable()
    {
        $this->enabled = true;
        return $this;
    }
    
    /**
     * Just disables the lock.
     * 
     * @return codenamegary\Lock\Lock
     */
    public function disable()
    {
        $this->enabled = false;
        return $this;
    }
    
    /**
     * Looks up and returns the user that was stored in the session.
     * 
     * @return codenamegary\User
     */
    public function user()
    {
        if(!$user = $this->getSession('user', false)) return false;
        return unserialize($user);
    }
    
    /**
     * Returns true if the user is logged in or the lock is disabled. Returns false
     * if the user requires login.
     * 
     * @return bool
     */
    public function check()
    {
        if($this->disabled()) return true;
        if(!$this->getSession('user')) return false;
        return true;
    }
    
    /**
     * Attempts to log the user in and store their user object in the
     * session if the provided username and password are valid.
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function attempt($username, $password)
    {
        if(!$this->validator->valid($username, $password)) return false;
        $user = new User;
        $user->username = $username;
        $user->loggedInAt = gmdate('Y-m-d H:i:s');
        $this->putSession('user', serialize($user));
        return true;
    }
    
    /**
     * Clears the user from the session.
     * 
     * @return codenamegary\Lock\Lock
     */
    public function logout()
    {
        $this->forgetSession('user');
        return $this;
    }
    
    /**
     * Remembers the URL the user intended to visit using the lock's
     * $sessionKey property as a prefix.
     * 
     * @param string $url
     * @return codenamegary\Lock\Lock
     */
    public function setIntended($url)
    {
        $this->putSession('url', $url);
        return $this;
    }
    
    /**
     * Returns the URL that the was stored using the lock's $sessionKey
     * as a prefix. If none is set, $default is returned.
     * 
     * @param string $default
     * @return string
     */
    public function intended($default = '/')
    {
        return $this->getSession('url', $default);
    }
    
}
