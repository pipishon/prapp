<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CustomerStatistic;
use Carbon\Carbon;

class Customer extends Model
{
	protected $guarded = [];

  protected $appends = ['name'];
 /* protected $appends = [
    'first_order', 'last_order', 'count_orders', 'total_price',
    'aver_price'
  ];*/

  public function statistic()
  {
    return $this->hasOne('App\CustomerStatistic');
  }

  public function phones()
  {
    return $this->hasMany('App\Phone');
  }

  public function emails()
  {
    return $this->hasMany('App\Email');
  }

  public function orders()
  {
    return $this->hasMany('App\Order')->orderBy('prom_date_created', 'desc');
  }
    //
  public function scopeSearch ($query, $input)
  {

      foreach (['id'] as $type) {
        if (isset($input[$type])) {
          $query = $query->where($type, 'LIKE', '%'.$input[$type].'%');
        }
      }

      if (isset($input['order_by'])) {
        $query = $query->orderBy($input['order_by'], $input['order_direction']);
      }

      return $query;
  }

  public function getNameAttribute ($val) {
    if (isset($this->attributes['name'])) {
      return unserialize($this->attributes['name']);
    }
    return array();
  }

  public function setPromIdAttribute ($val) {
    $old_val = '';
    if (isset($this->attributes['prom_id'])) {
      $old_val = $this->attributes['prom_id'] . ';';
    }
    $this->attributes['prom_id'] = $old_val . $val;
  }

  public function setNameAttribute ($val) {
    if (isset($this->attributes['name'])) {
      $names = unserialize($this->attributes['name']);
    } else {
      $names = array();
    }
    if (is_array($val)) {
      $names = $val;
    } else {
      $names[] = $val;
    }
    $this->attributes['name'] = serialize($names);
  }

  public function recalcStatistics ()
  {
    $customer = $this;
    $id = $customer->id;
    $statistic = CustomerStatistic::firstOrNew(['customer_id' => $id]);
    $statistic->count_orders = 0;
    $statistic->count_orders_delivered = 0;
    $statistic->count_orders_received = 0;
    $statistic->count_orders_canceled = 0;
    $statistic->total_price = 0;
    $statistic->aver_price = 0;
    if ($customer->orders->first()) {
      $statistic->first_order = $customer->orders->last()->prom_date_created;
      $statistic->last_order = $customer->orders->first()->prom_date_created;
      $num_rows = count($customer->orders);
      if ($num_rows > 1) {
        $prelast = $customer->orders()->offset(1)->limit(1)->get()->first()->prom_date_created;
      } else {
        $prelast = $customer->orders()->first()->prom_date_created;
      }
      $prelast_date = Carbon::parse($prelast);
      $length = Carbon::parse($statistic->last_order)->diffInDays($prelast_date);
      $statistic->days_after_last_order = $length;
    } else {
      $statistic->first_order = $statistic->last_order = $customer->created_at;
      $statistic->days_after_last_order = 1;
    }
    foreach ($customer->orders as $order) {
      $statistic->count_orders = $statistic->count_orders + 1;
      switch ($order->status) {
        case 'delivered':
          $statistic->count_orders_delivered++;
          break;
        case 'received':
          $statistic->count_orders_received++;
          break;
        case 'canceled':
          $statistic->count_orders_canceled++;
          break;
      }
      if ($order->status == 'delivered') {

      }
      if ($order->status != 'canceled') {
        $price = (int) preg_replace('/\s+/u', '', $order->price);
        $statistic->total_price = $statistic->total_price + $price;
        $statistic->aver_price = ($statistic->aver_price == 0) ? (int) ($statistic->aver_price + $price) / 2 : $price;
      }

    }
    $statistic->save();
  }
/*
  public function getFirstOrderAttribute () {
    return $this->countDays($this->orders->first()->prom_date_created);
  }

  public function getLastOrderAttribute () {
    return $this->countDays($this->orders->last()->prom_date_created);
  }

  public function getCountOrdersAttribute () {
    return $this->orders->count();
  }

  public function getTotalPriceAttribute () {
    return (int) $this->orders->sum('price');
  }

  public function getAverPriceAttribute () {
    return (int) $this->orders->average('price');
  }

  public function countDays ($date) {
    $date = new \DateTime($date);
    $now = new \DateTime();
    return (int) $now->diff($date)->format('%a');
  }*/
}
