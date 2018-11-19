<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductLabelp extends Model
{
	protected $guarded = [];

  protected $appends = ['name'];

  public function label()
  {
    return $this->hasOne('App\Labelp', 'id', 'suplier_id');
  }

  public function getNameAttribute ($val) {
    return $this->label->name;
  }
    //
}
