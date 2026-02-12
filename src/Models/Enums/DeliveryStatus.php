<?php

namespace Opscale\NotificationCenter\Models\Enums;

enum DeliveryStatus: string
{
    // Notification is queued and waiting to be sent.
    case PENDING = 'Pending';

    // Notification failed to be delivered.
    case FAILED = 'Failed';

    // Notification has been sent to the recipient.
    case SENT = 'Sent';

    // Notification has been received by the recipient.
    case RECEIVED = 'Received';

    // Notification has been opened by the recipient.
    case OPENED = 'Opened';

    // Notification has been read or acknowledged.
    case VERIFIED = 'Verified';

    // Notification expired before being verified.
    case EXPIRED = 'Expired';

    /**
     * Check if the current status can transition to the given status.
     */
    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /**
     * Get allowed transitions from the current status.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::SENT, self::FAILED, self::EXPIRED],
            self::SENT => [self::RECEIVED, self::OPENED, self::EXPIRED],
            self::RECEIVED => [self::OPENED, self::VERIFIED, self::EXPIRED],
            self::OPENED => [self::VERIFIED, self::EXPIRED],
            self::VERIFIED => [],
            self::FAILED => [],
            self::EXPIRED => [],
        };
    }
}
