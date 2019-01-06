<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pack;

class PackController extends Controller
{
  public function show ($product_id)
  {
    return Pack::where('product_id', $product_id)->get();
  }

  public function update ($product_id, Request $request)
  {
    $items = $request->input('items');
    Pack::where('product_id', $product_id)->delete();
    foreach ($items as $item) {
      if (isset($item['item_name'])) {
        Pack::create($item);
      }
    }

  }
    //
}
