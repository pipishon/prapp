<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;

class MassActionController extends Controller
{
  public function statusDelivered (Request $request)
  {
    $ids = $request->input('ids');
    $api = new PromApi;
    $orders = Order::whereIn('id', $ids)->get();
    foreach ($orders as $order) {
      $order->status = 'delivered';
      $order->save();
      $api->setOrderStatus($order->prom_api, 'delivered');
    }
    return $ids;

  }
}
