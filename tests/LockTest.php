<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as m;
use codenamegary\Lock\Validator;
use codenamegary\Lock\Lock;
use codenamegary\Lock\User;
use Illuminate\Session\Store;

class LockTest extends TestCase {
    
    /**
     * @var codenamegary\Lock\Lock
     */
    protected $lock;
    
    public function tearDown()
    {
        m::close();
    }
    
    protected function getLock($sessionData)
    {

    }
    
    public function testLogin()
    {
        $session = m::mock('Illuminate\Session\Store');
        $request = m::mock('Illuminate\Http\Request');
        $validator = new Validator(array('user' => 'p@ssw0rd'));
        
        $user = new User;
        $user->username = 'user';
        $user->password = 'p@ssw0rd';
        $user->loggedInAt = gmdate('Y-m-d H:i:s');

        $session->shouldReceive('set')
            ->with(array('lock.user', serialize($user)));        
            //->andReturn(null);
        
        $lock = new Lock($session, $request, $validator, true, 'lock', 0);

        try {
            $valid = $lock->attempt('user', 'p@ssw0rd');
            $this->assertTrue($valid);
        } catch(Mockery\Exception\NoMatchingExpectationException $e) {
            var_dump($e->getMockName());
            var_dump($e->getMethodName());
            var_dump($e->getActualArguments());
            die();
        }
        
            $valid = $lock->attempt('user', 'p@ssw0rd');
        
    }
    
    public function testBadCredentials()
    {
        //$valid = self::$lock->attempt('goggle', 'snog');
        //$this->assertFalse($valid);
    }
    
}
