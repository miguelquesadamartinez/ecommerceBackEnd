<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class outOdersSentToPfizer extends Command
{
    protected $signature = 'out:OdersSentToPfizer';
    protected $description = 'out Oders Sent To NoName';
    public function handle() {
        PfizerHelper::doApiCommandCall('/out/orders-sent-to-noName', $this->description);
    }
}
