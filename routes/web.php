<?php

use Illuminate\Support\Facades\Route;
use Opscale\NotificationCenter\Http\Controllers\TrackingController;
use Opscale\NotificationCenter\Http\Controllers\TwilioController;
use Opscale\NotificationCenter\Http\Controllers\WebPushController;

Route::get('o/{slug}', [TrackingController::class, 'trackOpen'])
    ->name('notification-center.track.open');

Route::get('a/{slug}', [TrackingController::class, 'trackAction'])
    ->name('notification-center.track.action');

Route::get('notifications/{id}', [TrackingController::class, 'renderNotification'])
    ->name('notification-center.notifications.render');

Route::post('twiml/call-notification/{id}', [TwilioController::class, 'callNotification'])
    ->name('notification-center.twiml.call-notification');

Route::get('sw.js', [WebPushController::class, 'serviceWorker'])
    ->name('notification-center.webpush.sw');

Route::get('webpush/subscribe/{profileId}', [WebPushController::class, 'subscribe'])
    ->name('notification-center.webpush.subscribe');

Route::post('webpush/register/{profileId}', [WebPushController::class, 'register'])
    ->name('notification-center.webpush.register');
