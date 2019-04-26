<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
	protected $guarded = [];
    //
      public function order()
      {
        return $this->hasOne('App\Order', 'prom_id', 'order_id');
      }
}
