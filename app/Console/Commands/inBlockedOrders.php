<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class inBlockedOrders extends Command
{
    protected $signature = 'in:blockedOrders';
    protected $description = 'in Blocked Orders';
    public function handle() {
        NomaneHelper::doApiCommandCall('/in/blocked-orders-in', $this->description);
    }
}
