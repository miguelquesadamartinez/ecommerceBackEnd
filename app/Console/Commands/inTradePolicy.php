<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class inTradePolicy extends Command
{
    protected $signature = 'in:tradePolicy';
    protected $description = 'in Trade Policy';
    public function handle() {
        PfizerHelper::doApiCommandCall('/in/trade-policy', $this->description);
    }
}
