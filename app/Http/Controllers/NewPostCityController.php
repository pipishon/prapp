<?php

namespace App\Http\Controllers;

use App\NewPostCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewPostCityController extends Controller
{
    public function index (Request $request)
    {
        return DB::table('new_post_cities')->select(['description','ref'])->get();
        //return NewPostCity::all()->pluck('description');
    }

    public function warehouses (Request $request)
    {
        $city = $request->input('city');
        $new_post_city = NewPostCity::where('description', '=', $city)->first();
        return $new_post_city->warehouses()->get()->pluck('description');
    }

    public function isAddressValide (Request $request)
    {
        //$city = $request->input('city');
        $address = $request->input('address');
        return NewPostCity::isAddressValid($address);
    }
    //
}
