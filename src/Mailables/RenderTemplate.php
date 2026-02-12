<?php

namespace Opscale\NotificationCenter\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Opscale\NotificationCenter\Models\Notification;

class RenderTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Notification $notification, public ?string $actionUrl = null) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'notification-center::notifications.mail',
        );
    }
}
