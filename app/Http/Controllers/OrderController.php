<?php

namespace App\Http\Controllers;

use App\PromApi;

use App\Order;
use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\NewPostCity;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $input = $request->all();

      $per_page = (isset($input['per_page'])) ? (int) $input['per_page'] : 20;

      $orders =  Order::where('status', '!=', 'test')->with('smsStatuses')->with('emailStatuses')->with('products')->with('ttn')
        ->with('statuses')->with('customer')->search($input)->orderBy('prom_date_created', 'desc')->paginate($per_page);
      //var_dump($orders);
      foreach ($orders as $order) {
        if ($order->statuses->payment_price == 0) {
          $order->statuses->payment_price = (float) str_replace(',', '.', preg_replace('/\s+/u', '', $order->price));
        }
        $order->customer->load('statistic');
        if (is_object($order->ttn)) {
            $order->is_address_valid = NewPostCity::isAddressValid($order->ttn->full_address);
        } else {
            $order->is_address_valid = NewPostCity::isAddressValid($order->delivery_address);
        }
      }

      $delivery_collected = array();
      foreach (['Новая Почта', 'Доставка Укрпочтой (25-55 грн, оплачивается вместе с заказом)',  'Доставка "Ин тайм" (отправки – по средам)', '«Нова пошта» - Покупка без риска'] as $name) {
        $total =  DB::table('orders')->join('order_statuses', 'orders.id', '=', 'order_statuses.order_id')
            ->where('orders.delivery_option', $name)->where('orders.status', '!=', 'canceled')
            ->whereDate('order_statuses.shipment_date', '=', Db::Raw('CURDATE()'))->count();
        $collected =  DB::table('orders')->join('order_statuses', 'orders.id', '=', 'order_statuses.order_id')
          ->select(DB::raw('order_statuses.shipment_date'))->where('order_statuses.collected_string', '=', 'Собран')
          ->where('orders.delivery_option', $name)->where('orders.status', '!=', 'canceled')
          ->whereDate('order_statuses.shipment_date', '=', Db::Raw('CURDATE()'))->count();
        $delivery_collected[$name] = array('collected' => $collected, 'total' => $total);
      }
      foreach ($delivery_collected['Новая Почта'] as $name => $val) {
        $delivery_collected['Новая Почта'][$name] = $val + $delivery_collected['«Нова пошта» - Покупка без риска'][$name];
      }


      $name = 'Пункты самовывоза';
      $total =  DB::table('orders')->join('order_statuses', 'orders.id', '=', 'order_statuses.order_id')
          ->where('orders.delivery_option', $name)->whereNotIn('orders.status', ['delivered', 'canceled'])
          ->whereDate('order_statuses.shipment_date', '<=', Db::Raw('CURDATE()'))->count();
      $collected =  DB::table('orders')->join('order_statuses', 'orders.id', '=', 'order_statuses.order_id')
          ->where('order_statuses.collected_string', '=', 'Собран')->where('orders.delivery_option', $name)
          ->whereNotIn('orders.status', ['delivered', 'canceled'])->whereDate('order_statuses.shipment_date', '<=', Db::Raw('CURDATE()'))->count();
      $delivery_collected[$name] = array('collected' => $collected, 'total' => $total);

      $all_collected = array('total' => 0, 'collected' => 0);
      foreach ($delivery_collected as $item) {
        $all_collected['total'] += $item['total'];
        $all_collected['collected'] += $item['collected'];
      }
      /*$all_collected['total'] =  DB::table('orders')->join('order_statuses', 'orders.id', '=', 'order_statuses.order_id')->where('orders.status', '!=', 'canceled')
          ->whereDate('order_statuses.shipment_date', '=', Db::Raw('CURDATE()'))->count();
      $all_collected['collected'] =  DB::table('orders')->join('order_statuses', 'orders.id', '=', 'order_statuses.order_id')
        ->select(DB::raw('order_statuses.shipment_date'))->where('order_statuses.collected_string', '=', 'Собран')->where('orders.status', '!=', 'canceled')
        ->whereDate('order_statuses.shipment_date', '=', Db::Raw('CURDATE()'))->count();
       */
      $stats = array('pending' => '0', 'received' => '0', 'delivered' => '0', 'canceled' => '0');
      $s = DB::table('orders')->select('status', DB::raw('count(*) as total'))->groupBy('status')->get();
      foreach($s as $stat) {
        $stats[$stat->status] = $stat->total;
      }
      $stats['all'] = Order::count();


      $custom = collect([
        'delivery_collected' => $delivery_collected,
        'stats' => $stats,
        'all_collected' => $all_collected
      ]);
      $orders = $custom->merge($orders);

      return $orders;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $order->load('smsStatuses')->load('emailStatuses')->load('products')->load('ttn')
        ->load('statuses')->load('customer')->customer->load('statistic');

        if ($order->statuses->payment_price == 0) {
          $order->statuses->payment_price = (float) str_replace(',', '.', preg_replace('/\s+/u', '', $order->price));
        }
        if (is_object($order->ttn)) {
            $order->is_address_valid = NewPostCity::isAddressValid($order->ttn->full_address);
        } else {
            $order->is_address_valid = NewPostCity::isAddressValid($order->delivery_address);
        }
        return $order;
;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
      //$order->comment = (string) $request->input('comment');
      $result = $order->update($request->only('comment', 'delivery_option', 'payment_option', 'delivery_address'));
      return ($result) ? 'success' : 'faild';
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function changeStatus(Request $request)
    {
      $api = new PromApi;
      $inputs = $request->only('id', 'status', 'reason', 'reason_message');
      $order = Order::find($inputs['id']);
      $order->status = $inputs['status'];
      $order->save();

      $customer = Customer::find($order->customer_id);
      $customer->recalcStatistics();
      return $api->setOrderStatus($order->prom_id, $order->status, $inputs['reason'], $inputs['reason_message']);
    }
}
