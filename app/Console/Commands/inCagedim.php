<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class inCagedim extends Command
{
    protected $signature = 'in:cagedim';
    protected $description = 'in Cagedim';
    public function handle() {
        NomaneHelper::doApiCommandCall('/in/cagedim-orders', $this->description);
    }
}
