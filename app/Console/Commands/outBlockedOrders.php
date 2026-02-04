<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class outBlockedOrders extends Command
{
    protected $signature = 'out:blockedOrders';
    protected $description = 'Generate report of blocked orders';

    public function handle()
    {
        NomaneHelper::doApiCommandCall('/out/blocked-orders-out', $this->description);
    }
}
