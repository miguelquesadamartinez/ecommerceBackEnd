<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Mail\ExceptionOccured;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            return response()->json([
                'message' => '' // ou pas de clé 'message'
            ], 429);
        }
        return parent::render($request, $exception);
    }

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->sendEmail($e);

        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ( ( ! $request->is('verify-token')) && ($request->expectsJson() || $request->is('api/*') ) ) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'status' => 401
                ], 401);
            }
        });
    }

    public function sendEmail(Throwable $exception)
    {
       try {
            $content['message'] = $exception->getMessage();
            $content['file'] = $exception->getFile();
            $content['line'] = $exception->getLine();
            $content['trace'] = $exception->getTrace();
            $content['url'] = request()->url();
            $content['body'] = request()->all();
            $content['ip'] = request()->ip();
            $content['fullUrl'] = request()->fullUrl();
            if (isset(Auth::user()->name)){
                $content['user'] = Auth::user()->name;
            } else {
                $content['user'] = 'No user logged';
            }
            Mail::to(env('EMAIL_FOR_APP_ERROR'))->send(new ExceptionOccured($content));
        } catch (Throwable $exception) {
            Log::error($exception);
        }
    }
}
