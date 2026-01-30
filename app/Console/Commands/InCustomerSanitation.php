<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class InCustomerSanitation extends Command
{
    protected $signature = 'in:customerSanitation';
    protected $description = 'in Customer Sanitation';
    public function handle() {
        PfizerHelper::doApiCommandCall('/in/customer-sanitation', $this->description);
    }
}