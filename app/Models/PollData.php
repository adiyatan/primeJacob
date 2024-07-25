<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollData extends Model
{
    use HasFactory;

    protected $table = 'poll_data';

    protected $fillable = [
        'poll_id',
        'options',
        'total_voter_count',
        'date',
        'chat_id',
        'message_id',
        'first_name'
    ];

    protected $casts = [
        'options' => 'array',
    ];
}
