<?php

namespace App\Http\Controllers;

use App\Order;
use App\PromApi;
use Illuminate\Http\Request;

class MassActionController extends Controller
{
  public function statusDelivered (Request $request)
  {
    /*$ids = $request->input('ids');
    $api = new PromApi;
    $orders = Order::whereIn('id', $ids)->get();
    $responce = array();
    foreach ($orders as $order) {
      $order->status = 'delivered';
      $order->save();
      $responce[] = $api->setOrderStatus($order->prom_id, 'delivered');
    }
    return $responce;*/
    return '';

  }
}
