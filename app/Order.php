<?php

namespace App;

use App\Customer;
use App\Dictionary;
use App\MessageEmail;
use App\Sms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\PromApi;
use Carbon\Carbon;

class Order extends Model
{
	protected $guarded = [];
  protected $appends = ['validet', 'price_discount', 'feedbackcount'];

  public function getFeedbackcountAttribute()
  {
    return $this->hasMany('App\MessageEmail', 'order_id', 'prom_id')->where('type', 'feedback')->count();
  }

  public function statuses()
  {
    return $this->hasOne('App\OrderStatus', 'order_id', 'id');
  }

  public function ttn()
  {
    return $this->hasOne('App\NewPostTtn');
  }

  public function ttntrack()
  {
    return $this->hasOne('App\NewPostTtnTrack');
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

  public function getPriceDiscountAttribute ()
  {
      $sum = 0;
      foreach ($this->products as $product) {
        $sum += $product->quantity * ($product->price - $product->price * $product->discount / 100);
      }
      return number_format($sum, 2, '.', '');
  }

  public function smsStatuses ()
  {
    return $this->hasMany('App\Sms', 'order_id', 'prom_id');
  }

  public function customer()
  {
    return $this->hasOne('App\Customer', 'id', 'customer_id');
  }


public function getProductGroupDiscounts ($products)
{
    $product_ids = Product::whereIn('prom_id', array_column($products, 'id'))->get()->pluck('id', 'prom_id')->toArray();
    foreach ($products as $key => $product) {
        if (isset($product_ids[$products[$key]['id']])) {
            $products[$key]['prom_id'] = $products[$key]['id'];
            $products[$key]['id'] = $product_ids[$products[$key]['prom_id']];
        }
    }
    $discount_ids = DB::table('discount_products')->select('discount_id', 'product_id')
        ->whereIn('product_id', $product_ids)->get()->pluck('discount_id', 'product_id')->toArray();
    $discounts = array();
    foreach ($products as $product) {
        foreach ($discount_ids as $product_id => $discount_id) {
            if ($product_id == $product['id']) {
                if (isset($discounts[$discount_id])){
                    $discounts[$discount_id] += $product['quantity'];
                } else {
                    $discounts[$discount_id] = $product['quantity'];
                }
            }
        }
    }
    $discount_vals = DB::table('discounts')->select('vals', 'id')->whereIn('id', array_values($discount_ids))->get()->pluck('vals', 'id')->toArray();

    $product_discounts = array();

    foreach ($discount_vals as $discount_id => $discount_val) {
        $vals = unserialize($discount_val);
        $percent = 0;
        foreach ($vals as $val) {
          if ($val['qty'] <= $discounts[$discount_id]) {
              $percent = $val['percent'];
          }
        }
        foreach ($products as $product) {
            foreach ($discount_ids as $product_id => $ds_id) {
                if ($product_id == $product['id'] && $ds_id == $discount_id) {
                    $product_discounts[$product_id] = (float) $percent;
                }
            }
        }
    }
    return $product_discounts;
}

  public function getProductDiscount($customer, $product, $product_quantity, $total_price, $product_price, $group_discounts) {
      $total_price = (float) str_replace(',', '.', preg_replace('/\s+/u', '', $total_price));
      $prom_discount = 0;
      if ($product->price != $product_price) {
        $prom_discount = ($product->price - $product_price) * 100 / $product->price;
      }


      if ($customer->statistic) {
          $num_orders = ($customer->statistic->count_orders > 2) ? 2 : $customer->statistic->count_orders - 1;
      } else {
          $num_orders = 1;
      }

      $customer_discount = 0;
        $table_discounts = json_decode(Settings::where('name','table_discounts')->value('value'), true);
        if (isset($table_discounts['enable']) && $table_discounts['enable']) {
            $price_key = 0;
            $qty_key = 0;
            foreach ($table_discounts['prices'] as $key => $price) {
                if ($total_price < (int) $price) {
                    $price_key = $key + 1;
                }
            }
            foreach ($table_discounts['quantities'] as $key => $qty) {
                if ($num_orders >= (int) $qty) {
                    $qty_key = $key;
                }
            }
            $customer_discount = (float) $table_discounts['vals'][$price_key][$qty_key];
        }
        $product_discount = 0;
      if (isset($group_discounts[$product->id])) {
          $product_discount = $group_discounts[$product->id];
      }


      $discount = 0;
      $discount_type = '';
      if ($prom_discount > $product_discount && $prom_discount > $customer_discount) {
          $discount = $prom_discount;
          $discount_type = 'prom';
      } else {
          $discount = $customer_discount;
          $discount_type = 'customer';
          if ($product_discount > $customer_discount) {
              $discount = $product_discount;
              $discount_type = 'product';
          }
      }
      return array('discount' => $discount, 'type' => $discount_type);
  }

    public function sendfeedback ()
    {
        $order_id = $this->prom_id;

        $email = ($this->statuses->custom_email != null) ? $this->statuses->custom_email : $this->email;
        $ivlen = openssl_cipher_iv_length('AES-128-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $iv = 'p/Ȅ����';
        $params = array(
            'hash' => rawurlencode(strtr(openssl_encrypt($order_id, 'AES-128-CBC', 'sercet', 0, $iv), '+/=', '-_,')),
            'order_id' => $order_id
        );

        $sputnik_email = SputnikEmail::where('email', $email)->first();
        if (!$sputnik_email) {
            $sputnik_email = SputnikEmail::create(['email' => $email]);
            $sputnik_email->subscribe();
        }

        $message_email = MessageEmail::create(array(
            'email' => $email,
            'order_id' => $order_id,
            'type' => 'feedback',
            'send_at' => Carbon::now(),
            'send_by' => 'manual'
        ));

        $sputnik_email->sendEvent('api-send-feedback', $params);
        return $message_email;
    }

  public function updateFromApi ($with_discounts = false)
  {
    $order_id = $this->prom_id;
    $api = new PromApi;
    $prom_order = $api->getItem($order_id, 'orders')['order'];
    $prom_products = $prom_order['products'];
    if ($prom_products == null) return;
    //$this->products()->delete();
    $total_price = 0;
    $ids = array();

    if ($with_discounts) {
        $group_discounts = $this->getProductGroupDiscounts($prom_products);
        $customer = $this->customer;
    }

    foreach ($prom_products as $prom_product) {
        $product = Product::where('prom_id', $prom_product['id'])->first();
        $product_price = floatval(str_replace(',', '.', $prom_product['price']));
        if ($product == null) {
            $product = Product::firstOrCreate(array(
                'name' => $prom_product['name'],
                'price' => $product_price,
                'sku' => $product['sku'],
                'main_image' => (string) $product['image'],
                'presence' => 'available',
                'status' => 'on_display',
            ));
        }
        $ids[] = $product->id;
        $order_product_update = array(
          'prom_price' => $product_price,
          'quantity' => str_replace(',','.', $prom_product['quantity']),
        );

        if ($with_discounts) {
            $discount_arr = $this->getProductDiscount(
                $customer,
                $product,
                str_replace(',','.', $prom_product['quantity']),
                $prom_order['price'],
                $product_price,
                $group_discounts
            );

            if ($discount_arr['discount'] != 0) {
                $order_product_update['discount'] = $discount_arr['discount'];
                $order_product_update['discount_descripiton'] = $discount_arr['type'];
            } else {
                $order_product_update['discount'] = 0;
            }
        }

        /*if (!OrderProduct::where('product_id', $product->id)
            ->where('order_id', $this->id)->exists() &&
            $product->price != $product_price &&
            $product->price != 0
        ) {
            $order_product_update['discount'] = ($product->price - $product_price) * 100 / $product->price;
        }*/
        $order_product = OrderProduct::updateOrCreate(array(
            'product_id' => $product->id,
            'order_id' => $this->id,
        ), $order_product_update);
        $total_price += floatval(str_replace(',', '.', $prom_product['price']));
    }

    OrderProduct::where('order_id', $this->id)->whereNotIn('product_id', $ids)->delete();
    $price = preg_replace('/\s+/u', '', $prom_order['price']);
    $price = str_replace(',','.', $price);
    $this->price = floatval($price);
    $this->save();
  }

  public function mapDeliveryPayment()
  {
      $payments = Dictionary::where('payment', '1')->get()->pluck('to', 'from');
      $deliveries = Dictionary::where('delivery', '1')->get()->pluck('to', 'from');
      if (!in_array($this->delivery_option, array_values($deliveries->toArray()))) {
        $delivery = trim($this->delivery_option);
        $this->delivery_option = isset($deliveries[$delivery]) ? $deliveries[$delivery] : 'не указан';
      }
      if (!in_array($this->payment_option, array_values($payments->toArray()))) {
        $payment = trim($this->payment_option);
        $this->payment_option = isset($payments[$payment]) ? $payments[$payment] : 'не указан';
      }
      $this->save();
  }

  public function validateOrder() {
      $status = array(
        'Доставка' => true,
        'Адрес' => true,
        'Клиент' => true,
        'Оплата' => true,
        'Сборка' => true,
        'Вес' => true,
      );

      $delivery = $this->delivery_option;
      $payment = $this->payment_option;
      switch ($delivery) {
          case 'Новая Почта':
          case 'НП без риска':
              if (in_array($payment, ['не указан', 'Наличные'])) $status['Доставка'] = false;
              break;
          case 'Укрпочта':
              if (in_array($payment, ['не указан', 'Наличные', 'Наложенный платеж'])) $status['Доставка'] = false;
              break;
          case 'Самовывоз':
              if (in_array($payment, ['не указан', 'Наложенный платеж'])) $status['Доставка'] = false;
              break;
          case 'не указан':
              $status['Доставка'] = false;
              break;
      }

      if (in_array($delivery, ['Новая Почта', 'НП без риска'])) {
          if ((is_object($this->ttn) &&
              is_null(NewPostCity::isAddressValid($this->ttn->full_address))) ||
              (!is_object($this->ttn) &&
              is_null(NewPostCity::isAddressValid($this->delivery_address)))) {
                  $status['Адрес'] = false;
          }
      }

      if ($delivery == 'Укрпочта' && $this->delivery_address == 'Не указан') {
          $status['Адрес'] = false;
      }

      if (!is_object($this->ttn)) {
        if (in_array($delivery, ['Новая Почта', 'НП без риска', 'Укрпочта']) &&
            ($this->client_first_name == '' || $this->client_last_name == '')) {
                $status['Клиент'] = false;
        }
      }
      $phone_regexp = '/^\+380\d{9}$/';
      $email = ($this->statuses->custom_email != null) ? $this->statuses->custom_email : $this->email;
      $phone = ($this->statuses->custom_phone != null) ? $this->statuses->custom_phone : $this->phone;
      if (($email != '' && filter_var($email, FILTER_VALIDATE_EMAIL) == false) || !preg_match($phone_regexp, $phone, $matches)) {
              $status['Клиент'] = false;
      }

      if (is_object($this->ttn) && !preg_match($phone_regexp, $this->ttn->phone, $matches)) {
              $status['Клиент'] = false;
      }
      // phone email validation
      if ($this->status == 'delivered' && $this->statuses->payment_status == 'Не оплачен') {
              $status['Оплата'] = false;
      }
      if ($this->status == 'delivered' && !$this->statuses->collected) {
              $status['Сборка'] = false;
      }

      $result = array();

      if (!in_array(false, $status)) {
        $result['success'] = 'not_weight';
      } else {
        $result['success'] = 'not';
      }

      if (in_array($delivery, ['Новая Почта', 'НП без риска'])) {
        if (floatval(str_replace(',', '.', $this->statuses->shipment_weight)) == 0) {
                $status['Вес'] = false;
        }
      }


      if (!in_array(false, $status)) {
        $result['success'] = 'all';
      }
      $result['statuses'] = $status;

      if ($this->statuses->ttn_string != '') $result['ttn'] = true;
      return $result;
  }

  public function scopeSearch ($query, $input)
  {

      foreach (['email', 'client_first_name', 'prom_id', 'delivery_option'] as $type) {
        if (isset($input[$type])) {
          if ($type == 'delivery_option' && $input[$type] == 'Новая Почта') {
            $query = $query->whereIn('delivery_option', ['Новая Почта', 'НП без риска']);
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
      if (isset($input['status']) || isset($input['payment_status']) || isset($input['today_delivery']) || isset($input['not_payed'])) {
        $query = $query->select('orders.*')->join('order_statuses', 'orders.id', '=', 'order_statuses.order_id');
      }

      if (isset($input['payment_status'])) {
        $query = $query->where('payment_status', $input['payment_status']);
      }

      if (isset($input['today_delivery'])) {
        if ($input['today_delivery'] == '1') {
            $query = $query->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('delivery_option', '!=', 'Самовывоз')->where('shipment_date', DB::raw('CURDATE()'));
                })->orWhere(function ($query) {
                    $query->where('delivery_option', 'Самовывоз')->whereNotIn('orders.status', ['delivered', 'canceled'])->where('shipment_date', '<=', DB::raw('CURDATE()'));
                });
            });
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
