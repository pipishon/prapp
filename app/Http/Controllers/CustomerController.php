<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Email;
use App\Phone;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $input = $request->all();

      $per_page = (isset($input['per_page'])) ? (int) $input['per_page'] : 20;
      $customer = Customer::join('customer_statistics as st', 'st.customer_id', '=', 'customers.id')->select('customers.*')->with('statistic');
      if (isset($input['email']) && $input['email'] != '') {
        $customer->whereIn('customers.id', function($query) use ($input) {
          $query->from('email')->select('emails.customer_id')->where('emails.email', 'like', '%'.$input['email'].'%');
        });
        //$customer->whereHas('emails', function($query) use ($input) { $query->where('email', 'like', '%'.$input['email'].'%');});
      }
      if (isset($input['phone']) && $input['phone'] != '') {
        $customer->whereIn('customers.id', function($query) use ($input) {
          $query->from('phones')->select('phones.customer_id')->where('phones.phone', 'like', '%'.$input['phone'].'%');
        });
        //$customer->whereHas('phones', function($query) use ($input) { $query->where('phone', 'like', '%'.$input['phone'].'%');});
      }
      return $customer->with('emails')->with('phones')->search($input)->orderBy('last_order', 'desc')->paginate($per_page);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show (Customer $customer)//$id)
    {
      /*
      $customer->load('orders');
      $total = 0;
      $aver = 0;
      foreach ($customer->orders as $order) {
        $price = (int) preg_replace('/\s+/u', '', $order['price']);
        $total = $total + $price;
        $aver = ($aver == 0) ? $price : ($aver + $price) / 2;
      }
      $customer->total_price = $total;
      $customer->aver_price = $aver;
      $customer->save();
       */

      return $customer->load('orders')->load('emails')->load('phones')->load('statistic')->load(['orders' => function ($q) { $q->orderBy('prom_date_created', 'desc'); }]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
      $return = '';
      $req = $request->all();

      /*foreach ($req as $name => $val) {
        //if ($val === null) continue;
        if (!in_array($name, ['manual_status', 'name', 'comment', 'stars', 'facebook_id', 'instagram_id'])) continue;
        if (is_null($val) && !in_array($name, ['stars', 'facebook_id', 'instagram_id'])) {
          $val = '';
        }
        $customer->{$name} = $val;
      }*/
      $customer->update($request->except('merge', 'ids', 'emails', 'orders', 'phones', 'statistic', 'prom_id'));
     // $customer->name = $request->input('name');


      if (isset($req['merge'])) {
        $ids = $req['ids'];
        $customer = $this->mergeCustomers($customer, $ids);
        $return = 'merged ' . count($ids);
      }

      $customer->save();
      return $req;
    }

    public function mergeCustomers($customer, $ids)
    {
      foreach ($ids as $id) {
        $c = Customer::where('id', $id)->with('emails')->with('phones')->with('orders')->first();
        $customer->name = array_merge($customer->name, $c->name);
        foreach (['phones', 'emails', 'orders'] as $name) {
          foreach ($c->{$name} as $item) {
            $item->customer_id = $customer->id;
            $item->save();
           /* if ($name == 'orders') {
              $customer->count_orders = $customer->count_orders + 1;
              $customer->total_price = $customer->total_price + (int) $item->price;
              $customer->aver_price = ($customer->aver_price + $item->price) / 2;

              $date = new \DateTime($item->prom_date_created);
              $date->setTimezone(new \DateTimeZone('Europe/Kiev'));
              $date_str = $date->format('Y-m-d H:i');

              if ($date < new \DateTime($customer->first_order)) {
                  $customer->first_order = $date_str;
              }

              if ($date > new \DateTime($customer->last_order)) {
                  $customer->last_order = $date_str;
              }
            }*/
          }
        }
        $c->delete();
      }
      $customer->recalcStatistics();
      return $customer;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        //
    }


    public function addPhoneEmail (Request $request)
    {
      $id = $request->input('id');
      if ($request->has('phone')) {
        Phone::firstOrCreate(['phone' => $request->input('phone'), 'customer_id' => $id]);
      }
      if ($request->has('email')) {
        Email::firstOrCreate(['email' => $request->input('email'), 'customer_id' => $id]);
      }
    }

    public function getByPhoneEmail (Request $request)
    {
      $input = $request->all();
      //if (!isset($input['id'])) return;

      $customer = Customer::with('orders');

      if (isset($input['email']) && $input['email'] != '') {
        $customer->whereIn('customers.id', function($query) use ($input) {
          $query->from('emails')->select('emails.customer_id')->where('emails.email', 'like', '%'.$input['email'].'%');
        });
      }
      if (isset($input['phone']) && $input['phone'] != '') {
        $customer->whereIn('customers.id', function($query) use ($input) {
          $query->from('phones')->select('phones.customer_id')->where('phones.phone', 'like', '%'.$input['phone'].'%');
        });
      }
      return $customer->with('emails')->with('phones')->first();
    }


    public function recalcStatistics (Request $request)
    {
      $per_page = 200;

      $customers = Customer::paginate($per_page);
      //$customers = Customer::paginate($per_page);
      foreach ($customers as $customer) {
        $customer->recalcStatistics();
      }
      return Customer::with('orders')->paginate($per_page);
    }
}
