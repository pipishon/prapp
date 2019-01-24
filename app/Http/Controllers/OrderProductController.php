<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\OrderProduct;


class OrderProductController extends Controller
{
    public function update ($id, Request $request)
    {
        $order_product = OrderProduct::find($id);
        $order_product->discount = $request->input('discount');
        $order_product->save();
        return $order_product;
    }

    public function massDiscount (Request $request)
    {
      $products = $request->only(['items'])['items'];
      foreach ($products as $product) {
        $order_product = OrderProduct::find($product['id']);
        $order_product->discount = $product['discount'];
        $order_product->save();
      }
    }
}
