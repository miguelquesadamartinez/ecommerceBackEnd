<?php

use App\Http\Middleware\Cors;
use Illuminate\Foundation\Application;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/*',
            '/sanctum/get-token',
            '/sanctum/verify-token'
        ]);
        $middleware->append(Cors::class);
        //$middleware->append('throttle:api');
        //$middleware->append(ThrottleRequests::class);

        // ToDo: Uncomment for LDAP
        // Needed for LdapRecord
        $middleware->append(EnsureFrontendRequestsAreStateful::class);

        // Estos ... fallan
        //$middleware->append('throttle:api');
        //$middleware->append(SubstituteBindings::class);

        $middleware->alias([
                            'adminOnly' => AdminMiddleware::class,
                            'throttle' => ThrottleRequests::class
                           ]);

        //$middleware->web(append: [
            //Cors::class,
            // Needed for LdapRecord
            //EnsureFrontendRequestsAreStateful::class,
            //SubstituteBindings::class,
        //]);
        //$middleware->api(append: [
            //Cors::class,
            // Needed for LdapRecord
            //EnsureFrontendRequestsAreStateful::class,
            //'throttle:api',
            //SubstituteBindings::class,
        //]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

    })->create();


