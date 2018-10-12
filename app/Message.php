<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	protected $guarded = [];


  public function replies()
  {
    return $this->hasMany('App\Replies', 'message_id', 'prom_id');
  }

  public function scopeSearch ($query, $input)
  {
      foreach (['prom_status', 'crm_status', 'product_id', 'client_full_name', 'phone', 'message', 'prom_id', 'subject'] as $type) {
        if (isset($input[$type])) {
          $query = $query->where($type, 'LIKE', '%'.$input[$type].'%');
        }
      }

      if (isset($input['order_by'])) {
        $query = $query->orderBy($input['order_by'], $input['order_direction']);
      }

      return $query;
  }

}
