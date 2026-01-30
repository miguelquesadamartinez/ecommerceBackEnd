<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class inPharmacies extends Command
{
    protected $signature = 'in:pharmacies';
    protected $description = 'In Pharmacies';
    public function handle() {
        PfizerHelper::doApiCommandCall('/in/pharmacies', $this->description);
    }
}
