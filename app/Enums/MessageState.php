<?php

namespace App\Enums;

enum MessageState: string
{
    case Pending = 'pending';
    case Sent = 'sent';
}
