<?php

namespace App\Http\Controllers;

use App\PromApi;
use App\Order;
use App\OrderProduct;
use App\Product;
use App\Customer;
use App\NewPostCity;
use App\NewPostApi;
use App\NewPostTtnTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NumberToWords\NumberToWords;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Settings;
use App\SputnikEmail;
use App\MessageEmail;

class TestController extends Controller
{

    public function actionProccess($action, Request $request)
    {
        if (method_exists($this, $action)) {
            $this->{$action}($request);
        }
    }

    public function viewfeedback (Request $request)
    {
        $params = array('hash' => 'hash', 'vote' => 1);
        $request->session()->put('vote', 1);
        echo view('vote_success', $params);
        //return redirect()->route('vote.success')->with(['vote' => 7]);
        return;
    }

    public function sendfeedback ($order_id = null, Request $request)
    {
      if ($order_id == null) {
        $order_id = $request->input('order_id');
      }
        if (Order::where('prom_id', $order_id)->first() == null) {
            dd('Неверный id заказа');
        }
        if (MessageEmail::where('order_id', $order_id)->where('type', 'feedback')->first() != null)  return;

        $email = 'info@helgamade.com.ua';

        $ivlen = openssl_cipher_iv_length('AES-128-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $iv = 'p/Ȅ����';
        $params = array(
            'hash' => rawurlencode(openssl_encrypt($order_id, 'AES-128-CBC', 'sercet', 0, $iv)),
            'order_id' => $order_id
        );

        $sputnik_email = SputnikEmail::where('email', $email)->first();
        if (!$sputnik_email) {
            $sputnik_email = SputnikEmail::create(['email' => $email]);
            $sputnik_email->subscribe();
        }

        $message_email = MessageEmail::create(array(
            'email' => $email,
            'order_id' => $order_id,
            'type' => 'feedback',
            'send_at' => Carbon::now()
        ));

        $sputnik_email->sendEvent('api-send-feedback', $params);
    }

    public function feedbackcron ()
    {
        $np = DB::table('orders')
            ->select('orders.prom_id')
            ->join('new_post_ttn_tracks', 'orders.id', 'new_post_ttn_tracks.order_id')
            ->join('message_emails', 'orders.prom_id', 'message_emails.order_id')
            ->whereIn('orders.delivery_option', ['Новая Почта', 'НП без риска'])
            ->whereRaw('new_post_ttn_tracks.date_received = CURDATE() - INTERVAL 1 DAY')
            ->whereRaw('ABS(MINUTE(orders.prom_date_created) - MINUTE(NOW())) < 30')
            ->whereRaw('HOUR(orders.prom_date_created) = HOUR(NOW())')
            ->whereNotIn('orders.prom_id', function($query) {
              $query->select('message_emails.order_id')->where('message_emails.type', 'feedback')
              ->where('message_emails.order_id', 'orders.prom_id');
            })
            ->get()->pluck('prom_id')->toArray();
        $pickup = DB::table('orders')
            ->select('orders.prom_id')
            ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
            ->join('message_emails', 'orders.prom_id', 'message_emails.order_id')
            ->where('orders.delivery_option', 'Самовывоз')
            ->whereRaw('order_statuses.delivered = CURDATE() - INTERVAL 1 DAY')
            ->whereRaw('ABS(MINUTE(orders.prom_date_created) - MINUTE(NOW())) < 30')
            ->whereRaw('HOUR(orders.prom_date_created) = HOUR(NOW())')
            ->whereNotIn('orders.prom_id', function($query) {
              $query->select('message_emails.order_id')->where('message_emails.type', 'feedback')
              ->where('message_emails.order_id', 'orders.prom_id');
            })
            ->get()->pluck('prom_id')->toArray();
        $res = array_merge($np, $pickup);
        foreach ($res as $order_id) {
          $this->sendfeedback($order_id);
        }
    }


    public function feedback ()
    {
        $np = DB::table('orders')
            ->select('orders.prom_id', 'new_post_ttn_tracks.date_received', 'orders.prom_date_created' )
            ->join('new_post_ttn_tracks', 'orders.id', 'new_post_ttn_tracks.order_id')
            ->join('message_emails', 'orders.prom_id', 'message_emails.order_id')
            ->whereIn('orders.delivery_option', ['Новая Почта', 'НП без риска'])
            ->whereRaw('new_post_ttn_tracks.date_received = CURDATE() - INTERVAL 1 DAY')
            ->whereRaw('ABS(MINUTE(orders.prom_date_created) - MINUTE(NOW())) < 30')
            ->whereRaw('HOUR(orders.prom_date_created) = HOUR(NOW())')
            ->whereNotIn('orders.prom_id', function($query) {
              $query->select('message_emails.order_id')->where('message_emails.type', 'feedback')
              ->where('message_emails.order_id', 'orders.prom_id');
            })
            ->get()->toArray();
        echo 'Новая почта';
        echo '<pre>';
        print_r($np);
        echo '</pre>';
        $pickup = DB::table('orders')
            ->select('orders.prom_id', 'orders.prom_date_created', 'order_statuses.delivered' )
            ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
            ->join('message_emails', 'orders.prom_id', 'message_emails.order_id')
            ->where('orders.delivery_option', 'Самовывоз')
            ->whereRaw('order_statuses.delivered = CURDATE() - INTERVAL 1 DAY')
            ->whereRaw('ABS(MINUTE(orders.prom_date_created) - MINUTE(NOW())) < 30')
            ->whereRaw('HOUR(orders.prom_date_created) = HOUR(NOW())')
            ->whereNotIn('orders.prom_id', function($query) {
              $query->select('message_emails.order_id')->where('message_emails.type', 'feedback')
              ->where('message_emails.order_id', 'orders.prom_id');
            })
            ->get()->toArray();
        echo 'Самовывоз';
        echo '<pre>';
        print_r($pickup);
        echo '</pre>';
    }



    public function instagram ()
    {
        $inst_ids = Customer::all()->pluck('instagram_id');
        $i = 0;
        foreach ($inst_ids as $inst_id) {
            if ($inst_id != null) {
                $i++;
                echo '<div>'.$inst_id.'</div>';
            }
        }
        echo '<div><strong>'.'Кол-во:'.$i.'</strong></div>';
      return ;
    }

    public function getProductDiscounts ($products)
    {
        $product_ids = Product::whereIn('prom_id', array_column($products, 'id'))->get()->pluck('id', 'prom_id')->toArray();
        foreach ($products as $key => $product) {
            if (isset($product_ids[$products[$key]['id']])) {
                $products[$key]['prom_id'] = $products[$key]['id'];
                $products[$key]['id'] = $product_ids[$products[$key]['prom_id']];
            }
        }
        $discount_ids = DB::table('discount_products')->select('discount_id', 'product_id')
            ->whereIn('product_id', $product_ids)->get()->pluck('discount_id', 'product_id')->toArray();
        $discounts = array();
        foreach ($products as $product) {
            foreach ($discount_ids as $product_id => $discount_id) {
                if ($product_id == $product['id']) {
                    if (isset($discounts[$discount_id])){
                        $discounts[$discount_id] += $product['quantity'];
                    } else {
                        $discounts[$discount_id] = $product['quantity'];
                    }
                }
            }
        }
        $discount_vals = DB::table('discounts')->select('vals', 'id')->whereIn('id', array_values($discount_ids))->get()->pluck('vals', 'id')->toArray();

        $product_discounts = array();

        foreach ($discount_vals as $discount_id => $discount_val) {
            $vals = unserialize($discount_val);
            $percent = 0;
            foreach ($vals as $val) {
              if ($val['qty'] <= $discounts[$discount_id]) {
                  $percent = $val['percent'];
              }
            }
            foreach ($products as $product) {
                foreach ($discount_ids as $product_id => $ds_id) {
                    if ($product_id == $product['id'] && $ds_id == $discount_id) {
                        $product_discounts[$product_id] = (float) $percent;
                    }
                }
            }
        }
        return $product_discounts;
    }

    public function test1 (Request $request)
    {
        $ivlen = openssl_cipher_iv_length('AES-128-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $iv = 'p/Ȅ����';
        $params = array(
            'hash' => rawurlencode(openssl_encrypt('100', 'AES-128-CBC', 'sercet', 0, $iv)),
        );
        $sputnik_email = SputnikEmail::where(['email' => 'info@helgamade.com.ua'])->first();
        $sputnik_email->sendEvent('api-send-feedback', $params);
        dd();

   /*     $prom_id = 76160073;
        $api = new PromApi;
        $order = $api->getItem($prom_id, 'orders');
        $products = $order['order']['products'];
        dd($this->getProductDiscounts($products));

  public function getProductDiscount($O_product, $product_price, $customer, $total_price) {
      $total_price = (float) str_replace(',', '.', preg_replace('/\s+/u', '', $total_price));
      $prom_discount = 0;
      if ($O_product->price != $product_price) {
        $prom_discount = ($O_product->price - $product_price) * 100 / $O_product->price;
      }
      $customer_discounts = array(500 => 2, 1000 => 3, 2000 => 4, 3000 => 5, 5000 => 6, 10000 => 7);
  }*/
        $prom_discount = 5;

      $customer = Customer::find(2);
      $product = Product::find(1586);
      $product_quantity = 2;
      $total_price = "501 грн.";
      $product_price = "100";
      $total_price = (float) str_replace(',', '.', preg_replace('/\s+/u', '', $total_price));
      $price_discounts = array(500 => 2, 1000 => 3, 2000 => 4, 3000 => 5, 5000 => 6, 10000 => 7);
      $num_orders_discount = ($customer->statistic->count_orders > 2) ? 2 : $customer->statistic->count_orders - 1;
      $price_discount = 0;

      foreach ($price_discounts as $val => $percent) {
          if ($val <= $total_price) {
              $price_discount = $percent;
          }
      }
      $customer_discount = $price_discount + $num_orders_discount;

      $product_discounts = DB::table('discounts')->select('vals')
          ->join('discount_products', 'discounts.id', 'discount_products.discount_id')
          ->where('discount_products.product_id', $product->id)->first();
      $product_discount = 0;
      if ($product_discounts != null) {
          $product_discounts = unserialize($product_discounts->vals);
          foreach ($product_discounts as $discount) {
              if ($discount['qty'] <= $product_quantity) {
                $product_discount = (float) $discount['percent'];
              }
          }
      }

      $discount = 0;
      if ($prom_discount > $product_discount && $prom_discount > $customer_discount) {
          $discount = $prom_discount;
      } else {
          $discount = $customer_discount;
          if ($product_discount > $customer_discount) {
              $discount = $product_discount;
          }
      }

      dd($discount);
      /*$order = Order::find(1);
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
        'with_discount' => false
      );
      $pdf = \PDF::loadView('pdf.invoice', array('data' => $data));
      $pdf->setPaper(array(0, 0, 595.28, 841.89));
      $GLOBALS['height'] = 0;
      $dompdf = $pdf->getDomPDF();

      $pdf->getDomPDF()->setCallbacks(
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
      $output = $pdf->output();
      $filename = 'order-'.$order->prom_id.'-'.$GLOBALS['height'].'.pdf';
       */
      //dd('test');
      /*return new Response($output, 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>  'attachment; filename="'.$filename.'"'
        ));

        $ivlen = openssl_cipher_iv_length('AES-128-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $iv = 'p/Ȅ����';
        var_dump($iv);
        $crypt = rawurlencode(openssl_encrypt('100', 'AES-128-CBC', 'sercet', 0, $iv));
        var_dump($crypt);
*/
        //var_dump(str_replace(base64_encode(url('test/test1/'))));
        /*DB::table('customer_statistics')->select('*')->update([
            'count_orders' => DB::Raw('count_orders_delivered + count_orders_received')
        ]);*/
        return ;

    }

    public function test2 (Request $request)
    {
        $sku = $request->input('sku');
        $order_prom_id = $request->input('order_id');
        $product = Product::where('sku', $sku)->first();
        $order = Order::where('prom_id', $order_prom_id)->first();
        $order_product = OrderProduct::where('product_id', $product->id)
            ->where('order_id', $order->id)->first();
        echo 'Оплаченные: ';
        echo $order_product->getSamePayedAttribute(0);
        echo '<br />';
        echo '<table>';
        $same = DB::table('order_products')
              ->join('orders', 'orders.id', 'order_products.order_id')
              ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
              ->select('orders.client_first_name','orders.client_last_name', 'orders.prom_id', 'order_products.quantity')
              ->where('orders.status', 'received')
              ->where('order_products.product_id', $order_product->product_id)
              ->where('order_products.order_id', '<>', $order_product->order_id)
              ->where('order_statuses.payment_status', 'Оплачен')
              ->where('order_statuses.collected', '0')
              ->get();
        foreach ($same as $item) {
            echo '<tr>';
            echo '<td>'.$item->prom_id.'</td>';
            echo '<td>'.$item->client_first_name.' '.$item->client_last_name.'</td>';
            echo '<td>'.$item->quantity.'</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo 'Не оплаченные:';
        echo $order_product->getSameNotPayedAttribute(0);
        echo '<br />';
        $same = DB::table('order_products')
              ->join('orders', 'orders.id', 'order_products.order_id')
              ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
              ->select('orders.client_first_name','orders.client_last_name', 'orders.prom_id', 'order_products.quantity')
              ->where('orders.status', 'received')
              ->where('order_products.product_id', $order_product->product_id)
              ->where('order_products.order_id', '<>', $order_product->order_id)
              ->where('order_statuses.payment_status', 'Не оплачен')
              ->where('order_statuses.collected', '0')
              ->get();
        echo '<table>';
        foreach ($same as $item) {
            echo '<tr>';
            echo '<td>'.$item->prom_id.'</td>';
            echo '<td>'.$item->client_first_name.' '.$item->client_last_name.'</td>';
            echo '<td>'.$item->quantity.'</td>';
            echo '</tr>';
        }
        echo '</table>';

    }

    public function index (Request $request)
    {
/*        $api = new PromApi;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://prom.ua/captcha');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result;
        return var_dump($api->getList('orders'));
        /*$orders = Order::whereDate('prom_date_created', '>', Carbon::parse('01-07-2018'))->paginate(20);
        $api = new PromApi;
        foreach ($orders as $order) {
            $order->updateFromApi();
        }
    }*/
        $product_sku = $request->input('sku');
        $product_id = Product::where('sku', $product_sku)->first()->id;
        $product_ids = Product::all()->pluck('id');
        $months = DB::table('orders')
            ->join('order_products', 'orders.id', 'order_products.order_id')
            ->join('products', 'products.id', 'order_products.product_id')
                ->where('orders.status', 'delivered')
                ->where('order_products.product_id', $product_id)
                ->select('orders.prom_id', 'order_products.quantity', DB::raw('YEAR(orders.prom_date_created) year, MONTH(orders.prom_date_created) month'), DB::raw('CONCAT(YEAR(orders.prom_date_created), "_" , MONTH(orders.prom_date_created)) as date'))
                ->orderBy('year')
                ->orderBy('month')
                ->get()->toArray();
echo '<table style="text-align: center"><thead><th>id</th><th>date</th><th>quantity</th></thead>';
echo '<tbody>';
        foreach ($months as $row) {
            echo '<tr>';
            echo '<td style="padding: 2px 5px">' . $row->prom_id . '</td><td>'. $row->date. '</td><td>'. $row->quantity . '</td>';
            echo '</tr>';
        }
echo '</tbody></table>';
    }
    /*
        $ttn = $request->input('ttn');
        $id = $request->input('id');
        $np = new NewPostApi;
        $orders = Order::with('ttn')->whereHas('ttn')->doesntHave('ttntrack')->limit(20)->get();
        $ttns = $orders->pluck('ttn.int_doc_number');

        $tracks = $np->track($ttns)['data'];
        foreach ($tracks as $track) {
            $order = $orders->filter(function ($item) use ($track) {
                return $item->ttn->int_doc_number == $track['Number'];
            })->first();
            $status = $track['StatusCode'];
            if ($status == 2) continue; // if ttn deleted
            $np_track = NewPostTtnTrack::updateOrCreate(array('order_id' => $order->id),
                array(
                    'customer_id' => $order->customer->id,
                    'prom_id' => $order->prom_id,
                    'ref' => $track['RefEW'],
                    'int_doc_number' => $track['Number'],
                    'estimate_delivery_date' => isset($track['ScheduledDeliveryDate']) ? Carbon::parse($track['ScheduledDeliveryDate']) : $oreder->ttn->estimated_delivery_date,
                    'status' => $track['Status'],
                    'status_code' => (int) $track['StatusCode'],
                    'redelivery' => $track['Redelivery'],
                    'redelivery_sum' => $track['RedeliverySum'],
                    'phone' => isset($track['PhoneRecipient']) ? $track['PhoneRecipient'] : $oreder->ttn->phone,
                    'full_name' => isset($track['RecipientFullNameEW']) ? $track['RecipientFullNameEW'] : $oreder->ttn->name,
                    'city' => $track['CityRecipient'],
                    'warehouse' => $track['WarehouseRecipient'],
                    'warehouse_ref' => $track['WarehouseRecipientRef'],
                    'recipient_address' => $track['RecipientAddress'],
                    'date_created' => Carbon::parse($track['DateCreated']),
                    'date_first_day_storage' => isset($track['DateFirstDayStorage']) ? Carbon::parse($track['DateFirstDayStorage']) : null,
                    'document_weight' => $track['DocumentWeight'],
                    'check_weight' => $track['CheckWeight'],
                    'document_cost' => $track['DocumentCost'],
                )
            );
            switch ($status) {
                case 7:
                case 8:
                    if ($np_track->date_delivered == null) {
                        $np_track->date_delivered = Carbon::now();
                    }
                    break;
                case 9:
                case 10:
                case 11:
                    if ($np_track->date_received == null) {
                        $np_track->date_received = Carbon::now();
                    }
                    break;

            }
            $today = Carbon::now();
            if ($np_track->date_delivered == null) {
                $np_track->send_days = $today->diffInDays(Carbon::parse($np_track->date_created));
            }
            if ($np_track->date_received == null && $np_track->date_delivered != null) {
                $np_track->delivery_days = $today->diffInDays(Carbon::parse($np_track->date_delivered));
            }
            $np_track->save();
        }
     */
}
