<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
	protected $guarded = [];

  protected $appends = ['sku', 'name', 'price', 'prom_id'];

  public function product()
  {
    return $this->hasOne('App\Product', 'id', 'product_id');
  }
    //
  public function getSkuAttribute ($val) {

    return $this->product->sku;
  }

  public function getNameAttribute ($val) {
    return $this->product->name;
  }

  public function getPriceAttribute ($val) {
    return $this->product->price;
  }

  public function getPromIdAttribute ($val) {
    return $this->product->prom_id;
  }
}
