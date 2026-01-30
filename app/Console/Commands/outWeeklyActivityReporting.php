<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class outWeeklyActivityReporting extends Command
{
    protected $signature = 'out:weeklyActivityReporting';
    protected $description = 'out Weekly Activity Reporting';
    public function handle() {
        PfizerHelper::doApiCommandCall('/out/weekly-order-confirmations', $this->description);
    }
}
