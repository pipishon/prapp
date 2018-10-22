<?php

namespace App\Http\Controllers;

use App\Order;
use App\PromApi;
use App\Sms;
use App\Settings;
use App\MessageTemplate;
use App\Dictionary;
use App\SputnikEmail;
use Carbon\Carbon;
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
      $order->save();
      $responce[] = $api->setOrderStatus($order->prom_id, 'delivered');
    }
    return $responce;
    //return '';
  }

  public function sendTtn (Request $request)
  {
    $ids = $request->input('ids');
    $orders = Order::whereIn('id', $ids)->get();
    foreach ($orders as $order) {
        if ($order->statuses->ttn_string != '') {
            $sms = Sms::firstOrCreate(array('order_id' => $order->prom_id, 'type' => 'ttn'));
            $sms->status = null;
            $template_id = Settings::where('name', 'template_ttn_sms')->first()->value;
            $template = MessageTemplate::find($template_id)->template;
            $message = str_replace('$ttn$', $order->statuses->ttn_string, $template);
            $phone = ($order->statuses->custom_phone) ?  $order->statuses->custom_phone : $order->phone;
            $sms->message = $message;
            $sms->phone = $phone;
            //$sms->sendSms();
            $email = ($order->statuses->custom_email) ?  $order->statuses->custom_email : $order->email;
            if ($email) {
                $order_id = $order->prom_id;
                $type = 'ttn';
                /*$sputnik_email = SputnikEmail::where('email', $email)->first();
                if (!$sputnik_email) {
                    $sputnik_email = SputnikEmail::create(['email' => $email]);
                    $sputnik_email->subscribe();
                }*/
                $message_email = array(//= MessageEmail::create(array(
                    'email' => $email,
                    'order_id' => $order_id,
                    'type' => $type,
                    'send_at' => Carbon::now()
                );//);
                $ukr_post_str = Dictionary::where('to', 'Укрпочта')->first()->from;
                $trigger = ($order->delivery_option == $ukr_post_str) ? 'api-send-ttn-ukrpost' : 'api-send-ttn-newpost';
                $params = array(
                    'ttn' => $order->statuses->ttn_string,
                );
                print_r(array($message_email, $trigger, $params));
                die();
            //    $sputnik_email->sendEvent($trigger, $params);
            }
        }
    }
  }
}
