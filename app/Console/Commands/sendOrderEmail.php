<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Mail\OrderMail;
use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class sendOrderEmail extends Command
{
    protected $signature = 'test:sendOrderEmail';
    protected $description = 'Send Order Email';

    public function handle() {

        $order = Order::find(27);

        Mail::to(env('EMAIL_FOR_APP_TEST'))->send(new OrderMail($order, $order->pharmacy, $order->order_reference));
    }
}
