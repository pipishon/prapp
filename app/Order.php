<?php

namespace App;

use App\Customer;
use App\MessageEmail;
use App\Sms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
	protected $guarded = [];

  public function statuses()
  {
    return $this->hasOne('App\OrderStatus');
  }

  public function ttn()
  {
    return $this->hasOne('App\NewPostTtn');
  }

  public function products()
  {
    return $this->hasMany('App\OrderProduct');
  }

  public function emailStatuses ()
  {
    return $this->hasMany('App\MessageEmail', 'order_id', 'prom_id');
  }

  public function smsStatuses ()
  {
    return $this->hasMany('App\Sms', 'order_id', 'prom_id');
  }

  public function customer()
  {
    return $this->hasOne('App\Customer', 'id', 'customer_id');
  }

  public function validateOrder() {
      $errors = array();
      $new_post = array('Новая Почта', '«Нова пошта» - Покупка без риска');
      if (in_array($this->delivery_option, $new_post)) {
          if ($this->client_first_name == '') $errors['Имя'] = 'пустое';
          if ($this->client_last_name == '') $errors['Фамилия'] = 'пустое';
          if ((is_object($this->ttn) &&
              is_null(NewPostCity::isAddressValid($this->ttn->full_address))) ||
              (!is_object($this->ttn) &&
              is_null(NewPostCity::isAddressValid($this->delivery_address)))) $errors['Адресс'] = 'не валидный';
          if (floatval($this->statuses->shipment_weight) == 0) $errors['Вес'] = 'не указан';
          if ($this->statuses->ttn_string != '') $errors['ТТН'] = 'указан';
      }
      if (count($errors)) {
          $result = array('succes' => false, 'errors' => $errors);
      } else {
          $result = array('succes' => true);
      }
      return $result;
  }

  public function scopeSearch ($query, $input)
  {

      foreach (['email', 'client_first_name', 'prom_id', 'delivery_option'] as $type) {
        if (isset($input[$type])) {
          if ($type == 'delivery_option' && $input[$type] == 'Новая Почта') {
            $query = $query->whereIn('delivery_option', ['Новая Почта', '«Нова пошта» - Покупка без риска']);
          } else {
            $query = $query->where($type, 'LIKE', '%'.$input[$type].'%');
          }
        }
      }
      if (isset($input['phone'])) {
          $customer = Customer::whereIn('customers.id', function($q) use ($input) {
            $q->from('phones')->select('phones.customer_id')->where('phones.phone', 'like', '%'.$input['phone'].'%');
          })->get();
          if ($customer->count() == 0) {
              $customer = Customer::where('name', 'like', '%'.$input['phone'].'%')
                  ->orWhere('instagram_id', 'like', '%'.$input['phone'].'%')->get();
          }
          if ($customer != null) {
             $ids = $customer->pluck('id')->toArray();
             $query = $query->whereIn('customer_id', $ids);//$query->orWhere('client_last_name', 'LIKE', '%'.$input[$type].'%')->orWhere('client_first_name', 'LIKE', '%'.$input[$type].'%');
          }
      }
      if (isset($input['status']) || isset($input['today_delivery']) || isset($input['not_payed'])) {
        $query = $query->join('order_statuses', 'orders.id', '=', 'order_statuses.order_id');
      }

      if (isset($input['payment_status'])) {
        $query = $query->where('payment_status', $input['payment_status']);
      }

      if (isset($input['today_delivery'])) {
        if ($input['today_delivery'] == '1') {
            $query = $query->where('shipment_date', DB::raw('CURDATE()'));
        }
      }

      if (isset($input['not_payed'])) {
        if ($input['not_payed'] == '1') {
            $query = $query->where('payment_status', 'Не оплачен');
        }
      }

      if (isset($input['status'])) {
        if ($input['status'] == 'not-delivered') {
          $query = $query->where('status', '!=', 'delivered')->where('status', '!=', 'canceled');
        }
        $query = $query->orderBy(DB::raw('IF(order_statuses.shipment_date IS NOT NULL AND order_statuses.shipment_date >= CURDATE(), ABS(DATEDIFF(order_statuses.shipment_date, NOW())), 1000000)'), 'asc')->orderBy('order_statuses.collected', 'asc')->latest('order_statuses.payment_date');
      }


      if (isset($input['price_from'])) {
        $query = $query->where('price', '>', $input['price_from'])->where('price', '<', $input['price_to']);
      }


      if (isset($input['order_by'])) {
        $query = $query->orderBy($input['order_by'], $input['order_direction']);
      }

      return $query;
  }
    //
}
