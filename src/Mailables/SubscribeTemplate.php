<?php

namespace Opscale\NotificationCenter\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscribeTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $registerUrl, public string $swUrl, public string $vapidPublicKey) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Subscribe to Push Notifications'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'notification-center::webpush.subscribe',
        );
    }
}
