<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sms;
use Carbon\Carbon;
use App\Order;

class SmsApiController extends Controller
{
  public function sendSms(Request $request)
  {
    $sms = Sms::firstOrCreate(array('order_id' => $request->input('order_id'), 'type' => $request->input('type')));
    $sms->status = null;
    $sms->message = $request->input('message');
    $sms->phone = $request->input('phone');
    $sms->sendSms();
  }

}
