<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class outBlockedOrders extends Command
{
    protected $signature = 'out:blockedOrders';
    protected $description = 'Generate report of blocked orders';

    public function handle()
    {
        PfizerHelper::doApiCommandCall('/out/blocked-orders-out', $this->description);
    }
}
