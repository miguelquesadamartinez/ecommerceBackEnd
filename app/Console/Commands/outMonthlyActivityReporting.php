<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class outMonthlyActivityReporting extends Command
{
    protected $signature = 'out:monthlyActivityReporting';
    protected $description = 'out Monthly Activity Reporting';
    public function handle() {
        NomaneHelper::doApiCommandCall('/out/monthly-activity-reporting', $this->description);
    }
}
