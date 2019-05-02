<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vote;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function index (Request $request)
    {
      return Vote::with(['order', 'message'])->orderBy('updated_at', 'desc')->get();
    }

    public function getEmails()
    {
      $pickup = DB::table('message_emails')
        ->select('message_emails.send_at', 'message_emails.delivered_at',
        'message_emails.read_at', 'order_statuses.delivered',
        'orders.prom_date_created', 'orders.id as order_id',
        'orders.client_last_name',  'orders.client_first_name',
        'customers.id as customer_id', 'customers.manual_status',
        'customers.auto_status', 'customer_statistics.count_orders',
        'customer_statistics.total_price', 'orders.delivery_option',
        'orders.email', 'orders.prom_id')
        ->join('orders', 'orders.prom_id', 'message_emails.order_id')
        ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
        ->join('customers', 'orders.customer_id', 'customers.id')
        ->join('customer_statistics', 'customer_statistics.customer_id', 'customers.id')
        ->where('message_emails.type', 'feedback')
        ->where('orders.delivery_option', 'Самовывоз')
        ->orderBy('send_at')
        ->get()->toArray();
      $np = DB::table('message_emails')
        ->select('message_emails.send_at', 'message_emails.delivered_at',
        'message_emails.read_at', 'new_post_ttn_tracks.date_received as np_received',
        'orders.prom_date_created', 'orders.id as order_id',
        'orders.client_last_name',  'orders.client_first_name',
        'customers.id as customer_id', 'customers.manual_status',
        'customers.auto_status', 'customer_statistics.count_orders',
        'customer_statistics.total_price', 'orders.delivery_option',
        'orders.email', 'orders.prom_id')
        ->join('orders', 'orders.prom_id', 'message_emails.order_id')
        ->join('new_post_ttn_tracks', 'orders.id', 'new_post_ttn_tracks.order_id')
        ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
        ->join('customers', 'orders.customer_id', 'customers.id')
        ->join('customer_statistics', 'customer_statistics.customer_id', 'customers.id')
        ->where('message_emails.type', 'feedback')
        ->where('orders.delivery_option', ['Новая Почта', 'НП без риска'])
        ->orderBy('send_at')
        ->get()->toArray();
      return array_merge($pickup, $np);
      //return \App\MessageEmail::where('type', 'feedback')->orderBy('send_at')->get();
    }

    public function getForm (Request $request)
    {
        $params = $request->only(['hash', 'vote']); //id 100 hash FjAvQe3ev%2FBpEfSK2g%2BANQ%3D%3D
        $order_id = $this->encrypt($params['hash']);
        if (Vote::where('order_id', $order_id)->first() != null) {
            return view('vote', array('repeat' => true));
        }
        $ip = $_SERVER['REMOTE_ADDR'];

        Vote::create(array(
            'order_id' => $order_id,
            'vote' => $request->input('vote'),
            'ip' => $ip
        ));

        return view('vote', $params );
    }

    public function processForm (Request $request)
    {
        $order_id = $this->encrypt($request->input('hash'));
        Vote::where('order_id', $order_id)->update(array(
            'comment' => $request->input('comment')
        ));
        return redirect()->route('vote.success')->with(['vote' => $request->input('vote')]);
    }

    public function getSuccess (Request $request)
    {
        return view('vote_success');
    }

    public function encrypt ($hash)
    {
        $iv = 'p/Ȅ����';
        $hash = rawurldecode($hash);
        return openssl_decrypt($hash, 'AES-128-CBC', 'sercet', 0, $iv);
    }
}
