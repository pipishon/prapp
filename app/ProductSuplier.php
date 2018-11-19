<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductSuplier extends Model
{
	protected $guarded = [];

  protected $appends = ['name'];

  public function suplier()
  {
    return $this->hasOne('App\Suplier', 'id', 'suplier_id');
  }

  public function getNameAttribute ($val) {
    return $this->suplier->name;
  }
    //
}
