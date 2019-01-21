<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\OrderProduct;


class OrderProductController extends Controller
{
    public function update ($id, Request $request)
    {
        $product = OrderProduct::find($id);
        $product->update($request->only(['discount']));
    }

    public function massDiscount (Request $request)
    {
      $products = $request->only(['items'])['items'];
      foreach ($products as $product) {
        OrderProduct::find($product['id'])->update(array(
          'discount' => $product['discount']
        ));
      }
    }
}
