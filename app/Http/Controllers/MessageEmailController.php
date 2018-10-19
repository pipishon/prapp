<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SputnikEmail;
use App\MessageEmail;
use Carbon\Carbon;

class MessageEmailController extends Controller
{
    public function sendEmail (Request $request)
    {
        $email = $request->input('email');
        $order_id = $request->input('order_id');
        $type = $request->input('type');
        $sputnik_email = SputnikEmail::where('email', $email)->first();
        if (!$sputnik_email) {
            $sputnik_email = SputnikEmail::create(['email' => $email]);
            $sputnik_email->subscribe();
        }
        $message_email = MessageEmail::create(array(
            'email' => $email,
            'order_id' => $order_id,
            'type' => $type,
            'send_at' => Carbon::now()
        ));
        switch ($type) {
            case 'requisites':
                $params = array(
                    'id' => $order_id,
                    'price' => $request->input('price'),
                );
                $sputnik_email->sendEvent('api-send-requisites', $params);
                break;
            case 'ttn':
                $trigger = ($request->input('deliverer') == 'Укрпочта') ? 'api-send-ttn-ukrpost' : 'api-send-ttn-newpost';
                $params = array(
                    'ttn' => $request->input('ttn'),
                );
                $sputnik_email->sendEvent($trigger, $params);
                break;
        }
    }
}
