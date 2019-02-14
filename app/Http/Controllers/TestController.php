<?php

namespace App\Http\Controllers;

use App\PromApi;
use App\Order;
use App\OrderProduct;
use App\Product;
use App\Customer;
use App\NewPostCity;
use App\NewPostApi;
use App\NewPostTtnTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NumberToWords\NumberToWords;
use Carbon\Carbon;

class TestController extends Controller
{

    public function instagram ()
    {
        $inst_ids = Customer::all()->pluck('instagram_id');
        $i = 0;
        foreach ($inst_ids as $inst_id) {
            if ($inst_id != null) {
                $i++;
                echo '<div>'.$inst_id.'</div>';
            }
        }
        echo '<div><strong>'.'Кол-во:'.$i.'</strong></div>';
      return ;
    }

    public function test1 (Request $request)
    {
        DB::table('customer_statistics')->select('*')->update([
            'count_orders' => DB::Raw('count_orders_delivered + count_orders_received')
        ]);
        return ;

    }

    public function test2 (Request $request)
    {
        $sku = $request->input('sku');
        $order_prom_id = $request->input('order_id');
        $product = Product::where('sku', $sku)->first();
        $order = Order::where('prom_id', $order_prom_id)->first();
        $order_product = OrderProduct::where('product_id', $product->id)
            ->where('order_id', $order->id)->first();
        echo 'Оплаченные: ';
        echo $order_product->getSamePayedAttribute(0);
        echo '<br />';
        echo '<table>';
        $same = DB::table('order_products')
              ->join('orders', 'orders.id', 'order_products.order_id')
              ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
              ->select('orders.client_first_name','orders.client_last_name', 'orders.prom_id', 'order_products.quantity')
              ->where('orders.status', 'received')
              ->where('order_products.product_id', $order_product->product_id)
              ->where('order_products.order_id', '<>', $order_product->order_id)
              ->where('order_statuses.payment_status', 'Оплачен')
              ->where('order_statuses.collected', '0')
              ->get();
        foreach ($same as $item) {
            echo '<tr>';
            echo '<td>'.$item->prom_id.'</td>';
            echo '<td>'.$item->client_first_name.' '.$item->client_last_name.'</td>';
            echo '<td>'.$item->quantity.'</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo 'Не оплаченные:';
        echo $order_product->getSameNotPayedAttribute(0);
        echo '<br />';
        $same = DB::table('order_products')
              ->join('orders', 'orders.id', 'order_products.order_id')
              ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
              ->select('orders.client_first_name','orders.client_last_name', 'orders.prom_id', 'order_products.quantity')
              ->where('orders.status', 'received')
              ->where('order_products.product_id', $order_product->product_id)
              ->where('order_products.order_id', '<>', $order_product->order_id)
              ->where('order_statuses.payment_status', 'Не оплачен')
              ->where('order_statuses.collected', '0')
              ->get();
        echo '<table>';
        foreach ($same as $item) {
            echo '<tr>';
            echo '<td>'.$item->prom_id.'</td>';
            echo '<td>'.$item->client_first_name.' '.$item->client_last_name.'</td>';
            echo '<td>'.$item->quantity.'</td>';
            echo '</tr>';
        }
        echo '</table>';

    }

    public function index (Request $request)
    {
/*        $api = new PromApi;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://prom.ua/captcha');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result;
        return var_dump($api->getList('orders'));
        /*$orders = Order::whereDate('prom_date_created', '>', Carbon::parse('01-07-2018'))->paginate(20);
        $api = new PromApi;
        foreach ($orders as $order) {
            $order->updateFromApi();
        }
    }*/
        $product_sku = $request->input('sku');
        $product_id = Product::where('sku', $product_sku)->first()->id;
        $product_ids = Product::all()->pluck('id');
        $months = DB::table('orders')
            ->join('order_products', 'orders.id', 'order_products.order_id')
            ->join('products', 'products.id', 'order_products.product_id')
                ->where('orders.status', 'delivered')
                ->where('order_products.product_id', $product_id)
                ->select('orders.prom_id', 'order_products.quantity', DB::raw('YEAR(orders.prom_date_created) year, MONTH(orders.prom_date_created) month'), DB::raw('CONCAT(YEAR(orders.prom_date_created), "_" , MONTH(orders.prom_date_created)) as date'))
                ->orderBy('year')
                ->orderBy('month')
                ->get()->toArray();
echo '<table style="text-align: center"><thead><th>id</th><th>date</th><th>quantity</th></thead>';
echo '<tbody>';
        foreach ($months as $row) {
            echo '<tr>';
            echo '<td style="padding: 2px 5px">' . $row->prom_id . '</td><td>'. $row->date. '</td><td>'. $row->quantity . '</td>';
            echo '</tr>';
        }
echo '</tbody></table>';
    }
    /*
        $ttn = $request->input('ttn');
        $id = $request->input('id');
        $np = new NewPostApi;
        $orders = Order::with('ttn')->whereHas('ttn')->doesntHave('ttntrack')->limit(20)->get();
        $ttns = $orders->pluck('ttn.int_doc_number');

        $tracks = $np->track($ttns)['data'];
        foreach ($tracks as $track) {
            $order = $orders->filter(function ($item) use ($track) {
                return $item->ttn->int_doc_number == $track['Number'];
            })->first();
            $status = $track['StatusCode'];
            if ($status == 2) continue; // if ttn deleted
            $np_track = NewPostTtnTrack::updateOrCreate(array('order_id' => $order->id),
                array(
                    'customer_id' => $order->customer->id,
                    'prom_id' => $order->prom_id,
                    'ref' => $track['RefEW'],
                    'int_doc_number' => $track['Number'],
                    'estimate_delivery_date' => isset($track['ScheduledDeliveryDate']) ? Carbon::parse($track['ScheduledDeliveryDate']) : $oreder->ttn->estimated_delivery_date,
                    'status' => $track['Status'],
                    'status_code' => (int) $track['StatusCode'],
                    'redelivery' => $track['Redelivery'],
                    'redelivery_sum' => $track['RedeliverySum'],
                    'phone' => isset($track['PhoneRecipient']) ? $track['PhoneRecipient'] : $oreder->ttn->phone,
                    'full_name' => isset($track['RecipientFullNameEW']) ? $track['RecipientFullNameEW'] : $oreder->ttn->name,
                    'city' => $track['CityRecipient'],
                    'warehouse' => $track['WarehouseRecipient'],
                    'warehouse_ref' => $track['WarehouseRecipientRef'],
                    'recipient_address' => $track['RecipientAddress'],
                    'date_created' => Carbon::parse($track['DateCreated']),
                    'date_first_day_storage' => isset($track['DateFirstDayStorage']) ? Carbon::parse($track['DateFirstDayStorage']) : null,
                    'document_weight' => $track['DocumentWeight'],
                    'check_weight' => $track['CheckWeight'],
                    'document_cost' => $track['DocumentCost'],
                )
            );
            switch ($status) {
                case 7:
                case 8:
                    if ($np_track->date_delivered == null) {
                        $np_track->date_delivered = Carbon::now();
                    }
                    break;
                case 9:
                case 10:
                case 11:
                    if ($np_track->date_received == null) {
                        $np_track->date_received = Carbon::now();
                    }
                    break;

            }
            $today = Carbon::now();
            if ($np_track->date_delivered == null) {
                $np_track->send_days = $today->diffInDays(Carbon::parse($np_track->date_created));
            }
            if ($np_track->date_received == null && $np_track->date_delivered != null) {
                $np_track->delivery_days = $today->diffInDays(Carbon::parse($np_track->date_delivered));
            }
            $np_track->save();
        }
     */
}
