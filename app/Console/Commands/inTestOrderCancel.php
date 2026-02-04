<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class inTestOrderCancel extends Command
{
    protected $signature = 'in:testOrderCancel';
    protected $description = 'in Test Order Cancel';
    public function handle() {
        NomaneHelper::doApiCommandCall('/in/cancelled-orders', 'NO');
    }
}
