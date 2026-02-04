<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class outProductAudit extends Command
{
    protected $signature = 'out:productAudit';
    protected $description = 'Generate report of product audit changes';

    public function handle()
    {
        NomaneHelper::doApiCommandCall('/out/product-audit', $this->description);
    }
}
