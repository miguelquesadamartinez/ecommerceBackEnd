<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class inPriceControlFile extends Command
{
    protected $signature = 'in:priceControlFile';
    protected $description = 'Process price control file from NoName';

    public function handle()
    {
        NomaneHelper::doApiCommandCall('/in/price-control-file', $this->description);
    }
}
