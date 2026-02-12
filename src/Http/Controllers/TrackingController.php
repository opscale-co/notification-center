<?php

namespace Opscale\NotificationCenter\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Jenssegers\Agent\Agent;
use Opscale\NotificationCenter\Mailables\RenderTemplate;
use Opscale\NotificationCenter\Models\Delivery;
use Opscale\NotificationCenter\Models\Notification;
use Opscale\NotificationCenter\Services\Actions\TrackEvent;

class TrackingController extends Controller
{
    /**
     * Track notification open and redirect to render.
     */
    public function trackOpen(Request $request, string $slug): RedirectResponse
    {
        $delivery = Delivery::where('open_slug', $slug)->firstOrFail();

        TrackEvent::run([
            'delivery_id' => $delivery->id,
            'event' => 'Opened',
            'payload' => $this->getTrackingInfo($request),
        ]);

        return redirect()->route('notification-center.notifications.render', $delivery->id);
    }

    /**
     * Track notification action click and redirect to action URL.
     */
    public function trackAction(Request $request, string $slug): RedirectResponse
    {
        $delivery = Delivery::where('action_slug', $slug)->firstOrFail();

        TrackEvent::run([
            'delivery_id' => $delivery->id,
            'event' => 'Verified',
            'payload' => $this->getTrackingInfo($request),
        ]);

        $actionUrl = $this->appendUtmParameters(
            $delivery->notification->action,
            $delivery->notification,
            $delivery->channel,
        );

        return redirect()->away($actionUrl);
    }

    /**
     * Display the notification body.
     */
    public function renderNotification(string $id): Response
    {
        $delivery = Delivery::findOrFail($id);

        $actionUrl = $delivery->action_slug
            ? route('notification-center.track.action', $delivery->action_slug)
            : null;

        $mailable = new RenderTemplate(
            $delivery->notification,
            $actionUrl,
        );

        return response($mailable->render())
            ->header('Content-Type', 'text/html');
    }

    /**
     * Append UTM parameters to the action URL.
     */
    protected function appendUtmParameters(string $url, Notification $notification, string $channel): string
    {
        $utm = [
            'utm_source' => 'notification_center',
            'utm_medium' => $channel,
            'utm_campaign' => str($notification->subject)->slug(),
            'utm_content' => $notification->type->value,
        ];

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($utm);
    }

    /**
     * Get tracking information from the request.
     */
    protected function getTrackingInfo(Request $request): array
    {
        $agent = new Agent;
        $agent->setUserAgent($request->userAgent());

        return [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'device' => $agent->device() ?: null,
            'platform' => $agent->platform() ?: null,
            'platform_version' => $agent->version($agent->platform()) ?: null,
            'browser' => $agent->browser() ?: null,
            'browser_version' => $agent->version($agent->browser()) ?: null,
            'is_desktop' => $agent->isDesktop(),
            'is_mobile' => $agent->isMobile(),
            'is_tablet' => $agent->isTablet(),
            'is_robot' => $agent->isRobot(),
            'robot' => $agent->robot() ?: null,
            'languages' => $agent->languages(),
        ];
    }
}
