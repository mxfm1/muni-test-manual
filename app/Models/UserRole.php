<?php

declare(strict_types=1);

namespace ManualMuni\Models;

enum UserRole: string
{
    case Viewer = 'VIEWER';
    case Editor = 'EDITOR';
    case Admin = 'ADMIN';
}
