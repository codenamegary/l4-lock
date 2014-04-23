<?php

namespace codenamegary\Lock;

use Closure;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Illuminate\Session\Middleware as BaseMiddleware;

class Middleware extends BaseMiddleware {

    /**
     * Create a new session middleware.
     *
     * @param  \Symfony\Component\HttpKernel\HttpKernelInterface  $app
     * @param  \codenamegary\Lock\LockSessionManager  $manager
     * @param  \Closure|null  $reject
     * @return void
     */
    public function __construct(HttpKernelInterface $app, LockSessionManager $manager, Closure $reject = null)
    {
        $this->app = $app;
        $this->reject = $reject;
        $this->manager = $manager;
    }

    /**
     * Handle the given request and get the response.
     *
     * @implements HttpKernelInterface::handle
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  int   $type
     * @param  bool  $catch
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if(false === $this->app['config']->get('l4-lock::config.lock.enabled', false)) return $this->abort();

        $this->checkRequestForArraySessions($request);

        // If a session driver has been configured, we will need to start the session here
        // so that the data is ready for an application. Note that the Laravel sessions
        // do not make use of PHP "native" sessions in any way since they are crappy.
        if ($this->sessionConfigured())
        {
            $session = $this->startSession($request);

            //$request->setSession($session);
        }

        $response = $this->app->handle($request, $type, $catch);

        // Again, if the session has been configured we will need to close out the session
        // so that the attributes may be persisted to some storage medium. We will also
        // add the session identifier cookie to the application response headers now.
        if ($this->sessionConfigured())
        {
            $this->closeSession($session);

            $this->addCookieToResponse($response, $session);
        }

        return $response;
    }

    /**
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function abort()
    {
        return $this->app->handle($request, $type, $catch);
    }

}
