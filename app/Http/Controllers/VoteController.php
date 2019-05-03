<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vote;
use Illuminate\Support\Facades\DB;
use App\MobileDetect;

class VoteController extends Controller
{
    public function remoteClick (Request $request)
    {
        $order_id = $request->input('id');
        Vote::where('order_id', $order_id)->update(array(
            'is_prom_comment' => 1
        ));
    }

    public function index (Request $request)
    {
      return Vote::with(['order', 'message'])->orderBy('updated_at', 'desc')->get();
    }

    public function getEmails()
    {
      $pickup = DB::table('message_emails')
        ->select('message_emails.send_at', 'message_emails.delivered_at',
        'message_emails.read_at', 'order_statuses.delivered', 'order_statuses.custom_email',
        'orders.prom_date_created', 'orders.id as order_id',
        'orders.client_last_name',  'orders.client_first_name',
        'customers.id as customer_id', 'customers.manual_status',
        'customers.auto_status', 'customer_statistics.count_orders',
        'customer_statistics.total_price', 'orders.delivery_option',
        'orders.email', 'orders.prom_id', 'message_emails.send_by')
        ->join('orders', 'orders.prom_id', 'message_emails.order_id')
        ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
        ->join('customers', 'orders.customer_id', 'customers.id')
        ->join('customer_statistics', 'customer_statistics.customer_id', 'customers.id')
        ->where('message_emails.type', 'feedback')
        ->where('orders.delivery_option', 'Самовывоз')
        //->orderBy('message_emails.send_at', 'desc')
        ->get()->toArray();
      $np = DB::table('message_emails')
        ->select('message_emails.send_at', 'message_emails.delivered_at',
        'message_emails.read_at', 'new_post_ttn_tracks.date_received as np_received',
        'orders.prom_date_created', 'orders.id as order_id', 'order_statuses.custom_email',
        'orders.client_last_name',  'orders.client_first_name',
        'customers.id as customer_id', 'customers.manual_status',
        'customers.auto_status', 'customer_statistics.count_orders',
        'customer_statistics.total_price', 'orders.delivery_option',
        'orders.email', 'orders.prom_id', 'message_emails.send_by')
        ->join('orders', 'orders.prom_id', 'message_emails.order_id')
        ->join('new_post_ttn_tracks', 'orders.id', 'new_post_ttn_tracks.order_id')
        ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
        ->join('customers', 'orders.customer_id', 'customers.id')
        ->join('customer_statistics', 'customer_statistics.customer_id', 'customers.id')
        ->where('message_emails.type', 'feedback')
        ->where('orders.delivery_option', ['Новая Почта', 'НП без риска'])
        //->orderBy('message_emails.send_at', 'desc')
        ->get()->toArray();
      $result = array_merge($pickup, $np);
        usort($result, function($a, $b) {
          $ad = new \DateTime($a->send_at);
          $bd = new \DateTime($b->send_at);
          if ($ad == $bd) {
            return 0;
          }
          return $ad > $bd ? -1 : 1;
        });
      return $result;
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

        $detect = new MobileDetect;
        $device = 'desctop';
        if ( $detect->isMobile() ) {
            $device = 'mobile';
        }
        if( $detect->isTablet() ){
            $device = 'tablet';
        }

        Vote::create(array(
            'order_id' => $order_id,
            'vote' => $request->input('vote'),
            'ip' => $ip,
            'device' => $device
        ));

        return view('vote', $params );
    }

    public function processForm (Request $request)
    {
        $order_id = $this->encrypt($request->input('hash'));
        Vote::where('order_id', $order_id)->update(array(
            'comment' => $request->input('comment')
        ));
        return redirect()->route('vote.success')->with([
            'vote' => $request->input('vote'),
            'order_id' => $order_id
        ]);
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
