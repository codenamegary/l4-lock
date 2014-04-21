<?php

/**
 * Stores an id in a cookie for the lock user data.
 */

namespace codenamegary\Lock;

use Illuminate\Http\Request;

class Storage {
    
    /**
     * @var string
     */
    protected $cookie;
    
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;
    
    /**
     * Number of minutes of inactivity before the session should expire.
     * 
     * @var int
     */
    protected $expiry;
    
    public function __construct(Request $request, $expiry)
    {
        $this->request = $request;
    }
    
    protected function readCookie($default)
    {
        
    }
    
}
