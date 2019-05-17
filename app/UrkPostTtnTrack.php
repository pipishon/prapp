<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UrkPostTtnTrack extends Model
{
    protected $guarded = [];

    public function scopeSearch ($query, $input)
    {
      if (isset($input['ttn'])) {
          $query = $query->where('ttn', 'LIKE', '%'.$input['ttn'].'%');
      }
      if (isset($input['prom_id'])) {
          $query = $query->where('prom_id', 'LIKE', '%'.$input['prom_id'].'%');
      }
    }

    public function order()
    {
      return $this->hasOne('App\Order', 'prom_id', 'prom_id');
    }
    //
}
