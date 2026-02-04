<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class outNewCustomerOrChange extends Command
{
    protected $signature = 'out:newCustomerOrChange';
    protected $description = 'out New Customer Or Change';
    public function handle() {
        NomaneHelper::doApiCommandCall('/out/new-customer-or-change', $this->description);
    }
}
