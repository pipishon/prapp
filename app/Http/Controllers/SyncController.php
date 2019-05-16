<?php

namespace App\Http\Controllers;

use App\PromApi;

use App\Order;
use App\Product;
use App\Message;
use App\OrderProduct;
use App\Customer;
use App\Phone;
use App\Email;
use App\OrderStatus;
use App\Settings;
use App\MessageTemplate;
use App\Sms;
use App\SputnikEmail;
use App\MessageEmail;
use App\Http\Controllers\AutoReceiveController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\NewPostApi;
use App\NewPostCity;
use App\NewPostWarehouse;
use App\Dictionary;
use App\Cron;

use App\AutoReply;

use Illuminate\Http\Request;

class SyncController extends Controller
{
  public function products()
  {
    //$api = new PromApi;
    //$products = $api->getAllProducts();
    //var_dump($products);
    // TODO save products to database
  }

    public function OrderProducts($orders = null)
    {
        $cron = Cron::find(9);
        $cron->last_job = Carbon::now();
        $cron->save();
        $api = new PromApi;
        $orders = $api->getOpenOrders();
        foreach ($orders as $prom_order) {
            $prom_products = $prom_order['products'];
            $order = Order::where('prom_id', $prom_order['id'])->first();
            if ($order == null) continue;
            //$order->products()->delete();
            $ids = array();
            foreach ($prom_products as $prom_product) {
                $product = Product::where('prom_id', $prom_product['id'])->first();
                if ($product == null) {
                    continue;
                }
                $ids[] = $product->id;
                $product_price = floatval(str_replace(',', '.', $prom_product['price']));
                $order_product_update = array(
                    'prom_price' => $product_price,
                    'quantity' => str_replace(',','.', $prom_product['quantity']),
                );
                if (!OrderProduct::where('product_id', $product->id)
                    ->where('order_id', $order->id)->exists() &&
                    $product->price != $product_price) {
                    $order_product_update['discount'] = ($product->price - $product_price) * 100 / $product->price;
                }
                $order_product = OrderProduct::updateOrCreate(array(
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                ), $order_product_update); /*array(
                    'quantity' => str_replace(',','.', $prom_product['quantity']),
                    'prom_price' => floatval(str_replace(',', '.', $prom_product['price'])),
                ));*/
            }
            OrderProduct::where('order_id', $order->id)->whereNotIn('product_id', $ids)->delete();
            $price = preg_replace('/\s+/u', '', $prom_order['price']);
            $price = str_replace(',','.', $price);
            $order->price = floatval($price);
            $order->save();
        }
    }

  public function orders($orders = null)
  {

    $cron = Cron::find(8);
    $cron->last_job = Carbon::now();
    $cron->save();
    $api = new PromApi;
    if ($orders == null) {
      $orders = $api->getOpenOrders();
    }
    $new_orders_id = array();
    $result = array('test');
    foreach ($orders as $order) {
      $new_orders_id[] = $order['id'];

      $ord = Order::where('prom_id', $order['id'])->first();


      if (!empty($ord)) {
        $ord->status = $order['status'];
        $ord->save();
        continue;
      }
      $customer_id = 0;

      $phone = Phone::where('phone', $order['phone'])->first();
      if (!empty($phone)) {
        $customer_id = $phone->customer_id;
      }
      if ($customer_id == 0) {
        $email = Email::where('email', $order['email'])->first();
        if (!empty($email)) {
          $customer_id = $email->customer_id;
        }
      }

      $customer = Customer::firstOrNew(array('id' => $customer_id));

      $customer_name = trim($order['client_first_name'].' '.$order['client_last_name']);

      if (!in_array($customer_name, $customer->name)) {
        $customer->name = $customer_name;
      }

      if ($customer->auto_status == null) {
        $customer->auto_status = 'new';
      }

      $date = new \DateTime($order['date_created']);
      $date->setTimezone(new \DateTimeZone('Europe/Kiev'));
      $date_str = $date->format('Y-m-d H:i');

      $customer->save();

      if ($order['phone'] != '') {
        $phone = Phone::firstOrCreate(array(
          'customer_id' => $customer->id,
          'phone' => $order['phone'],
        ));
      }

      if ($order['email'] != '') {
        $email = Email::firstOrCreate(array(
          'customer_id' => $customer->id,
          'email' => $order['email'],
        ));
      }

      $payments = Dictionary::where('payment', '1')->get()->pluck('to', 'from');
      $deliveries = Dictionary::where('delivery', '1')->get()->pluck('to', 'from');

      $delivery = trim((string) $order['delivery_option']['name']);
      $delivery = isset($deliveries[$delivery]) ? $deliveries[$delivery] : 'не указан';

      $payment = trim((string) $order['payment_option']['name']);
      $payment = isset($payments[$payment]) ? $payments[$payment] : 'не указан';

      $O_order = Order::firstOrCreate(array('prom_id' => $order['id']),
        array(
          'prom_id' => $order['id'],
          'status' => $order['status'],
          'customer_id' => $customer->id,
          'delivery_option' => $delivery,
          'delivery_address' =>(string) $order['delivery_address'],
          'payment_option' => $payment,
          'price' => $order['price'],
          'phone' => (string) $order['phone'],
          'email' => (string) $order['email'],
          'client_first_name' => (string) $order['client_first_name'],
          'prom_date_created' => $date_str,
          'client_notes' => (string) $order['client_notes'],
          'client_last_name' => (string) $order['client_last_name'],
          'source' => (string) $order['source'],
          'comment' => ''
        ));


      $auto_receive = Settings::where('name', 'auto_receive_order')->first()->value;
      if ($auto_receive && $order['status'] == 'pending') {
        $api->setOrderStatus($order['id'], 'received');
        $o = Order::where('prom_id', $order['id'])->first();
        if (!is_null($o)) {
          $o->status = 'received';
          $o->save();
        }
        $template = AutoReceiveController::template();
        if ($template) {
          $message = str_replace(array(
            '$name$',
            '$id$',
            '$price$'
          ), array(
            $order['client_first_name'].' '.$order['client_last_name'],
            $order['id'],
            str_replace('.', '', $order['price'])
          ), $template);
          $sms = Sms::firstOrCreate(array('order_id' => $order['id'], 'type' => 'auto'));
          $sms->message = $message;
          $sms->phone = $order['phone'];//'+380679325925';//'+380683223527';//$order['phone'];
          $result = $sms->sendSms();
        }

      }


      $group_discounts = $this->getProductGroupDiscounts($order['products']);

      foreach ($order['products'] as $product) {
        $product_price = $product['price'];
        $product_price = preg_replace('/\s+/u', '', $product_price);
        $product_price = str_replace(',','.', $product_price);
        $product_price = floatval($product_price);
        $O_product = Product::updateOrCreate(array('prom_id' => $product['id']),
            array(
              'sku' => $product['sku'],
              'name' => $product['name'],
            //  'price' => $product_price,
              'main_image' => (string) $product['image'],
              'presence' => 'available',
              'status' => 'on_display',
            ));
          $order_product_update = array(
            'prom_price' => $product_price,
            'quantity' => str_replace(',','.', $product['quantity']),
        );
        $discount_arr = $this->getProductDiscount(
            $customer,
            $O_product,
            str_replace(',','.', $product['quantity']),
            $order['price'],
            $product_price,
            $group_discounts
        );
        if ($discount_arr['discount'] != 0) {
            $order_product_update['discount'] = $discount_arr['discount'];
            $order_product_update['discount_descripiton'] = $discount_arr['type'];
        }

          $order_product = OrderProduct::updateOrCreate(array(
            'product_id' => $O_product->id,
            'order_id' => $O_order->id,
            ),
            $order_product_update
          );
      }
      if (isset($order['email'])) {
        $sputnik_email = SputnikEmail::firstOrCreate(['email' => $order['email']],
            ['first_name' => $order['client_first_name'], 'last_name' => $order['client_last_name']]);
        $sputnik_email->subscribe();
      }

        $order_crm = Order::where('prom_id', $order['id'])->first();
        OrderStatus::firstOrCreate(array('order_id' => $order_crm->id));
        $customer->recalcStatistics();

    }

    foreach ($new_orders_id as $id) {
        $order_crm = Order::where('prom_id', $id)->first();
        OrderStatus::firstOrCreate(array('order_id' => $order_crm->id));
    }

    $orders_to_update_status = Order::whereIn('status', ['pending', 'received'])->whereNotIn('prom_id', $new_orders_id)->get();
    foreach ($orders_to_update_status as $order) {
      $status = $api->getItem($order->prom_id, 'orders')['order']['status'];
      $order->status = $status;
      $order_status = OrderStatus::firstOrCreate(array('order_id' => $order->id));
      if ($status == 'delivered') {
        $order_status->delivered = Carbon::now('Europe/Kiev')->format('Y-m-d H:i:s');
      }
      $order_status->save();
      $order->save();
      $customer = Customer::find($order->customer_id);
      $customer->recalcStatistics();
    }
    print_r('Get orders '.count($orders));
    print_r('Statuses updated '.count($orders_to_update_status));
  }

  public function getProductDiscount($customer, $product, $product_quantity, $total_price, $product_price, $group_discounts) {
      $total_price = (float) str_replace(',', '.', preg_replace('/\s+/u', '', $total_price));
      $prom_discount = 0;
      if ($product->price != $product_price) {
        $prom_discount = ($product->price - $product_price) * 100 / $product->price;
      }

      if ($customer->statistic) {
          $num_orders = ($customer->statistic->count_orders > 2) ? 2 : $customer->statistic->count_orders - 1;
      } else {
          $num_orders = 1;
      }

      $customer_discount = 0;
        $table_discounts = json_decode(Settings::where('name','table_discounts')->value('value'), true);
        if (isset($table_discounts['enable']) && $table_discounts['enable']) {
            $price_key = 0;
            $qty_key = 0;
            foreach ($table_discounts['prices'] as $key => $price) {
                if ($total_price < (int) $price) {
                    $price_key = $key + 1;
                }
            }
            foreach ($table_discounts['quantities'] as $key => $qty) {
                if ($num_orders >= (int) $qty) {
                    $qty_key = $key;
                }
            }
            $customer_discount = (float) $table_discounts['vals'][$price_key][$qty_key];
        }
        $product_discount = 0;
      if (isset($group_discounts[$product->id])) {
          $product_discount = $group_discounts[$product->id];
      }


      $discount = 0;
      $discount_type = '';
      if ($prom_discount > $product_discount && $prom_discount > $customer_discount) {
          $discount = $prom_discount;
          $discount_type = 'prom';
      } else {
          $discount = $customer_discount;
          $discount_type = 'customer';
          if ($product_discount > $customer_discount) {
              $discount = $product_discount;
              $discount_type = 'product';
          }
      }
      return array('discount' => $discount, 'type' => $discount_type);
  }

    public function getProductGroupDiscounts ($products)
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

  public function smsStatus () {
    $cron = Cron::find(7);
    $cron->last_job = Carbon::now();
    $cron->save();
    $statuses = array(
      'NO_DATA',
      'WRONG_DATA_FORMAT',
      'REQUEST_FORMAT',
      'AUTH_DATA',
      'API_DISABLED',
      'USER_NOT_MODERATED',
      'INCORRECT_FROM',
      'INVALID_FROM',
      'MESSAGE_TOO_LONG',
      'NO_MESSAGE',
      'MAX_MESSAGES_COUNT',
      'NOT_ENOUGH_MONEY',
      'UNKNOWN_ERROR',
      'NO_DATA',
      'MESSAGE_NOT_EXIST',
      'MESSAGE_NOT_DELIVERED',
      'MESSAGE_IS_DELIVERED'
    );
    //$smses = Sms::whereNotIn('status', $statuses)->orWhereNull('status')->get();
    $smses = Sms::whereDate('created_at', Carbon::today())->where(function($q) {
        $q->where('status', '!=', 'MESSAGE_IS_DELIVERED')
            ->orWhereNull('status');
      })->get();
        //->where('status', '!=', 'MESSAGE_IS_DELIVERED')->get();
    foreach ($smses as $sms) {
      $old_status = $sms->status;
      $sms->checkSmsStatus();
      if ($old_status == $sms->status) continue;
      if (in_array($sms->type , ['requisites', 'payed', 'ttn'])) {
        $order = Order::where('prom_id', $sms->order_id)->first();
        if (!$order) continue;
        $order_status = $order->statuses;
        $status = 0; // 0 - no status ; 1 - send; 2 - delivered; 3 - error
        if (in_array($sms->status, $statuses) && ($sms->status != 'MESSAGE_IS_DELIVERED')) {
          $status = 3;
          $sms->error_at = Carbon::now();
        }
        if ($sms->status == 'MESSAGE_IS_SENT') {
          $status = 1;
          $sms->send_at = Carbon::now();
        }
        if ($sms->status == 'MESSAGE_IS_DELIVERED') {
          $status = 2;
          $sms->delivered_at = Carbon::now();
        }
        $order_status->{$sms->type.'_phone'} = $status;
        $order_status->save();
        $sms->save();
      }
    }
    $this->checkSputnikActivity();
  }

  public function messages () {
    $cron = Cron::find(2);
    $cron->last_job = Carbon::now();
    $cron->save();
    $api = new PromApi;
    $messages = $api->getMessages();//Message::min('prom_id'));

    foreach ($messages as $m) {
      $date = new \DateTime($m['date_created']);
      $date->setTimezone(new \DateTimeZone('Europe/Kiev'));
      $date_str = $date->format('Y-m-d H:i');


      $message = Message::firstOrNew(array(
        'product_id' => (string) $m['product_id'],
        'client_full_name' => $m['client_full_name'],
        'phone' => $m['phone'],
        'prom_date_created' => $date,
        'message' => $m['message'],
        'prom_id' => $m['id'],
        'subject' => $m['subject'],
      ));


      $message->prom_status = $m['status'];

      $autoreply = Autoreply::where('active', 1)->where('from', '<=', $date_str)->where('to', '>=', $date_str)->first();
      if ($autoreply != null && $message->crm_status != 'autoreply' && $m['status'] == 'unread') {
        $api->sendMessage($m['id'], $autoreply->message);
        $api->setMessageStatus($m['id'], 'read');
        $message->crm_status = 'autoreply';
        $message->prom_status = 'read';
      }

      $message->save();
    }
    print_r(count($messages));
  }

  public function date ($str, $format = false, $import = true) {
    if ($format) {
      return \DateTime::createFromFormat('d.m.y H:i', $str)->format('Y-m-d H:i');
    }
    $f = ($import) ?  'd.m.y H:i' : 'Y-m-d H:i';
    return \DateTime::createFromFormat($f, $str);
  }

  public function checkSputnikActivity ()
  {
    $activities = SputnikEmail::getActivity();
    $map_type = array(
        1427460 => 'requisites',
        1430244 => 'ttn',
        1469445 => 'ttn',
        1795607 => 'feedback'
    );

    foreach ($activities as $activity) {
        $email = $activity['email'];
        $api_id = $activity['iid'];
        if (isset($map_type[$activity['messageId']])) {
            $type = $map_type[$activity['messageId']];
        } else {
            continue;
        }

        $status = $activity['activityStatus'];

        $date_time = new Carbon($activity['activityDateTime']);
        $message_emails = MessageEmail::where('email', $email)->where('type', $type)->get();
        $message_email = $message_emails->first();
        if (!$message_email) continue;
        if (count($message_emails) > 1) {
            $diff = $date_time->diffInMinutes(new Carbon($message_emails[0]->created_at));
            foreach ($message_emails as $m) {
                if ($date_time->diffInMinutes($m->created_at) < $diff) {
                    $message_email = $m;
                }
            }
        }
        if ($message_email->status == $status) continue;
        $message_email->api_id = $api_id;
        $message_email->status = $status;
        $order = Order::where('prom_id', $message_email->order_id)->first();
        if (!$order) continue;
        $order_status = $order->statuses;
        switch ($status) {
          case 'UNDELIVERED':
              $order_status->{$type.'_email'} = 3;
              break;
          case 'DELIVERED':
              $order_status->{$type.'_email'} = 2;
              $message_email->delivered_at = Carbon::now();
              break;
          case 'READ':
              $order_status->{$type.'_email'} = 4;
              $message_email->read_at = Carbon::now();
              break;

        }
        $message_email->save();
        $order_status->save();
    }

  }

  public function newPost () {
      $cron = Cron::find(6);
      $cron->last_job = Carbon::now();
      $cron->save();

      $np = new NewPostApi;
      $privatPost = '95dc212d-479c-4ffb-a8ab-8c1b9073d0bc';
      $warehouses = $np->getWarehouses()['data'];
      $warehouses_refs = array_column($warehouses, 'Ref');
      foreach ($warehouses as $warehouse) {
          if ($warehouse['TypeOfWarehouse'] == $privatPost ) continue;
          NewPostWarehouse::updateOrCreate(array('ref'=>$warehouse['Ref']), array(
              'description' => trim($warehouse['DescriptionRu']),
              'description_ua' => trim($warehouse['Description']),
              'site_id' => $warehouse['SiteKey'],
              'city_ref' => $warehouse['CityRef'],
              'city_description' => trim($warehouse['CityDescriptionRu']),
          ));
      }
      NewPostWarehouse::whereNotIn('ref', $warehouses_refs)->delete();
      $cities = $np->getCities()['data'];
      foreach ($cities as $city) {
          NewPostCity::updateOrCreate(array('ref'=>$city['Ref']), array(
              'description' => trim($city['DescriptionRu']),
              'description_ua' => trim($city['Description']),
              'city_id' => $city['CityID'],
          ));
      }
  }

  public function testapi () {
//      $this->checkSputnikActivity();
/*      $sputnik_email = SputnikEmail::firstOrCreate(['email' => 'c67200eeb4@mailox.biz'],
          ['first_name' => 'Vasilii', 'last_name' => 'Ivanov']);
      $sputnik_email->subscribe();
 */
    $api = new PromApi;
    dd($api->getItem('60471742', 'orders'));
  }
}
