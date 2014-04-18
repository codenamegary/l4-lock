<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as m;
use codenamegary\Lock\Validator;
use codenamegary\Lock\Lock;

class LockTest extends TestCase {
    
    /**
     * @var codenamegary\Lock\Lock
     */
    protected $lock;
    
    /**
     * @beforeClass
     */
    public static function setupLock()
    {
        $session = m::mock('Illuminate\Session\Store');
        $request = m::mock('Illuminate\Http\Request');
        $validator = new Validator(array('user' => 'p@ssw0rd'));
        $this->lock = new Lock($session, $request, $validator, true, 'lock', 0);
    }
    
    public function testLockConstructor()
    {
        var_dump(self::$lock);
    }
    
}
