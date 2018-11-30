<?php

namespace App\Http\Controllers;

use App\Order;
use App\PromApi;
use App\Sms;
use App\Settings;
use App\MessageTemplate;
use App\NewPostTtnTrack;
use App\Dictionary;
use App\SputnikEmail;
use App\MessageEmail;
use Carbon\Carbon;
use App\NewPostCity;
use App\NewPostApi;
use App\NewPostTtn;
use Illuminate\Http\Request;

class MassActionController extends Controller
{
  public function statusDelivered (Request $request)
  {
    $ids = $request->input('ids');
    $api = new PromApi;
    $orders = Order::whereIn('id', $ids)->get();
    $responce = array();
    foreach ($orders as $order) {
      $order->status = 'delivered';
      $order->statuses->delivered = Carbon::now();
      $order->push();
      $responce[] = $api->setOrderStatus($order->prom_id, 'delivered');
    }
    return $responce;
    //return '';
  }

  public function sendTtn (Request $request)
  {
    $ids = $request->input('ids');
    $orders = Order::whereIn('id', $ids)->get();
    $result = array('phone' => array(), 'email' => array());
    foreach ($orders as $order) {
        if ($order->statuses->ttn_string != '' && $order->validet['statuses']['Клиент'] == '1') {
            $sms = Sms::firstOrCreate(array('order_id' => $order->prom_id, 'type' => 'ttn'));
            $sms->status = null;
            $template_id = Settings::where('name', 'template_ttn_sms')->first()->value;
            $template = MessageTemplate::find($template_id)->template;
            $message = str_replace('$ttn$', $order->statuses->ttn_string, $template);
            $phone = ($order->statuses->custom_phone) ?  $order->statuses->custom_phone : $order->phone;
            $sms->message = $message;
            $sms->phone = $phone;
            $result['phone'][] = $order->id;
            $order->statuses->ttn_status = '1';
            $order->statuses->ttn_phone = '1';
            $sms->sendSms();
            $email = ($order->statuses->custom_email) ?  $order->statuses->custom_email : $order->email;
            if ($email) {
                $order_id = $order->prom_id;
                $type = 'ttn';
                $sputnik_email = SputnikEmail::where('email', $email)->first();
                if (!$sputnik_email) {
                    $sputnik_email = SputnikEmail::create(['email' => $email]);
                    $sputnik_email->subscribe();
                }
                $message_email = MessageEmail::create(array(
                    'email' => $email,
                    'order_id' => $order_id,
                    'type' => 'ttn',
                    'send_at' => Carbon::now()
                ));
                //$ukr_post_str = Dictionary::where('to', 'Укрпочта')->first()->from;
                $trigger = ($order->delivery_option == 'Укрпочта') ? 'api-send-ttn-ukrpost' : 'api-send-ttn-newpost';
                $params = array(
                    'ttn' => $order->statuses->ttn_string,
                );
                $result['email'][] = $order->id;
                $sputnik_email->sendEvent($trigger, $params);
                $order->statuses->ttn_email = '1';
            }
            $order->push();
        }
    }
    return $result;
  }
  public function createTtn (Request $request)
  {
    $ids = $request->input('ids');
    $orders = Order::whereIn('id', $ids)->get();
    $result = array();
    foreach ($orders as $order) {
        if ($order->statuses->ttn_string == '' &&
            $order->validet['success'] == 'all' &&
            (int) $order->statuses->shipment_place == 1
        ) {
            $phone = ($order->statuses->custom_phone) ?  $order->statuses->custom_phone : $order->phone;
            $price = ($order->statuses->payment_price) ?  $order->statuses->payment_price : $order->price;
            $price = ceil(floatval($price));
            $data = array(
                'order_id' => $order->id,
                'payer' => 'Recipient',
                'volume_general' => '0.1',
                'phone' => $phone,
                'price' => $price,
                'backpayer' => 'Recipient',
                'backprice' => $price,
                'date' => Carbon::now()->format('Y-m-d'),
            );
            $data['backdelivery'] = ($order->statuses->payment_status == 'Наложенный') ? '1': '0';
            $data['weight'] = floatval(str_replace(',', '.', $order->statuses->shipment_weight));

            $new_post_ttn = NewPostTtn::where('order_id', $order->id)->first();
            if ($new_post_ttn == null) {
                $new_post_address = NewPostCity::isAddressValid($order->delivery_address);
                $new_post_city = NewPostCity::where('description', '=', $new_post_address['city'])->first();
                $new_post_warehouse = $new_post_city->warehouses()->where('description', $new_post_address['warehouse'])->first();
                $data['city'] = $new_post_address['city'];
                $data['full_address'] = $order->delivery_address;
                $data['warehouse'] = $new_post_warehouse->ref;
                $data['name'] = $order->client_last_name.' '.$order->client_first_name;
                $new_post_ttn = NewPostTtn::firstOrNew($data);
            } else {
                $data['city'] = $new_post_ttn->city;
                $data['full_address'] = $new_post_ttn->full_address;
                $data['warehouse'] = $new_post_ttn->warehouse;
                $data['name'] = $new_post_ttn->name;
                $data['phone'] = $new_post_ttn->phone;
            }
            $new_post_ttn->update($data);
            $data['date'] = Carbon::now()->format('d.m.Y');
            $data['places'] = 1;
            $np = new NewPostApi();
            $responce = $np->getTtn($data);
            if ((bool) $responce['success'] == true) {
                $np_data = $responce['data'][0];
                $new_post_ttn->int_doc_number = $np_data['IntDocNumber'];
                $new_post_ttn->estimated_delivery_date = Carbon::parse($np_data['EstimatedDeliveryDate']);
                $new_post_ttn->cost_on_site = $np_data['CostOnSite'];
                $new_post_ttn->ref = $np_data['Ref'];
                $new_post_ttn->save();
                $order->statuses->ttn_string = $np_data['IntDocNumber'];
                $order->push();
                NewPostTtnTrack::updateOrCreate(array('order_id' => $order->id),
                    array(
                        'customer_id' => $order->customer->id,
                        'prom_id' => $order->prom_id,
                        'ref' => $np_data['Ref'],
                        'int_doc_number' => $np_data['IntDocNumber'],
                        'estimate_delivery_date' => $new_post_ttn->estimated_delivery_date,
                        'status' => '',
                        'status_code' => 0,
                        'redelivery' => $new_post_ttn->backdelivery,
                        'redelivery_sum' => $new_post_ttn->backprice,
                        'phone' => $new_post_ttn->phone,
                        'full_name' => $new_post_ttn->name,
                        'city' => $new_post_ttn->city,
                        'warehouse' => '',
                        'warehouse_ref' => $new_post_ttn->warehouse,
                        'recipient_address' => $new_post_ttn->full_address,
                        'date_created' => $new_post_ttn->date,
                        'date_first_day_storage' => null,
                        'document_weight' => $new_post_ttn->weight,
                        'check_weight' => 0,
                        'document_cost' => $new_post_ttn->cost_on_site,
                    )
                );
                $result[$order->id] = $new_post_ttn;
            }
        }
    }
    return $result;
  }
}


