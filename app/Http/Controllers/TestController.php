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
use Carbon\Carbon;

class TestController extends Controller
{
    public function index (Request $request)
    {
        $group_num = $request->input('group');
        $last_id = $request->input('last_id');
        $api = new PromApi;
        $limit = '100';
      $groups = DB::table('products')->groupBy('group_id')->select('group_id')->get()->pluck('group_id')->toArray();
        $prom_products = $api->getList('products', array('group_id' => $groups[$group_num], 'last_id'=> $last_id, 'limit' => $limit))['products'];
        foreach ($prom_products as $prom_product) {
            Product::where('prom_id', $prom_product['id'])->update(array(
                'status' => $prom_product['status'],
                'presence' => $prom_product['presence'],
            ));
        }

        //    $api->getList('groups')['groups'];
        dd(array_column($prom_products, 'id'), count($groups));
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
