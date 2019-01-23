<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NumberToWords\NumberToWords;
use Carbon\Carbon;
use App\Order;

class PdfController extends Controller
{
  public function invoice($id, Request $request)
  {
      $order = Order::find($id);
      $with_discount = $request->input('with_discount');
      $sums = array(
        'quantity' => 0,
        'price' => 0
      );
      foreach ($order->products as $product) {
        $sums['quantity'] += $product->quantity;
        $sums['price'] += $product->quantity * ($product->price - $product->price * $product->discount / 100);
      }
      $numberToWords = new NumberToWords();
      $currencyTransformer = $numberToWords->getCurrencyTransformer('ru');
      $title = $currencyTransformer->toWords($sums['price']*100, 'UAH');
      $sums['text'] =  mb_strtoupper(mb_substr($title, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($title, 1, null,'UTF-8');
      $data = array(
        'customer' => $order->client_first_name.' '.$order->client_last_name,
        'order_id' => $order->prom_id,
        'date' => Carbon::parse($order->prom_date_created)->format('d.m.Y'),
        'products' => $order->products,
        'sums' => $sums,
        'with_discount' => $with_discount
      );
      $pdf = \PDF::loadView('pdf.invoice', array('data' => $data));
      $pdf->setPaper(array(0, 0, 595.28, 841.89));
      return $pdf->download('order-'.$order->prom_id.'.pdf');
  }

  public function view($id)
  {
      $order = Order::find($id);
      $sums = array(
        'quantity' => 0,
        'price' => 0
      );
      foreach ($order->products as $product) {
        $sums['quantity'] += $product->quantity;
        $sums['price'] += $product->quantity * $product->prom_price;
      }
      $numberToWords = new NumberToWords();
      $currencyTransformer = $numberToWords->getCurrencyTransformer('ru');
      $title = $currencyTransformer->toWords($sums['price']*100, 'UAH');
      $sums['text'] =  mb_strtoupper(mb_substr($title, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($title, 1, null,'UTF-8');
      $data = array(
        'customer' => $order->client_first_name.' '.$order->client_last_name,
        'order_id' => $order->prom_id,
        'date' => Carbon::parse($order->prom_date_created)->format('d.m.Y'),
        'products' => $order->products,
        'sums' => $sums
      );
      return view('pdf.invoice', array('data' => $data));
  }
}
