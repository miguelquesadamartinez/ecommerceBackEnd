<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class outWeeklyActivityReporting extends Command
{
    protected $signature = 'out:weeklyActivityReporting';
    protected $description = 'out Weekly Activity Reporting';
    public function handle() {
        NomaneHelper::doApiCommandCall('/out/weekly-order-confirmations', $this->description);
    }
}
