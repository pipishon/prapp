<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UkrPostTtnTrack;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Cron;

class UkrPostTtnTrackController extends Controller
{
    public function index(Request $request)
    {
      $input = $request->all();
      $per_page = (isset($input['per_page'])) ? (int) $input['per_page'] : 20;

      $urk_track = UkrPostTtnTrack::with('order')->search($input);

      if (!$request->has('all')) {
          //$urk_track = $urk_track->whereNotIn('status_code', array(9, 11));

          $urk_track = $urk_track->whereNotIn('status_code', array(41002, 41003, 41004, 41022));
          $urk_track = $urk_track->where('current', 1);
      }
      if ($request->has('today')) {
          $urk_track = $urk_track->whereDate('date_created', '=', Db::Raw('CURDATE()'));
      }

      $urk_track = $urk_track->orderByRaw("FIELD(status_code, '41002', '41003', '41004', '41022') DESC");

      $urk_track = $urk_track->orderBy('date_created', 'asc');

      $custom = collect(array(
          'nums' => array(
            'all' => UkrPostTtnTrack::all()->count(),
            'today' => UkrPostTtnTrack::whereDate('date_created', '=', Db::Raw('CURDATE()'))->count(),
            'usual' => UkrPostTtnTrack::whereNotIn('status_code', array(41002, 41003, 41004, 41022))->where('current', 1)->count()
        )
      ));

      $urk_track = $urk_track->paginate($per_page);
      $urk_track = $custom->merge($urk_track);
      return $urk_track;
    }
    //
    //
    public function addTtn(Request $request)
    {
        $ttn = $request->input('ttn');
        $prom_id = $request->input('prom_id');
        //$track = $np->track(array($ttn))['data'][0];
        UkrPostTtnTrack::updateOrCreate(array('prom_id' => $prom_id),
            array(
                'ttn' => $ttn,
                'status_code' => 0,
                'date_created' => Carbon::now()
            )
        );
        return 'success';
    }

    public function checkStatus()
    {
        $cron = Cron::find(10);
        $cron->last_job = Carbon::now();
        $cron->success = false;
        $cron->save();
        $ukr_tracks = UkrPostTtnTrack::whereNotIn('status_code', array(41002, 41003, 41004, 41022))->get();
        foreach ($ukr_tracks as $track) {

            $url = 'http://services.ukrposhta.ua/barcodestatistic/barcodestatistic.asmx/GetBarcodeInfo?culture=1&guid=1&barcode='.$track->ttn;
            /*$ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_PROXY, '10.0.0.80:3128');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $xml = simplexml_load_string($result);*/
            $xml = simplexml_load_file($url);
            $status_code = (int) $xml->code;
            $track->status_code = $status_code;
            $track->status = (string) $xml->eventdescription;

            switch ($status_code) {
                    case 21701:
                        if ($track->date_delivered == null) {
                            $track->date_delivered = Carbon::now();
                        }
                        break;
                    case 41002:
                    case 41003:
                    case 41004:
                    case 41022:
                        if ($track->date_delivered == null) {
                            $track->delivery_days = 0;
                            $track->date_delivered = Carbon::now();
                        }
                        if ($track->date_received == null) {
                            $track->date_received = Carbon::now();
                        }
                        break;
            }
            $today = Carbon::now();
            if ($track->date_delivered == null) {
                $track->send_days = $today->diffInDays(Carbon::parse($track->date_created)->startOfDay());
            }
            if ($track->send_days == null) {
                $track->send_days = 0;
            }
            if ($track->date_received == null && $track->date_delivered != null) {
                $track->delivery_days = $today->diffInDays(Carbon::parse($track->date_delivered)->startOfDay());
            }
            $track->save();
        }
        $cron->success = true;
        $cron->save();
    }
}
