<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UkrPostTtnTrack;

class UrkPostTtnTrackController extends Controller
{
    public function index(Request $request)
    {
      $input = $request->all();
      $per_page = (isset($input['per_page'])) ? (int) $input['per_page'] : 20;

      $urk_track = UkrPostTtnTrack::with('order')->search($input);

      if (!$request->has('all')) {
          //$urk_track = $urk_track->whereNotIn('status_code', array(9, 11));

          $urk_track = $urk_track->where(function ($query) {
              $query->where(function ($query) {
                  $query->where('status_code', '!=', '9')->where('redelivery', 0);
              })->orWhere(function ($query) {
                  $query->where('status_code', '!=', '11')->where('redelivery', 1);
              });
          });

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
            'usual' => NewPostTtnTrack::whereNotIn('status_code', array(41002, 41003, 41004, 41022))->where('current', 1)->count()
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
        UkrPostTtnTrack::updateOrCreate(array('prom_id' => $prom_d),
            array(
                'ttn' => $ttn,
                'status' => 'status',//$track['Status'],
                'status_code' => (int) '123',
            )
        );
        return 'success';
    }

    public function checkStatus()
    {
        $urk_tracks = NewPostTtnTrack::whereNotIn('status_code', array(41002, 41003, 41004, 41022))->get();
        foreach ($ukr_tracks as $track) {
            $url = 'http://services.ukrposhta.ua/barcodestatistic/barcodestatistic.asmx/GetBarcodeInfo?guid=&barcode='.$track->ttn;
            $xml = simplexml_load_file($url);
        }

    }
}
