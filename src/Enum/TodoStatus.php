<?php

declare(strict_types=1);

namespace App\Enum;

enum TodoStatus: string
{
    case OPEN = 'open';
    case DONE = 'done';
}
