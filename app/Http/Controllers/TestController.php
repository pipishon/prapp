<?php

namespace App\Http\Controllers;

use App\PromApi;
use App\Order;
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
        echo Carbon::now()->subMonth()->endOfMonth()->toDateString();
        echo Carbon::now()->subMonth(6)->startOfMonth()->toDateString();
        return;
        $products = DB::table('products')
            ->join('product_supliers', 'products.id', 'product_supliers.product_id')
            ->join('order_products', 'order_products.product_id', 'products.id')
            ->join('orders', 'orders.id', 'order_products.order_id')
            ->where('product_supliers.suplier_id', '7')
            ->where('orders.status', '<>', 'canceled')
            ->whereDate('orders.prom_date_created', '>', '2018-11-22')
            ->whereDate('orders.prom_date_created', '<', '2018-11-24')
            ->groupBy('products.id')
            ->select('products.id', 'products.sku', DB::Raw('SUM(order_products.quantity) as qty'), DB::Raw('SUM((order_products.prom_price - products.purchase_price)*order_products.quantity) as earn'))->orderBy('earn', 'desc')->get();
        $sum = $products->sum('earn');
        $agr = 0;
        $products = $products->map(function ($item) use ($sum, &$agr) {
            $agr += $item->earn * 100 / $sum;
            $abc = 'D';
            if ($agr < 50) {
                $abc = 'A';
            }
            if ($agr > 50 && $agr < 80) {
                $abc = 'B';
            }
            if ($agr > 80 && $agr < 95) {
                $abc = 'C';
            }
            return ['sku' => $item->sku, 'abc' => $abc, 'earn' => $item->earn, 'percent' => $item->earn * 100 / $sum, 'agr' => $agr, 'qty' => $item->qty];
        });
        echo '<table>';
            echo '<tr>';
            echo '<th>sku</th>';
            echo '<th>abc</th>';
            echo '<th>earn</th>';
            echo '<th>percent</th>';
            echo '<th>agr</th>';
            echo '<th>qty</th>';
            echo '</tr>';

        foreach ($products as $product) {
            echo '<tr>';
            echo '<td>'.$product['sku'].'</td>';
            echo '<td>'.$product['abc'].'</td>';
            echo '<td>'.$product['earn'].'</td>';
            echo '<td>'.$product['percent'].'</td>';
            echo '<td>'.$product['agr'].'</td>';
            echo '<td>'.$product['qty'].'</td>';
            echo '</tr>';
        }
        echo '</table>';
        return; //$products;
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
