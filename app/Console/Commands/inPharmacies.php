<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class inPharmacies extends Command
{
    protected $signature = 'in:pharmacies';
    protected $description = 'In Pharmacies';
    public function handle() {
        NomaneHelper::doApiCommandCall('/in/pharmacies', $this->description);
    }
}
