<?php

/**
 * The public interface implemented by Lock.
 * 
 * During startup, this interface is bound to the constructed instance
 * of Lock, so you can use LockInterface as a dependency in other
 * controllers and it will be automatically injected.
 *
 * @category   codenamegary
 * @package    l4-lock
 * @author     Gary Saunders <gary@codenamegary.com>
 * @copyright  2014 Gary Saunders
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @link       http://github.com/codenamegary/l4-lock
 */
 
namespace codenamegary\Lock;

interface LockInterface {
    
    /**
     * Returns true if the lock is enabled, false if not.
     * 
     * @return bool
     */
    public function enabled();
    
    /**
     * Returns true if the lock is disabled, false if enabled.
     * 
     * @return bool
     */
    public function disabled();
    
    
    /**
     * Just enables the lock.
     * 
     * @return codenamegary\Lock\Lock
     */
    public function enable();
    
    /**
     * Just disables the lock.
     * 
     * @return codenamegary\Lock\Lock
     */
    public function disable();
    
    /**
     * Looks up and returns the user that was stored in the session.
     * 
     * @return codenamegary\User
     */
    public function user();
    
    /**
     * Returns true if the user is logged in or the lock is disabled. Returns false
     * if the user requires login.
     * 
     * @return bool
     */
    public function check();
    
    /**
     * Attempts to log the user in and store their user object in the
     * session if the provided username and password are valid.
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function attempt($username, $password);
    
    /**
     * Clears the user from the session.
     * 
     * @return codenamegary\Lock\Lock
     */
    public function logout();
    
    /**
     * Remembers the URL the user intended to visit using the lock's
     * $sessionKey property as a prefix.
     * 
     * @param string $url
     * @return codenamegary\Lock\Lock
     */
    public function setIntended($url);
    
    /**
     * Returns the URL that the was stored using the lock's $sessionKey
     * as a prefix. If none is set, $default is returned.
     * 
     * @param string $default
     * @return string
     */
    public function intended($default = '/');
    
}
