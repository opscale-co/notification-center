<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Messages
    |--------------------------------------------------------------------------
    |
    | Here you can register all the available notification messages.
    | The key should be an identifier (e.g., slack, sms, whatsapp)
    | and the value should be an instance of the message class.
    |
    */

    'messages' => [
        'nova' => \Opscale\NotificationCenter\Notifications\NovaNotification::class,
        'card' => \Opscale\NotificationCenter\Notifications\CardNotification::class,
        'email' => \Opscale\NotificationCenter\Notifications\EmailNotification::class,
        'sms' => \Opscale\NotificationCenter\Notifications\SmsNotification::class,
        'call' => \Opscale\NotificationCenter\Notifications\CallNotification::class,
        'whatsapp' => \Opscale\NotificationCenter\Notifications\WhatsAppNotification::class,
        'webpush' => \Opscale\NotificationCenter\Notifications\WebPushNotification::class,
        'slack' => \Opscale\NotificationCenter\Notifications\SlackNotification::class,
        'teams' => \Opscale\NotificationCenter\Notifications\TeamsNotification::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Strategies
    |--------------------------------------------------------------------------
    |
    | Configure the delivery strategy for each notification type.
    | - queue: the queue name for processing notifications
    | - channels: ordered list of channels to attempt
    | - retry_interval: seconds between retries (array for escalating delays)
    | - max_attempts: maximum number of delivery attempts per channel
    | - timeout_per_channel: hours before timing out a channel attempt
    | - days: days of the week when notifications can be sent (0=Sunday, 6=Saturday)
    | - hours: time window [from, to] in 24h format when notifications can be sent
    |
    */

    'strategies' => [
        'marketing' => [
            'queue' => 'notifications-marketing',
            'channels' => ['email'],
            'retry_interval' => [3600],
            'max_attempts' => 1,
            'timeout_per_channel' => 24,
            'days' => [1, 2, 3, 4, 5],
            'hours' => ['09:00', '18:00'],
        ],
        'transactional' => [
            'queue' => 'notifications-transactional',
            'channels' => ['nova'],
            'retry_interval' => [300, 1800],
            'max_attempts' => 3,
            'timeout_per_channel' => 2,
            'days' => [0, 1, 2, 3, 4, 5, 6],
            'hours' => ['00:00', '23:59'],
        ],
        'system' => [
            'queue' => 'notifications-system',
            'channels' => ['webpush', 'email'],
            'retry_interval' => [1800, 3600],
            'max_attempts' => 2,
            'timeout_per_channel' => 12,
            'days' => [1, 2, 3, 4, 5],
            'hours' => ['08:00', '20:00'],
        ],
        'alert' => [
            'queue' => 'notifications-alert',
            'channels' => ['webpush', 'whatsapp', 'card'],
            'retry_interval' => [30, 300, 900],
            'max_attempts' => 3,
            'timeout_per_channel' => 1,
            'days' => [0, 1, 2, 3, 4, 5, 6],
            'hours' => ['00:00', '23:59'],
        ],
        'reminder' => [
            'queue' => 'notifications-reminder',
            'channels' => ['webpush', 'whatsapp'],
            'retry_interval' => [1800],
            'max_attempts' => 2,
            'timeout_per_channel' => 6,
            'days' => [1, 2, 3, 4, 5],
            'hours' => ['09:00', '19:00'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp
    |--------------------------------------------------------------------------
    |
    | Content SID for WhatsApp notifications. This is the Twilio Content
    | Template SID used when sending WhatsApp messages.
    |
    */

    'whatsapp_content_sid' => env('TWILIO_WHATSAPP_CONTENT_SID'),

    /*
    |--------------------------------------------------------------------------
    | Google Analytics
    |--------------------------------------------------------------------------
    |
    | Your GA4 Measurement ID (e.g. G-XXXXXXXXXX). When set, the gtag.js
    | script will be included in rendered notifications.
    |
    */

    'google_analytics_id' => env('GOOGLE_ANALYTICS_ID'),
];
