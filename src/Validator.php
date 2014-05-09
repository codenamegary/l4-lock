<?php

/**
 * This is the default username and password validator.
 * 
 * It is configured with user credentials from the lock configuration and
 * simply validates that the passed user and password match an item from
 * the configured users.
 * 
 * @category   codenamegary
 * @package    l4-lock
 * @author     Gary Saunders <gary@codenamegary.com>
 * @copyright  2014 Gary Saunders
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @link       http://github.com/codenamegary/l4-lock
 */
 
namespace codenamegary\Lock;

class Validator implements ValidatorInterface {
    
    /**
     * Just simple key => value mapping of valid user => passwords.
     * 
     * @var array
     */
    protected $validCredentials;
    
    /**
     * Valid credentials are generally injected by the lock service provider.
     * 
     * @param array $validCredentials
     */
    public function __construct(array $validCredentials = array())
    {
        $this->validCredentials = $validCredentials;
    }
    
    /**
     * Adds a username and password combination to the list of
     * credentials that will be considered valid.
     * 
     * @param string $username
     * @param string $password
     * @return codenamegary\Lock\Validator
     */
    public function addUser($username, $password)
    {
        $this->validCredentials[$username] = $password;
        return $this;
    }
    
    /**
     * Removes a user from the list of valid credentials.
     * 
     * @param string $username
     * @return codenamegary\Lock\Validator
     */
    public function deleteUser($username)
    {
        if(array_key_exists($username, $this->validCredentials)) unset($this->validCredentials[$username]);
        return $this;
    }
    
    /**
     * Sets the passed array of credentials as the list
     * of valid credentials. Overwrites any existing
     * users in the list.
     * 
     * @param array $validCredentials
     * @return codenamegary\Lock\Validator
     */
    public function setUsers(array $validCredentials)
    {
        $this->validCredentials = $validCredentials;
        return $this;
    }
    
    /**
     * Validates that the given username and password match
     * a username in the $validCredentials property.
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function valid($username, $password)
    {
        if(array_key_exists($username, $this->validCredentials) and $password === $this->validCredentials[$username]) return true;
        return false;
    }
    
}
