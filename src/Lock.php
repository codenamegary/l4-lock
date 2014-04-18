<?php

namespace codenamegary\Lock;

use Illuminate\Session\Store;
use Illuminate\Http\Request;
use DateTime, DateTimezone, DateInterval, Log;

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
     * @var codenamegary\Lock\Validator
     */
    protected $validator;
    
    /**
     * Whether or not the lock is enabled.
     * 
     * @var boolean
     */
    protected $enabled;
    
    /**
     * How many seconds a login is valid for.
     * 
     * @var integer
     */
    protected $expiry;
    
    public function __construct(Store $session, Request $request, Validator $validator, $enabled, $sessionKey, $expiry)
    {
        $this->session = $session;
        $this->request = $request;
        $this->validator = $validator;
        $this->enabled = $enabled;
        $this->sessionKey = $sessionKey;
        $this->expiry = $expiry;
    }
    
    protected function getSession($key, $default = false)
    {
        return $this->session->get($this->sessionKey . '.' . $key, $default);
    }

    protected function forgetSession($key)
    {
        $this->session->forget($this->sessionKey . '.' . $key);
        return $this;
    }

    protected function putSession($key, $value)
    {
        $this->session->set($this->sessionKey . '.' . $key, $value);
        return $this;
    }
    
    public function enabled()
    {
        return $this->enabled;
    }
    
    public function disabled()
    {
        return !$this->enabled;
    }
    
    /**
     * @return codenamegary\Lock\Lock
     */
    public function enable()
    {
        $this->enabled = true;
        return $this;
    }
    
    /**
     * @return codenamegary\Lock\Lock
     */
    public function disable()
    {
        $this->enabled = false;
        return $this;
    }
    
    /**
     * @return codenamegary\User
     */
    public function user()
    {
        if(!$user = $this->getSession('user', false)) return false;
        return unserialize($user);
    }
    
    public function check()
    {
        if($this->disabled()) return true;
        if(!$this->getSession('user')) return false;
        return true;
    }
    
    public function attempt($username, $password)
    {
        if(!$this->validator->valid($username, $password)) return false;
        $user = new User;
        $user->username = $username;
        $user->loggedInAt = gmdate('Y-m-d H:i:s');
        $this->putSession('user', serialize($user));
        $this->session->save();
        return true;
    }
    
    public function logout()
    {
        $this->forgetSession('user');
        $this->session->save();
        return $this;
    }
    
    public function expired()
    {
        $tzUtc = new DateTimezone("UTC");
        $now = new DateTime(null, $tzUtc);
        $then = new DateTime($this->getSession('updated', gmdate('Y-m-d H:i:s')), $tzUtc);
        $seconds = $now->getTimestamp() - $then->getTimestamp();
        if($seconds > $this->expiry)
        {
            Log::info('Lock expired.');
            return true;
        }
        Log::info('Lock not expired.');
        return false;
    }
    
    public function tick()
    {
        $this->putSession('updated', gmdate('Y-m-d H:i:s'));
        $this->session->save();
    }
    
    public function setIntended($url)
    {
        $this->putSession('url', $url);
        return $this;
    }
    
    public function intended($default = '/')
    {
        return $this->getSession('url', $default);
    }
    
}
