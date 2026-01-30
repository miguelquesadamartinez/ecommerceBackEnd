<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class inProcessUnavailableProducts extends Command
{
    protected $signature = 'in:processUnavailableProducts';
    protected $description = 'Process unavailable products from Excel file and update their status';

    public function handle()
    {
        PfizerHelper::doApiCommandCall('/in/process-unavailable-products', $this->description);
    }
}
