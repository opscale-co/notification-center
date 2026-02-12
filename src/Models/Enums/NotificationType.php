<?php

namespace Opscale\NotificationCenter\Models\Enums;

enum NotificationType: string
{
    // Promotional content, newsletters, and marketing campaigns.
    case MARKETING = 'Marketing';

    // Order confirmations, receipts, and account-related updates.
    case TRANSACTIONAL = 'Transactional';

    // Platform updates, maintenance notices, and system messages.
    case SYSTEM = 'System';

    // Urgent notifications requiring immediate attention.
    case ALERT = 'Alert';

    // Scheduled reminders for tasks, events, or deadlines.
    case REMINDER = 'Reminder';
}
