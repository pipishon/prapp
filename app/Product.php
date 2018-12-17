<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
	protected $guarded = [];

  protected $appends = ['orders'];

  public function getOrdersAttribute () {
      return 0;
    return DB::table('products')
        ->join('order_products', 'products.id', 'order_products.product_id')
        ->join('orders', 'orders.id', 'order_products.order_id')
        ->where('products.id', $this->id)
        ->where('orders.status', 'delivered')->count();
  }

    public static function createFromApi($prom_id)
    {
        $api = new PromApi;
        $prom_product = $api->getItem($prom_id, 'products')['products'];
        //dd($prom_id, $prom_product);
        $product = Product::create(array(
            'prom_id' => $prom_product['id'],
            'name' => $prom_product['name'],
            'sku' => (string) $prom_product['sku'],
            'status' => $prom_product['status'],
            'presence' => $prom_product['presence'],
            'group_id' => $prom_product['group']['id'],
            'category' => $prom_product['group']['name'],
            'price' => $prom_product['price'],
        ));
        return $product;
    }

  public function scopeSearch ($query, $input)
  {

      foreach (['name', 'sku', 'id', 'category', 'presence', 'status', 'description', 'prom_id'] as $type) {
        if (isset($input[$type])) {
          $query = $query->where($type, 'LIKE', '%'.$input[$type].'%');
        }
      }

      if (isset($input['filter'])) {
          foreach ($input['filter'] as $filter) {
              $filter = json_decode($filter, true);
              if ($filter['from'] == '-' || $filter['to'] == '-') {
                $query = $query->whereNull($filter['name']);
                continue;
              }
              if ($filter['from']) {
                  $query = $query->where($filter['name'], '>=', floatval($filter['from']));
              }
              if ($filter['to']) {
                  $query = $query->where($filter['name'], '<=', floatval($filter['to']));
              }
          }
      }

      if (isset($input['price_from'])) {
        $query = $query->where('price', '>', $input['price_from'])->where('price', '<', $input['price_to']);
      }

      if (isset($input['available']) && $input['available'] == 'true') {
          $query = $query->where('presence', 'available');
      }

      if (isset($input['on_display']) && $input['on_display'] == 'true') {
          $query = $query->where('status', 'on_display');
      }

      /*if (isset($input['availability']) && $input['availability'] != '') {
        $query = $query->where(function ($query) use ($input) {
            $query->where('status', $input['availability'])->orWhere('presence', $input['availability']);
        });
      }*/

      if (isset($input['suplier']) && $input['suplier'] != '' && $input['suplier'] != '-') {
          $query = $query->join('product_supliers', 'product_supliers.product_id', 'products.id')
              ->join('supliers', 'product_supliers.suplier_id', 'supliers.id')
              ->select('products.*')
              ->where('supliers.name', 'like', '%'.$input['suplier'].'%');
      }

      if (isset($input['suplier']) && $input['suplier'] == '-') {
        $query = $query->whereDoesntHave('supliers');
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

  public function morders()
  {
    return $this->hasMany('App\ProductMonthOrder');
  }

  public function suplierlinks()
  {
    return $this->hasMany('App\ProductSuplierLink');
  }

  public function labels()
  {
    return $this->hasMany('App\ProductLabelp');
  }

}
