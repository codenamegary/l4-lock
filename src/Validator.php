<?php

namespace codenamegary\Lock;

class Validator implements ValidatorInterface {
    
    /**
     * Just simple key => value mapping of valid user => passwords.
     * 
     * @var array
     */
    protected $validCredentials;
    
    public function __construct(array $validCredentials = array())
    {
        $this->validCredentials = $validCredentials;
    }
    
    public function addUser($username, $password)
    {
        $this->validCredentials[$username] = $password;
        return $this;
    }
    
    public function deleteUser($username)
    {
        if(array_key_exists($username, $this->validCredentials)) unset($this->validCredentials[$username]);
        return $this;
    }
    
    public function setUsers(array $validCredentials)
    {
        $this->validCredentials = $validCredentials;
        return $this;
    }
    
    public function valid($username, $password)
    {
        if(array_key_exists($username, $this->validCredentials) and $password === $this->validCredentials[$username]) return true;
        return false;
    }
    
}
