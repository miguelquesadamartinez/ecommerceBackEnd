<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class outQuarterlyActivityReporting extends Command
{
    protected $signature = 'out:quarterlyActivityReporting';
    protected $description = 'out Quarterly Activity Reporting';
    public function handle() {
        NomaneHelper::doApiCommandCall('/out/quarterly-activity-reporting', $this->description);
    }
}
