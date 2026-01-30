<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class outQuarterlyActivityReporting extends Command
{
    protected $signature = 'out:quarterlyActivityReporting';
    protected $description = 'out Quarterly Activity Reporting';
    public function handle() {
        PfizerHelper::doApiCommandCall('/out/quarterly-activity-reporting', $this->description);
    }
}
