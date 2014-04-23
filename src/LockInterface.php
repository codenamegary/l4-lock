<?php

namespace codenamegary\Lock;

interface LockInterface {
    
    public function enabled();
    
    public function disabled();
    
    /**
     * @return codenamegary\Lock\Lock
     */
    public function enable();
    
    /**
     * @return codenamegary\Lock\Lock
     */
    public function disable();
    
    /**
     * @return codenamegary\User
     */
    public function user();
    
    public function check();
    
    public function attempt($username, $password);
    
    public function logout();
    
    public function setIntended($url);
    
    public function intended($default = '/');
    
}
