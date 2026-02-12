<?php

namespace Opscale\NotificationCenter\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Opscale\NotificationCenter\Mailables\SubscribeTemplate;
use Opscale\NotificationCenter\Models\Profile;

class WebPushController extends Controller
{
    /**
     * Serve the service worker script.
     */
    public function serviceWorker(): Response
    {
        $path = __DIR__ . '/../../../resources/js/sw.js';

        return response(file_get_contents($path))
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/');
    }

    /**
     * Display the push notification subscription page.
     */
    public function subscribe(string $profileId): Response
    {
        Profile::findOrFail($profileId);

        $mailable = new SubscribeTemplate(
            registerUrl: route('notification-center.webpush.register', $profileId),
            vapidPublicKey: config('webpush.vapid.public_key'),
        );

        return response($mailable->render())
            ->header('Content-Type', 'text/html');
    }

    /**
     * Register a push subscription for a profile.
     */
    public function register(Request $request, string $profileId): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|url',
            'key' => 'nullable|string',
            'token' => 'nullable|string',
            'content_encoding' => 'nullable|string',
        ]);

        $profile = Profile::findOrFail($profileId);

        $profile->updatePushSubscription(
            $request->input('endpoint'),
            $request->input('key'),
            $request->input('token'),
            $request->input('content_encoding'),
        );

        return response()->json(['message' => __('Subscription registered successfully.')]);
    }
}
