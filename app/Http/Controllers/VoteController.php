<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vote;
use App\Order;
use Illuminate\Support\Facades\DB;
use App\MobileDetect;
use App\MessageEmail;

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
      return Vote::with(['order', 'message', 'order.customer'])->orderBy('updated_at', 'desc')->get();
    }

    public function removeVote(Request $request)
    {
        $id = $request->input('id');
        Vote::where('order_id', $id)->delete();
    }

    public function removeEmail(Request $request)
    {
        $id = $request->input('id');
        MessageEmail::where('type', 'feedback')->where('order_id', $id)->delete();
    }

    public function getEmails(Request $request)
    {
      $search = $request->input('search_query');
      $pickup = DB::table('message_emails')
        ->select('message_emails.send_at', 'message_emails.delivered_at',
        'message_emails.read_at', 'message_emails.email', 'order_statuses.delivered',
        'message_emails.status', 'orders.prom_date_created', 'orders.id as order_id',
        'orders.client_last_name',  'orders.client_first_name',
        'customers.id as customer_id', 'customers.manual_status',
        'customers.auto_status', 'customer_statistics.count_orders',
        'customer_statistics.total_price', 'orders.delivery_option',
        'orders.prom_id', 'message_emails.send_by')
        ->join('orders', 'orders.prom_id', 'message_emails.order_id')
        ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
        ->join('customers', 'orders.customer_id', 'customers.id')
        ->join('customer_statistics', 'customer_statistics.customer_id', 'customers.id')
        ->where('message_emails.type', 'feedback')
        ->where('orders.delivery_option', 'Самовывоз')
        ->where(function ($query) use ($search) {
            $query->where('orders.client_last_name', 'LIKE', $search)
                ->orWhere('orders.prom_id', $search);
        })
        ->orderBy('message_emails.send_at', 'desc')
        ->take(50)
        ->get()->toArray();

      $ukr = DB::table('message_emails')
        ->select('message_emails.send_at', 'message_emails.delivered_at',
        'message_emails.status', 'message_emails.read_at', 'message_emails.email',
        'ukr_post_ttn_tracks.date_received as np_received',
        'orders.prom_date_created', 'orders.id as order_id',
        'orders.client_last_name',  'orders.client_first_name',
        'customers.id as customer_id', 'customers.manual_status',
        'customers.auto_status', 'customer_statistics.count_orders',
        'customer_statistics.total_price', 'orders.delivery_option',
        'orders.prom_id', 'message_emails.send_by')
        ->join('orders', 'orders.prom_id', 'message_emails.order_id')
        ->leftJoin('ukr_post_ttn_tracks', 'orders.prom_id', 'ukr_post_ttn_tracks.prom_id')
        ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
        ->join('customers', 'orders.customer_id', 'customers.id')
        ->join('customer_statistics', 'customer_statistics.customer_id', 'customers.id')
        ->where('message_emails.type', 'feedback')
        ->where('orders.delivery_option', 'Укрпочта')
        ->where(function ($query) use ($search) {
            $query->where('orders.client_last_name', 'LIKE', $search)
                ->orWhere('orders.prom_id', $search);
        })
        ->orderBy('message_emails.send_at', 'desc')
        ->take(50)
        ->get()->toArray();
      $np = DB::table('message_emails')
        ->select('message_emails.send_at', 'message_emails.delivered_at',
        'message_emails.status', 'message_emails.read_at', 'message_emails.email',
        'new_post_ttn_tracks.date_received as np_received',
        'orders.prom_date_created', 'orders.id as order_id',
        'orders.client_last_name',  'orders.client_first_name',
        'customers.id as customer_id', 'customers.manual_status',
        'customers.auto_status', 'customer_statistics.count_orders',
        'customer_statistics.total_price', 'orders.delivery_option',
        'orders.prom_id', 'message_emails.send_by')
        ->join('orders', 'orders.prom_id', 'message_emails.order_id')
        ->leftJoin('new_post_ttn_tracks', 'orders.id', 'new_post_ttn_tracks.order_id')
        ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
        ->join('customers', 'orders.customer_id', 'customers.id')
        ->join('customer_statistics', 'customer_statistics.customer_id', 'customers.id')
        ->where('message_emails.type', 'feedback')
        ->whereIn('orders.delivery_option', ['Новая Почта', 'НП без риска'])
        ->where(function ($query) use ($search) {
            $query->where('orders.client_last_name', 'LIKE', $search)
                ->orWhere('orders.prom_id', $search);
        })
        ->orderBy('message_emails.send_at', 'desc')
        ->take(50)
        ->get()->toArray();
      $result = array_merge($pickup, $np, $ukr);
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
        if (!$order_id || Vote::where('order_id', $order_id)->first() != null) {
            return view('vote', array('repeat' => true));
        }
        $ip = $_SERVER['REMOTE_ADDR'];
				$vote = $request->input('vote');

        $detect = new MobileDetect;
        $device = 'desctop';
        if ( $detect->isMobile() ) {
            $device = 'mobile';
        }
        if( $detect->isTablet() ){
            $device = 'tablet';
        }

				$is_prom_comment = 0;

				if ($vote > 6) {
					$customer = Order::where('prom_id', $order_id)->first()->customer()->first();
					$url = 'http://kiev.prom.ua/opinions/list/2054335';
					if ($customer->is_google_comment == 0) {
						$customer->is_google_comment = 1;
						$customer->save();
						$url = 'https://search.google.com/local/writereview?placeid=ChIJy5LAnKbF1EARJ_CkshyRihY';
					} else {
						$is_prom_comment = 1;
					}
				}

        Vote::create(array(
            'order_id' => $order_id,
            'vote' => $vote,
            'ip' => $ip,
						'device' => $device,
						'is_prom_comment' => $is_prom_comment
        ));
				if ($vote > 6) {
					Redirect::to($url);
				} else {
					return view('vote', $params );
				}
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
        $hash = strtr($hash, '-_,', '+/=');
        return openssl_decrypt($hash, 'AES-128-CBC', 'sercet', 0, $iv);
    }

		public function updateField ($id, Request $request)
	 	{
			$update = array();
			$update[$request->input('name')] = $request->input('value');
			$customer = Vote::where('id', $id)->update($update);
		}

}
