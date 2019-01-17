<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
	protected $guarded = [];

  protected $appends = ['image', 'sku', 'name', 'price', 'prom_id', 'purchase'];

  public function product()
  {
    return $this->hasOne('App\Product', 'id', 'product_id');
  }
    //
  public function getSkuAttribute ($val) {
    return $this->product->sku;
  }

  public function getPurchaseAttribute ($val) {
    return $this->product->purchase_price;
  }

  public function getNameAttribute ($val) {
    return $this->product->name;
  }

  public function getImageAttribute ($val) {
    return $this->product->main_image;
  }

  public function getPriceAttribute ($val) {
    return $this->product->price;
  }

  public function getPromIdAttribute ($val) {
    return $this->product->prom_id;
  }
}
