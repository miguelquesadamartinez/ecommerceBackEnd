<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class outNewCustomerOrChange extends Command
{
    protected $signature = 'out:newCustomerOrChange';
    protected $description = 'out New Customer Or Change';
    public function handle() {
        PfizerHelper::doApiCommandCall('/out/new-customer-or-change', $this->description);
    }
}
