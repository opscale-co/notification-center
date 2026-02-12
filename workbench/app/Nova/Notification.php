<?php

namespace Workbench\App\Nova;

use Opscale\NotificationCenter\Nova\Notification as BaseNotification;

class Notification extends BaseNotification
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Notifications';
}
