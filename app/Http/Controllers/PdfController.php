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
      $order->statuses->bill = 1;
      $order->push();
      if ($request->input('sort') == 'name') {
        $order->products = $order->products->sortBy('name');
      } else {
        $order->products = $order->products->sortBy(function($product) {
            $hash = ($product->sort1) ? $product->sort1 : 0;
            return $product->sort1.$product->name;
        });
      }
      $with_discount = $request->input('with_discount');
      $sums = array(
        'quantity' => 0,
        'price' => 0
      );

      foreach ($order->products as $product) {
        $product->pdf_price = ($product->order_price != null) ? $product->order_price : $product->price;
        $sums['quantity'] += $product->quantity;
        $sums['price'] += $product->quantity * ($product->pdf_price - $product->pdf_price * $product->discount / 100);
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
      $GLOBALS['height'] = 0;
      $dompdf = $pdf->getDomPDF();

      $dompdf->setCallbacks(
          array(
            'myCallbacks' => array(
              'event' => 'end_frame', 'f' => function ($infos) {
                $frame = $infos["frame"];
                if (strtolower($frame->get_node()->nodeName) === "body") {
                    $padding_box = $frame->get_padding_box();
                    $GLOBALS['height'] = $padding_box['h'];
                }
              }
            )
          )
        );
      $pdf->output();
      //dd('test');
      return $pdf->download('order-'.$order->prom_id.'-'.$GLOBALS['height'].'.pdf');
  }

}
