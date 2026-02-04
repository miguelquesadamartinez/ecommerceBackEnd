<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class InCustomerSanitation extends Command
{
    protected $signature = 'in:customerSanitation';
    protected $description = 'in Customer Sanitation';
    public function handle() {
        NomaneHelper::doApiCommandCall('/in/customer-sanitation', $this->description);
    }
}
