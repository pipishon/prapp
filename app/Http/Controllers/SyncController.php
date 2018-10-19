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

  public function orders($orders = null)
  {
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


      $O_order = Order::firstOrCreate(array('prom_id' => $order['id']),
        array(
          'prom_id' => $order['id'],
          'status' => $order['status'],
          'customer_id' => $customer->id,
          'delivery_option' => (string) $order['delivery_option']['name'],
          'delivery_address' =>(string) $order['delivery_address'],
          'payment_option' => (string) $order['payment_option']['name'],
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



      foreach ($order['products'] as $product) {
        $product_price = floatval(preg_replace('/\s+/u', '', $product['price']));
        $O_product = Product::updateOrCreate(array('sku' => $product['sku']),
            array(
              'sku' => $product['sku'],
              'name' => $product['name'],
              'price' => $product_price,
              'main_image' => (string) $product['image'],
              'prom_id' => (string) $product['id'],
            ));

          $order_product = OrderProduct::firstOrCreate(array(
            'product_id' => $O_product->id,
            'order_id' => $O_order->id,
            'quantity' => $product['quantity'],
          ));
      }
      if (isset($order['email'])) {
        $sputnik_email = SputnikEmail::firstOrCreate(['email' => $order['email']],
            ['first_name' => $order['client_first_name'], 'last_name' => $order['client_last_name']]);
        $sputnik_email->subscribe();
      }

      //$customer = Customer::find($O_order->customer_id);

    }

    foreach ($new_orders_id as $id) {
        $order = Order::where('prom_id', $id)->first();
        OrderStatus::firstOrCreate(array('order_id' => $order->id));
        $customer_id = $order->customer_id;
        $customer = Customer::find($customer_id);
        $customer->recalcStatistics();
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

  public function smsStatus () {
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
    $smses = Sms::whereNotIn('status', $statuses)->orWhereNull('status')->get();
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
        1469445 => 'ttn'
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
      $np = new NewPostApi;
      $privatPost = '95dc212d-479c-4ffb-a8ab-8c1b9073d0bc';
      $warehouses = $np->getWarehouses()['data'];
      foreach ($warehouses as $warehouse) {
          if ($warehouse['TypeOfWarehouse'] == $privatPost ) continue;
          NewPostWarehouse::updateOrCreate(array('ref'=>$warehouse['Ref']), array(
              'description' => $warehouse['DescriptionRu'],
              'site_id' => $warehouse['SiteKey'],
              'city_ref' => $warehouse['CityRef'],
              'city_description' => $warehouse['CityDescriptionRu'],
          ));
      }
      $cities = $np->getCities()['data'];
      foreach ($cities as $city) {
          NewPostCity::updateOrCreate(array('ref'=>$city['Ref']), array(
              'description' => $city['DescriptionRu'],
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
