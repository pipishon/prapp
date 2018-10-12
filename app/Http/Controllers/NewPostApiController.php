<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NewPostTtn;
use App\NewPostCity;
use App\NewPostApi;
use App\NewPostWarehouse;
use Carbon\Carbon;

class NewPostApiController extends Controller
{
    public function getTtn (Request $request)
    {
      $order_id = $request->input('order_id');
      $new_post_ttn = NewPostTtn::where('order_id', $order_id)->first();

      $data = $request->except(['places', 'only_save']);
      $data['date'] = Carbon::now()->format('Y-m-d');

      if ($new_post_ttn == null) {
        $new_post_city = NewPostCity::where('description', '=', $data['city'])->first();
        $new_post_warehouse = $new_post_city->warehouses()->where('description', $data['warehouse'])->first();
        $data['full_address'] = $data['city'].', '.$data['warehouse'];
        $data['warehouse'] = $new_post_warehouse->ref;
        $new_post_ttn = NewPostTtn::firstOrNew($data);
        $data['date'] = Carbon::now()->format('d.m.Y');
        $data['places'] = $request->input('places');
      } else {
        $data['full_address'] = $data['city'].', '.$data['warehouse'];
        $data['warehouse'] = $new_post_ttn->warehouse;
        $new_post_ttn->fill($data);
        $data['date'] = Carbon::now()->format('d.m.Y');
        $data['places'] = $request->input('places');
        $data['ref'] = $new_post_ttn->ref;
      }
      if (!$request->has('only_save')) {
          $np = new NewPostApi();
          $responce = $np->getTtn($data);
          if ((bool) $responce['success'] == true) {
              $np_data = $responce['data'][0];
              $new_post_ttn->int_doc_number = $np_data['IntDocNumber'];
              $new_post_ttn->estimated_delivery_date = Carbon::parse($np_data['EstimatedDeliveryDate']);
              $new_post_ttn->cost_on_site = $np_data['CostOnSite'];
              $new_post_ttn->ref = $np_data['Ref'];
          }
      }
      $new_post_ttn->save();
      return $new_post_ttn;
    }
}
