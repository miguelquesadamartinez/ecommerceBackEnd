<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class inProductControlFile extends Command
{
    protected $signature = 'in:productControlFile';
    protected $description = 'in Product Control File';
    public function handle() {
        NomaneHelper::doApiCommandCall('/in/product-control-file', $this->description);
    }
}
