<?php

namespace App\Http\Controllers;

use App\PromApi;

use App\Order;
use App\OrderProduct;
use App\Product;
use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\NewPostCity;
use App\OrderStatus;
use App\PrivatPayment;


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
          $order->statuses->save();
        }
        $order->customer->load('statistic');
        if (is_object($order->ttn)) {
            $order->is_address_valid = NewPostCity::isAddressValid($order->ttn->full_address);
        } else {
            $order->is_address_valid = NewPostCity::isAddressValid($order->delivery_address);
        }
      }

      $delivery_collected = array();
      foreach (['Новая Почта', 'Укрпочта',  'НП без риска'] as $name) {
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
        $delivery_collected['Новая Почта'][$name] = $val + $delivery_collected['НП без риска'][$name];
      }
      unset($delivery_collected['НП без риска']);


      $name = 'Самовывоз';
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

        $order->products->each(function($instance) {
            $instance->append('same_payed');
            $instance->append('same_not_payed');
        });

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

    public function updateFromProm ($prom_id, Request $request)
    {
        $order = Order::where('prom_id', $prom_id)->first();
        if ($request->has('with_discounts')) {
            $order->updateFromApi(true);
        } else {
            $order->updateFromApi();
        }
        return $order;
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

    public function refresh($id)
    {
      $order = Order::find($id);
      //if ($order == null) return;
      $order->statuses->payment_price = $order->price_discount;
      $order->push();
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

    public function ImportFromApi (Request $request)
    {

        $last_id = $request->input('last_id');
        $api = new PromApi;
        $prom_params = array('limit' => 50);
        if ($last_id) {
            $prom_params['last_id'] = $last_id;
        }
        $prom_orders = $api->getList('orders', $prom_params)['orders'];
        foreach ($prom_orders as $prom_order) {
            $prom_products = $prom_order['products'];
            $order = Order::where('prom_id', $prom_order['id'])->first();
            if ($order == null) continue;
            $ids = array();
            //$order->products()->delete();
            foreach ($prom_products as $prom_product) {
                $product = Product::where('prom_id', $prom_product['id'])->first();
                if ($product == null) {
                    $product = Product::firstOrCreate(array(
                        'name' => $prom_product['name'],
                    ));
                }
                $ids[] = $product->id;
                $order_product = OrderProduct::updateOrCreate(array(
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                ),array(
                    'quantity' => str_replace(',','.', $prom_product['quantity']),
                    'prom_price' => floatval(str_replace(',', '.', $prom_product['price'])),
                ));
            }
            OrderProduct::where('order_id', $order->id)->whereNotIn('product_id', $ids)->delete();
            $price = preg_replace('/\s+/u', '', $prom_order['price']);
            $price = str_replace(',','.', $price);
            $order->price = floatval($price);
            $order->status = $prom_order['status'];
            $order->save();
        }

        $total = Order::count();
        if (count($prom_orders) > 0) {
            $ids = array_column($prom_orders, 'id');
            $last_id = array_values(array_slice($ids, -1))[0];
            return array('last_id' => $last_id, 'imported' => count($prom_orders), 'total' => $total);
        } else {
            return array('imported' => count($prom_orders), 'total' => $total);
        }
        /*$orders = Order::paginate(10);
        $req_count = 0;
        foreach ($orders as $order) {
            $req_count++;
            $order->updateFromApi();
        }
        sleep(10);*/
        //return $orders;
    }

    public function getGroups (Request $request)
    {
        $orders = $request->input('orders');
        $result = DB::table('orders')
          ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
          ->join('order_products', 'orders.id', 'order_products.order_id')
          ->join('products', 'order_products.product_id', 'products.id')
          ->select('products.category')
          ->groupBy('products.category')
          ->where('orders.status', 'received')
          ->where('order_statuses.collected', '0');
        if ($orders != 'all') {
          $result = $result->where(function ($query) {
              $query->where('order_statuses.payment_status', 'Оплачен')->orWhere('order_statuses.payment_status', 'Наложенный')->orWhere(function ($query) {
                $query->where('orders.delivery_option', 'Самовывоз')->whereNotNull('order_statuses.shipment_date');
              });
          });
        }
        return $result->get();
    }

    public function getProductByGroup (Request $request)
    {
        $group = $request->input('group');
        $orders = $request->input('orders');
        $result = DB::table('orders')
          ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
          ->join('order_products', 'orders.id', 'order_products.order_id')
          ->join('products', 'order_products.product_id', 'products.id')
          ->select('products.id', 'products.name', 'products.sku', 'products.main_image', 'products.price', DB::Raw('SUM(order_products.quantity) as sum'), DB::Raw('COUNT(order_products.quantity) as qty'))
          ->orderBy('products.sort1')
          ->orderBy('products.name')
          ->groupBy('order_products.product_id')
          ->where('products.category', $group)
          ->where('order_statuses.collected', '0')
          ->where('orders.status', 'received');
        if ($orders != 'all') {
          $result = $result->where(function ($query) {
              $query->where('order_statuses.payment_status', 'Оплачен')->orWhere('order_statuses.payment_status', 'Наложенный')->orWhere(function ($query) {
                $query->where('orders.delivery_option', 'Самовывоз')->whereNotNull('order_statuses.shipment_date');
              });
          });
        }
        $products = $result->get();
        foreach ($products as $product) {
            $orders_with_product = DB::table('orders')
              ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
              ->join('order_products', 'orders.id', 'order_products.order_id')
              ->select('orders.prom_id', 'orders.client_first_name',  'orders.client_last_name', 'orders.delivery_option', 'order_statuses.shipment_date', 'order_products.quantity')
              ->where('order_statuses.collected', '0')
              ->where('orders.status', 'received')
              ->where('order_products.product_id', $product->id);
            if ($orders != 'all') {
              $orders_with_product = $orders_with_product->where(function ($query) {
                  $query->where('order_statuses.payment_status', 'Оплачен')->orWhere('order_statuses.payment_status', 'Наложенный')->orWhere(function ($query) {
                    $query->where('orders.delivery_option', 'Самовывоз')->whereNotNull('order_statuses.shipment_date');
                  });
              });
            }
            $product->orders = $orders_with_product->get();
        }
        return $products;
    }

    public function sendFeedBack ($prom_id) {
        $order = Order::where('prom_id', $prom_id)->first();
        return $order->sendfeedback();
    }
}
