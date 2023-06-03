<?php

declare(strict_types=1);

namespace App\Project;

enum ProjectStatus: string
{
    case New = 'new';
    case Pending = 'pending';
    case Failed = 'failed';
    case Done = 'done';
}
