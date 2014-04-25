<?php

namespace codenamegary\Lock;

interface ValidatorInterface {
    
    /**
     * Check the given username and password, return true if they
     * are valid, false if not.
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function valid($username, $password);
    
}
