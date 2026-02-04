<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class inProducts extends Command
{
    protected $signature = 'in:poducts';
    protected $description = 'In Products';
    public function handle() {
        NomaneHelper::doApiCommandCall('/in/products', $this->description);
    }
}
