<?php

namespace App\Http\Controllers;
use App\NewPostTtnTrack;
use App\NewPostApi;
use App\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class NewPostTtnTrackController extends Controller
{
    public function index(Request $request)
    {
      $input = $request->all();
      $per_page = (isset($input['per_page'])) ? (int) $input['per_page'] : 20;

      $np_track = new NewPostTtnTrack;

      if (!$request->has('all')) {
          //$np_track = $np_track->whereNotIn('status_code', array(9, 11));

          $np_track = $np_track->where(function ($query) {
              $query->where(function ($query) {
                  $query->where('status_code', '!=', '9')->where('redelivery', 0);
              })->orWhere(function ($query) {
                  $query->where('status_code', '!=', '11')->where('redelivery', 1);
              });
          });
      }
      if ($request->has('today')) {
          $np_track = $np_track->whereDate('date_created', '=', Db::Raw('CURDATE()'));
      }

      $np_track = $np_track->orderByRaw('ISNULL(date_first_day_storage), date_first_day_storage', 'asc');
      $np_track = $np_track->orderBy('date_created', 'asc');

      $custom = collect(array(
          'nums' => array(
            'all' => NewPostTtnTrack::all()->count(),
            'today' => NewPostTtnTrack::whereDate('date_created', '=', Db::Raw('CURDATE()'))->count(),
            'usual' => NewPostTtnTrack::whereNotIn('status_code', array(9, 11))->count()
        )
      ));

      $np_track = $np_track->search($input)->with('ttn')->paginate($per_page);
      $np_track = $custom->merge($np_track);
      return $np_track;
    }

    public function addTtn(Request $request)
    {
        $ttn = $request->input('ttn');
        $order_id = $request->input('order_id');
        $redelivery = $request->input('redelivery');
        $order = Order::find($order_id);
        $np = new NewPostApi;
        $track = $np->track(array($ttn))['data'][0];
        NewPostTtnTrack::updateOrCreate(array('order_id' => $order->id),
            array(
                'customer_id' => $order->customer->id,
                'prom_id' => $order->prom_id,
                'int_doc_number' => $ttn,
                'status' => $track['Status'],
                'status_code' => (int) $track['StatusCode'],
                'ref' => '',
                'redelivery' => $redelivery
            )
        );
        return $track;
    }
    public function checkStatus()
    {
        //$np_tracks = NewPostTtnTrack::whereNotIn('status_code', array(9, 11))->get();
        $np_tracks = NewPostTtnTrack::where(function ($query) {
            $query->where(function ($query) {
                $query->where('status_code', '!=', '9')->where('redelivery', 0);
            })->orWhere(function ($query) {
                $query->where('status_code', '!=', '11')->where('redelivery', 1);
            });
        });
        $ttns = $np_tracks->pluck('int_doc_number');
        $np = new NewPostApi;
        $tracks = $np->track($ttns)['data'];
        foreach ($tracks as $track) {
            if ($track['StatusCode'] == 1) {
                $np_track = NewPostTtnTrack::where('int_doc_number', $track['Number'])->first();
                if ($np_track == null) continue;
                $np_track->update(array(
                    'status' => $track['Status'],
                    'status_code' => $track['StatusCode'],
                ));
            } else {
                $status = $track['StatusCode'];

                $np_track = NewPostTtnTrack::where('int_doc_number', $track['Number'])->first();
                if ($np_track == null) continue;
                $np_track->update(array(
                    'estimate_delivery_date' => Carbon::parse($track['ScheduledDeliveryDate']),
                    'status' => $track['Status'],
                    'status_code' => (int) $track['StatusCode'],
                    'redelivery' => $track['Redelivery'],
                    'redelivery_sum' => $track['RedeliverySum'],
                    'phone' => $track['PhoneRecipient'],
                    'full_name' => $track['RecipientFullNameEW'],
                    'city' => $track['CityRecipient'],
                    'warehouse' => $track['WarehouseRecipient'],
                    'warehouse_ref' => $track['WarehouseRecipientRef'],
                    'recipient_address' => $track['RecipientAddress'],
                    'date_created' => Carbon::parse($track['DateCreated']),
                    'date_first_day_storage' => isset($track['DateFirstDayStorage']) ? Carbon::parse($track['DateFirstDayStorage']) : null,
                    'document_weight' => $track['DocumentWeight'],
                    'check_weight' => $track['CheckWeight'],
                    'document_cost' => $track['DocumentCost'],
                ));
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
                        if ($np_track->date_delivered == null) {
                            $np_track->delivery_days = 0;
                            $np_track->date_delivered = Carbon::now();
                        }
                        if ($np_track->date_received == null) {
                            $np_track->date_received = Carbon::now();
                        }
                        break;

                }
            }
            $today = Carbon::now();
            if ($np_track->date_delivered == null) {
                $np_track->send_days = $today->diffInDays(Carbon::parse($np_track->date_created)->startOfDay());
            }
            if ($np_track->send_days == null) {
                $np_track->send_days = 0;
            }
            if ($np_track->date_received == null && $np_track->date_delivered != null) {
                $np_track->delivery_days = $today->diffInDays(Carbon::parse($np_track->date_delivered)->startOfDay());
            }
            $np_track->save();
        }
    }
    //
}
