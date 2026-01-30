<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class inTestOrderCancel extends Command
{
    protected $signature = 'in:testOrderCancel';
    protected $description = 'in Test Order Cancel';
    public function handle() {
        PfizerHelper::doApiCommandCall('/in/cancelled-orders', 'NO');
    }
}
