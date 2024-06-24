<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\classes;

enum NotificationTypeEnum: int
{
    case NEW = 1;

    case CHANGED = 2;
}
