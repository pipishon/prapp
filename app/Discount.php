<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
	protected $guarded = [];
    protected $appends = ['vals', 'nums'];
    //
    public function getValsAttribute ($val) {
      if (isset($this->attributes['vals'])) {
        return unserialize($this->attributes['vals']);
      }
      return array();
    }
      public function getNumsAttribute ()
      {
        return $this->hasMany('App\DiscountProduct', 'discount_id', 'id')->count();
      }

      public function setValsAttribute ($val) {
        if (isset($this->attributes['vals'])) {
          $vals = unserialize($this->attributes['vals']);
        } else {
          $vals = array();
        }
        if (is_array($val)) {
          $vals = $val;
        } else {
          $vals[] = $val;
        }
        $this->attributes['vals'] = serialize($vals);
      }
}
