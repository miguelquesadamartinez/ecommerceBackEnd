<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class outTwoWeekActivityReporting extends Command
{
    protected $signature = 'out:twoWeekActivityReporting';
    protected $description = 'out Two Week Activity Reporting';
    public function handle() {
        NomaneHelper::doApiCommandCall('/out/rolling-order-history', $this->description);
    }
}
