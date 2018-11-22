<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
	protected $guarded = [];

  public function scopeSearch ($query, $input)
  {

      foreach (['name', 'sku', 'id', 'category', 'presence', 'status', 'description', 'prom_id'] as $type) {
        if (isset($input[$type])) {
          $query = $query->where($type, 'LIKE', '%'.$input[$type].'%');
        }
      }

      if (isset($input['price_from'])) {
        $query = $query->where('price', '>', $input['price_from'])->where('price', '<', $input['price_to']);
      }

      if (isset($input['suplier']) && $input['suplier'] != '') {
          $query = $query->join('product_supliers', 'product_supliers.product_id', 'products.id')
              ->join('supliers', 'product_supliers.suplier_id', 'supliers.id')
              ->select('products.*')
              ->where('supliers.name', 'like', '%'.$input['suplier'].'%');
      }


      if (isset($input['order_by'])) {
        $query = $query->orderBy($input['order_by'], $input['order_direction']);
      }

      return $query;
  }
    //
  public function supliers()
  {
    return $this->hasMany('App\ProductSuplier');
  }

  public function labels()
  {
    return $this->hasMany('App\ProductLabelp');
  }
}
