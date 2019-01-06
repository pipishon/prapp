<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Purchase;
use Carbon\Carbon;
use App\Suplier;

class PurchaseController extends Controller
{
  public function index (Request $request)
  {
    $suplier_name = $request->input('suplier');
    $suplier = Suplier::where('name', $suplier_name)->first();
    if ($suplier == null) return;
    $date = $request->input('date');
    return Purchase::where('suplier_id', $suplier->id)->where('date', $date)->get();
  }

  public function store (Request $request)
  {

    $suplier_name = $request->input('suplier');
    $suplier = Suplier::where('name', $suplier_name)->first();
    if ($suplier == null) return;
    Purchase::updateOrCreate(array(
        'suplier_id' => $suplier->id,
        'date' => Carbon::now()->toDateString()
      ),
      array(
        'id_qty_buy' => $request->input('id_qty_buy'),
        'products' => $request->input('products'),
    ));
  }

  public function getSavedDates (Request $request)
  {
    $suplier_name = $request->input('suplier');
    $suplier = Suplier::where('name', $suplier_name)->first();
    if ($suplier == null) return;
    return Purchase::where('suplier_id', $suplier->id)->get()->pluck('date');
  }
}
