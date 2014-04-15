<?php

return array(
    
    /**
     * Enable or disable the lock here.
     */
    'enabled' => true,
    
    /**
     * Enforce the lock across the entire site. If you'd rather enable
     * the lock selectively, disable this config option and apply
     * the lock.auth filter on whichever routes you want locked.
     */
    'global' => true,
    
    /**
     * Some parameters to use for the session.
     */
    'session' => array(
        'key' => 'lock',
    ),
    
    /**
     * Expire after this many seconds of inactivity and force another login,
     * set to 0 or false to disable.
     */
    'expiry' => 60 * 20,
    
    /**
     * This refers to the app binding for the validator class that should
     * be used to validate usernames and passwords.
     */
    'validator' => 'lock.validator',
    
    /**
     * Valid username and password combinations used by the default validator.
     */
    'users' => array(
        'client' => 'alwaysright',
    ),
    
    /**
     * View that will be used for the login screen and related paramters.
     */
    'views' => array(
        'login' => 'lock::login',
        'title' => 'ACCESS RESTRICTED',
        'prompt' => 'Please login to continue.',
        // Use this to add a foot note to the login screen if desired,
        // e.g. - contact abc@xyz.com for support.
        'foot-note' => false,
    ),
    
    /**
     * URLs that lock will respond with / use.
     */
    'urls' => array(
        'login' => 'lock/login',
        'logout' => 'lock/logout',
    ),
    
);
