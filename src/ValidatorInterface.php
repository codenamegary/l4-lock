<?php

/**
 * The lock validator interface for verifying usernames and passwords.
 * 
 * @category   codenamegary
 * @package    l4-lock
 * @author     Gary Saunders <gary@codenamegary.com>
 * @copyright  2014 Gary Saunders
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @link       http://github.com/codenamegary/l4-lock
 */
 
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
