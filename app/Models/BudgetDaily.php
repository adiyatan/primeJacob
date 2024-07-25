<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetDaily extends Model
{
    use HasFactory;

    protected $table = 'budget_daily';

    protected $fillable = [
        'tanggal',
        'total_voters',
        'total_amount',
        'remaining_budget',
    ];
}
