<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class inTradePolicy extends Command
{
    protected $signature = 'in:tradePolicy';
    protected $description = 'in Trade Policy';
    public function handle() {
        NomaneHelper::doApiCommandCall('/in/trade-policy', $this->description);
    }
}
