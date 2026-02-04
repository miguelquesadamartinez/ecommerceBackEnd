<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class outOdersSentToNomane extends Command
{
    protected $signature = 'out:OdersSentToNomane';
    protected $description = 'out Oders Sent To NoName';
    public function handle() {
        NomaneHelper::doApiCommandCall('/out/orders-sent-to-noName', $this->description);
    }
}
