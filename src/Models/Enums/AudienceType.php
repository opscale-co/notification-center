<?php

namespace Opscale\NotificationCenter\Models\Enums;

enum AudienceType: string
{
    case STATIC = 'Static';
    case DYNAMIC = 'Dynamic';
    case SEGMENT = 'Segment';
}
