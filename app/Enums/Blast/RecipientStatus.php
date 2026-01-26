<?php

namespace App\Enums\Blast;

enum RecipientStatus: string
{
    case VALID = 'valid';
    case INVALID = 'invalid';
}
