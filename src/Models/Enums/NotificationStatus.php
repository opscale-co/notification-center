<?php

namespace Opscale\NotificationCenter\Models\Enums;

enum NotificationStatus: string
{
    case DRAFT = 'Draft';
    case PUBLISHED = 'Published';
}
