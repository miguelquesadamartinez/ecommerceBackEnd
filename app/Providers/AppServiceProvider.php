<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Sends the exception to app Handler for sending the exception email
        $this->app->singleton(ExceptionHandlerContract::class, Handler::class);

        $this->app->singleton('global_format_date', function () {
            if(env('APP_ENV') == 'production') {
                return 'Y-d-m';
            }
            return 'Y-m-d';
        });
        $this->app->singleton('global_format_date_hour_minute_export', function () {
            return 'd/m/Y H:i:s';
        });
        $this->app->singleton('global_format_date_hour_minute_slash', function () {
            if(env('APP_ENV') == 'production') {
                return 'Y/d/m H:i';
            }
            return 'Y/m/d H:i';
        });
        $this->app->singleton('global_format_date_hour_minute_second_slash', function () {
            if(env('APP_ENV') == 'production') {
                return 'Y/d/m H:i:s';
            }
            return 'Y/m/d H:i:s';
        });
        $this->app->singleton('global_format_time', function () {
            return 'H:i:s';
        });
        $this->app->singleton('global_format_time_milisecond', function () {
            return 'H:i:s.u';
        });
        $this->app->singleton('global_format_datetime', function () {
            if(env('APP_ENV') == 'production') {
                return 'Y-d-m H:i:s';
            }
            return 'Y-m-d H:i:s';
        });
        $this->app->singleton('global_format_datetime_milisecond', function () {
            if(env('APP_ENV') == 'production') {
                return 'Y-d-m H:i:s.u';
            }
            return 'Y-m-d H:i:s.u';
        });
        $this->app->singleton('global_format_datetime_files', function () {
            if(env('APP_ENV') == 'production') {
                return 'Y-d-m H-i-s.u';
            }
            return 'Y-m-d H-i-s.u';
        });
        $this->app->singleton('global_format_for_random', function () {
            if(env('APP_ENV') == 'production') {
                return 'YdmHisu';
            }
            return 'YmdHisu';
        });
        $this->app->singleton('global_format_for_orders', function () {
            if(env('APP_ENV') == 'production') {
                return 'YdmHis';
            }
            return 'YmdHis';
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
