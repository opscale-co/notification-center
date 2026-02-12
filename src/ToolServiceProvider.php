<?php

namespace Opscale\NotificationCenter;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event as EventFacade;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Models\Notification as NotificationModel;
use Opscale\NotificationCenter\Nova\Audience;
use Opscale\NotificationCenter\Nova\Delivery;
use Opscale\NotificationCenter\Nova\Event;
use Opscale\NotificationCenter\Nova\Notification;
use Opscale\NotificationCenter\Nova\Profile;
use Opscale\NotificationCenter\Nova\Subscription;
use Opscale\NotificationCenter\Nova\Template;
use Opscale\NotificationCenter\Services\Actions\TrackEvent;
use Opscale\NovaPackageTools\NovaPackage;
use Opscale\NovaPackageTools\NovaPackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;

class ToolServiceProvider extends NovaPackageServiceProvider
{
    /**
     * @phpstan-ignore solid.ocp.conditionalOverride
     */
    public function configurePackage(Package $package): void
    {
        /** @var NovaPackage $package */
        $package
            ->name('notification-center')
            ->hasConfigFile()
            ->hasViews()
            ->discoversMigrations()
            ->runsMigrations()
            ->hasResources([
                Audience::class,
                Delivery::class,
                Event::class,
                Notification::class,
                Profile::class,
                Subscription::class,
                Template::class,
            ])
            ->hasTranslations()
            ->hasRoute('web')
            ->hasInstallCommand(function (InstallCommand $installCommand): void {
                $installCommand
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('opscale-co/notification-center');
            });
    }

    public function packageBooted(): void
    {
        $this->registerListeners();
        $this->scheduleNotifications();
    }

    protected function registerListeners(): void
    {
        EventFacade::listen(NotificationSent::class, TrackEvent::class);
        EventFacade::listen(NotificationFailed::class, TrackEvent::class);
    }

    protected function scheduleNotifications(): void
    {
        $this->app->booted(function () {
            $this->app->make(Schedule::class)->call(function () {
                NotificationModel::where('status', NotificationStatus::PUBLISHED)
                    ->whereNull('expiration')->orWhere('expiration', '>', now())
                    ->each(function ($notification) {
                        ExecuteNotificationStrategy::dispatch($notification);
                    });
            })->hourly();
        });
    }
}
