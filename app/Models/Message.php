<?php

namespace App\Models;

use App\Enums\MessageState;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'message',
        'state',
        'sent_at',
    ];

    protected $casts = [
        'state' => MessageState::class,
        'sent_at' => 'datetime',
    ];
}
