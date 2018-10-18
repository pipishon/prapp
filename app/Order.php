<?php

namespace App;

use App\Customer;
use App\Dictionary;
use App\MessageEmail;
use App\Sms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
	protected $guarded = [];
  protected $appends = ['validet'];

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

  public function getValidetAttribute ()
  {
    return $this->validateOrder();
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
      $result = array(
        'Доставка' => true,
        'Адрес' => true,
        'Клиент' => true,
        'Оплата' => true,
        'Сборка' => true,
        'Вес' => true,
      );

      $delivery = Dictionary::whereIn('from', $this->delivery_option)->first()->to;
      $payment = Dictionary::whereIn('from', $this->payment_option)->first()->to;
      switch ($delivery) {
          case 'Новая Почта':
          case 'НП без риска':
              if (in_array($payment, ['не указан', 'Наличные'])) $result['Доставка'] = false;
              break;
          case 'Укрпочта':
              if (in_array($payment, ['не указан', 'Наличные', 'Наложенный платеж'])) $result['Доставка'] = false;
              break;
          case 'Самовывоз':
              if (in_array($payment, ['не указан', 'Наложенный платеж'])) $result['Доставка'] = false;
              break;
      }

      if ((is_object($this->ttn) &&
          is_null(NewPostCity::isAddressValid($this->ttn->full_address))) ||
          (!is_object($this->ttn) &&
          is_null(NewPostCity::isAddressValid($this->delivery_address)))) {
              $result['Адресс'] = false;
      }

      if (in_array($delivery, ['Новая Почта', 'НП без риска', 'Укрпочта']) &&
          ($this->client_first_name == '' || $this->client_last_name == '')) {
              $result['Клиент'] = false;
      }
      $phone_regexp = /^\+\d{12}$/;
      $email_regexp = /^\+\d{12}$/;
      // phone email validation
      if ($this->status == 'delivered' && $this->satuses->payment_status == 'Не оплачен') {
              $result['Оплата'] = false;
      }
      if ($this->status == 'delivered' && !$this->satuses->collected) {
              $result['Сборка'] = false;
      }

      if (floatval(str_replace(',', '.', $this->statuses->shipment_weight)) == 0) {
              $result['Вес'] = false;
      }

      if (count($errors)) {
          $result = array('succes' => false, 'errors' => $errors);
      } else {
          $result = array('succes' => true);
      }
      if ($this->statuses->ttn_string != '') $result['ttn'] = true;
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
