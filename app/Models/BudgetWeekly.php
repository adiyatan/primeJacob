<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetWeekly extends Model
{
    use HasFactory;

    protected $table = 'budget_weekly';

    protected $fillable = [
        'start_date',
        'end_date',
        'total_budget',
        'remaining_budget',
    ];
}
