<?php

namespace App\Console;

use App\Console\Commands\CloseDailyPolls;
use App\Console\Commands\MakanSiangCimahi;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // Register the command here
        CloseDailyPolls::class,
        MakanSiangCimahi::class,
    ];
}
