<?php

namespace Workbench\App\Providers;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Channels\StdoutChannel;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('stdout', function ($app) {
                return new StdoutChannel;
            });
        });
    }
}
