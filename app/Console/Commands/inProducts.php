<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class inProducts extends Command
{
    protected $signature = 'in:poducts';
    protected $description = 'In Products';
    public function handle() {
        PfizerHelper::doApiCommandCall('/in/products', $this->description);
    }
}
