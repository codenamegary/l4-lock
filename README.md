#Lock for Laravel

Lock is a Laravel package that allows you to very quickly and with minimal effort implement a global or selective authentication prompt while developing a site.

It is highly configurable and allows for very quick updates to the style and content of the login screen, users and URLs.

![Login Screenshot](login-screen.png?raw=true "Login Screenshot")

##SECURITY NOTE

Lock's primary purpose is to protect an application with a password while under development without interfering with Laravel's built-in auth drivers. It is completely distinct from the built-in Auth, allowing you to password protect all parts of your application for client access only while being developed.

Lock comes with a simple, config file based user => password validator for usernames and passwords. Users are stored in the config file in plain text. The validator binding is configurable and using a different method to validate users is as simple as creating your own Validator that implements the ValidatorInterface.

For more information, see "Custom Validators".

Lock requires the default Laravel session to store credentials, so it must be configured and working before use.

###Installation

**NOTE:** Once installed, lock will be enabled globally by default. You can disable it through the package config.

Add Lock to your composer.json.

    "require": {
        "codenamegary/l4-lock": "dev-master"
    }

Composer Update

    composer update

Add the service provider to app/config/app.php providers array.

    'providers' => array(
        ...
        'codenamegary\Lock\LockServiceProvider',
    ),

Publish the configuration with Artisan.

    php artisan config:publish codenamegary/l4-lock

Edit / add users in the config under app/config/packages/codenamegary/l4-lock/lock.php.

    /**
     * Valid username and password combinations used by the default validator.
     */
    'users' => array(
        'client' => 'alwaysright',
    ),
    
###Custom Validators

The lock config contains a setting for the validator binding that will be used to validate usernames and passwords. The Validator interface (below) contains 1 method.

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

To implement your own validator, do the following.

####Create your validator class and implement the ValidatorInterface.

    class CustomLockValidator implements codenamegary\Lock\ValidatorInterface {
    
        public function valid($username, $password)
        {
            ... validate the username and password ...
            ... return true ...
            ... (or) ...
            ... return false ...
        }
    
    }

####Register your validator in the container

For convenience and to enable you to inject whatever dependencies you might require into your validator, bind your custom validator to the container.

Example:

    $app->bindShared('custom.lock.validator', function($app){
        $validatorDependency1 = $app['some.dependency'];
        $validatorDependency2 = $app['some.other.dependency'];
        return new CustomLockValidator($validatorDependency1, $validatorDependency2);
    });

####Update the lock config to use the binding you created

Inside app/config/packages/codenamegary/l4-lock/lock.php, edit the validator binding.

    /**
     * This refers to the app binding for the validator class that should
     * be used to validate usernames and passwords.
     */
    'validator' => 'custom.lock.validator',
    
That's it! Lock will now call the valid method on your validator whenever a user tries to login.

##Selective Auth

You may wish to disable the global auth filter and instead selectively enable it for the routes you want protected.

First disable the global auth setting in app/config/packages/codenamegary/l4-lock/lock.php.

    /**
     * Enforce the lock across the entire site. If you'd rather enable
     * the lock selectively, disable this config option and apply
     * the lock.auth filter on whichever routes you want locked.
     */
    'global' => false,
    
Next, apply the auth filter to any route that you want to protect.

    Route::get('/', array(
        'before' => 'l4-lock.auth',
        function()
        {
           return View::make('hello')->render();
        }
    ));
    
###Global Filter Exceptions

If you'd like to enable the global filter with some exceptions, just leave the global config option enabled and then add the patterns and strings you'd like to exclude to the exceptions config.


    /**
     * Here you can provide a list of regex URI patterns that will be excluded
     * from the global filter. These are checked against the actual URI so
     * so to exclude 'http://domain/thing/*', you would add an exception
     * for '/thing\/.*?/'.
     */
    'exceptions' => array(
        // Route exception patterns go here
    ),

##Customizing the Login Screen

Lock comes with a built-in Bootstrap3 style login screen. The config includes many settings that make it easy to change the login prompt and even use a different layout or view.

    /**
     * View that will be used for the login screen and related paramters.
     */
    'views' => array(
        // The layout to use for the login screen
        'layout' => 'l4-lock::layout',
        // The section to put the login form inside the template
        'section' => 'content',
        // The view for the login screen
        'login' => 'l4-lock::login',
        // Title for the form legend
        'title' => 'ACCESS RESTRICTED',
        // Prompt displayed on the login form
        'prompt' => 'Access to this site is restricted. Please login to continue.',
        // Use this to add a foot note to the login screen if desired, you can also
        // specify the name of a partial here and it will be rendered for you.
        // e.g. - contact abc@xyz.com for support.
        'foot-note' => false,
    ),

##Customizing URLs

By default Lock uses /lock/login and /lock/logout to provide login/out functions. These are also configurable.

    /**
     * URLs that lock will respond with / use.
     */
    'urls' => array(
        'login' => 'lock/login',
        'logout' => 'lock/logout',
    ),
    