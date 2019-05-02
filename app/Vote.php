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

      public function message()
      {
        return $this->hasOne('App\MessageEmail', 'order_id', 'order_id')->where('type', 'feedback');
      }
}
