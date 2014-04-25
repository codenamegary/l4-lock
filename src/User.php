<?php

/**
 * Just a dumb object with 2 properties that gets serialized
 * and stored in the session when a user logs in via lock.
 * 
 * @category   codenamegary
 * @package    l4-lock
 * @author     Gary Saunders <gary@codenamegary.com>
 * @copyright  2014 Gary Saunders
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @link       http://github.com/codenamegary/l4-lock
 */
 
namespace codenamegary\Lock;

class User {
    
    public $username;
    public $loggedInAt;
    
}
