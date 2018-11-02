<?php

namespace App\Http\Controllers;

use App\PromApi;
use App\Order;
use App\Customer;
use App\NewPostCity;
use App\NewPostApi;
use App\NewPostTtnTrack;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TestController extends Controller
{
    public function index (Request $request)
    {
        $ttn = $request->input('ttn');
        $id = $request->input('id');
        $np = new NewPostApi;
        $order = Order::where('prom_id', $id)->with('statuses')->first();
        $track = $np->track($order->statuses->ttn_string)['data'][0];
        NewPostTtnTrack::updateOrCreate(array('order_id' => $order->id),
            array(
                'customer_id' => $order->customer->id,
                'prom_id' => $id,
                'ref' => $track['RefEW'],
                'int_doc_number' => $track['Number'],
                'estimate_delivery_date' => Carbon::parse($track['ScheduledDeliveryDate']),
                'status' => $track['Status'],
                'status_code' => (int) $track['StatusCode'],
                'redelivery' => $track['Redelivery'],
                'redelivery' => $track['Redelivery'],
                'redelivery_sum' => $track['RedeliverySum'],
                'phone' => $track['PhoneRecipient'],
                'full_name' => $track['RecipientFullNameEW'],
                'city' => $track['CityRecipient'],
                'warehouse' => $track['WarehouseRecipient'],
                'warehouse_ref' => $track['RecipientWarehouseTypeRef'],
                'recipient_address' => $track['RecipientAddress'],
                'date_created' => Carbon::parse($track['DateCreated']),
                'date_first_day_storage' => isset($track['DateFirstDayStorage']) ? Carbon::parse($track['DateFirstDayStorage']) : null,
                'document_weight' => $track['DocumentWeight'],
                'check_weight' => $track['CheckWeight'],
                'document_cost' => $track['DocumentCost'],
            )
        );
        dd($track);
    }
}
