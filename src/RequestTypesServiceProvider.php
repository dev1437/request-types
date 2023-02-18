<?php

namespace Dev1437\RequestTypes;

use Dev1437\RequestTypes\Console\ExportRequests;
use Illuminate\Support\ServiceProvider;

class RequestTypesServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportRequests::class,
            ]);
        }
    }
}