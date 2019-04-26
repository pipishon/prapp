<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderProduct extends Model
{
	//protected $guarded = [];
	protected $fillable = ['discount', 'product_id', 'order_id', 'quantity', 'prom_price'];

  protected $appends = ['image', 'sku', 'name', 'price', 'prom_id', 'purchase', 'sort1' ];

  public function product()
  {
    return $this->hasOne('App\Product', 'id', 'product_id');
  }
    //
  public function getSamePayedAttribute ($val) {
      $same = DB::table('order_products')
          ->join('orders', 'orders.id', 'order_products.order_id')
          ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
          ->where('orders.status', 'received')
          ->where('order_products.product_id', $this->product_id)
          ->where('order_products.order_id', '<>', $this->order_id)
          ->where('order_statuses.payment_status', 'Оплачен')
          ->where('order_statuses.collected', '0')
          ->sum('order_products.quantity');
      return $same;
  }

  public function getSameNotPayedAttribute ($val) {
      $same = DB::table('order_products')
          ->join('orders', 'orders.id', 'order_products.order_id')
          ->join('order_statuses', 'orders.id', 'order_statuses.order_id')
          ->where('orders.status', 'received')
          ->where('order_products.product_id', $this->product_id)
          ->where('order_products.order_id', '<>', $this->order_id)
          ->where('order_statuses.payment_status', 'Не оплачен')
          ->where('order_statuses.collected', '0')
          ->sum('order_products.quantity');
      return $same;
  }

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
  public function getSort1Attribute ($val) {
    return $this->product->sort1;
  }
}
