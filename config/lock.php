<?php

return array(
    
    /**
     * Enable or disable the lock here.
     */
    'enabled' => false,
    
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
     * set to 0 to expire whenever a user closes their browser or false to
     * disable expiry completely and use the default Laravel session.
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
        // The layout to use for the login screen
        'layout' => 'lock::layout',
        // The section to put the login form inside the template
        'section' => 'content',
        // The view for the login screen
        'login' => 'lock::login',
        // Title for the form legend
        'title' => 'ACCESS RESTRICTED',
        // Prompt displayed on the login form
        'prompt' => 'Access to this site is restricted. Please login to continue.',
        // Use this to add a foot note to the login screen if desired, you can also
        // specify the name of a partial here and it will be rendered for you.
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
