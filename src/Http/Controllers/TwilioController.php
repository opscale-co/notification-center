<?php

namespace Opscale\NotificationCenter\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Opscale\NotificationCenter\Models\Delivery;
use Twilio\TwiML\VoiceResponse;

class TwilioController extends Controller
{
    /**
     * Return TwiML for a call notification.
     */
    public function callNotification(string $id): Response
    {
        $delivery = Delivery::findOrFail($id);

        $response = new VoiceResponse;
        $response->say($delivery->notification->summary);

        return response($response->asXML(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
