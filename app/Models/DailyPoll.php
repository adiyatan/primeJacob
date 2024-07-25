<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyPoll extends Model
{
    use HasFactory;

    protected $table = 'daily_poll';

    protected $fillable = [
        'chat_id',
        'message_id',
        'tanggal',
    ];
}
