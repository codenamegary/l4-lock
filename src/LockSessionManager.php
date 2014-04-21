<?php

namespace codenamegary\Lock;

use Illuminate\Session\SessionManager;

class LockSessionManager extends SessionManager {
    
    /**
     * Get the session configuration.
     *
     * @return array
     */
    public function getSessionConfig()
    {
        return $this->app['config']['lock::session'];
    }
    
}
