<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NewPostCity extends Model
{
	protected $guarded = [];

    public function warehouses()
    {
        return $this->hasMany('App\NewPostWarehouse', 'city_ref', 'ref');
    }

    public static function isAddressValid ($address = '')
    {
        preg_match("/^([\w|-|(|)\s]+),(.*)/u", $address, $matches);
        $res = null;
        if (count($matches) > 1) {
          $res = DB::table('new_post_cities')
              ->join('new_post_warehouses', 'new_post_cities.ref', '=', 'new_post_warehouses.city_ref')
              ->where('new_post_cities.description', '=', $matches[1])
              ->where('new_post_warehouses.description', '=', trim($matches[2]))->get();
        }
        return ($res != null && count($res) == 1) ? '1' : '0';
    }
}
