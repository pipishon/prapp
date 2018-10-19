<?php

namespace App\Http\Controllers;

use App\PromApi;
use App\Order;
use App\NewPostCity;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index ()
    {
        $prom_api ='58999770';
        $order = Order::where('prom_id', $prom_api)->first();
        dd($order->validet);
    }
}
