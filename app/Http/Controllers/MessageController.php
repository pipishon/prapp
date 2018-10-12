<?php

namespace App\Http\Controllers;

use App\Message;
use App\Customer;
use App\Phone;
use App\Email;
use App\PromApi;
use Illuminate\Http\Request;

class MessageController extends Controller
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
      return Message::search($input)->orderBy('prom_date_created', 'desc')->paginate($per_page);
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
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function show(Message $message)
    {
      $phone = $message->phone;
      $customer = Customer::whereIn('customers.id', function($query) use ($phone) {
        $query->from('phones')->select('phones.customer_id')->where('phones.phone', $phone);
      })->first();
      $prom_customer = '';
      if (!$customer) {
        $api = new PromApi;
        $prom_customer = $api->getCustomerByPhone($phone);
        if (empty($prom_customer)) {
          return array('message' => $message);
        }
        if (count($prom_customer['emails']) > 0) {
          foreach ($prom_customer['emails'] as $email) {
            $customer = Customer::whereIn('customers.id', function($query) use ($email) {
              $query->from('emails')->select('emails.customer_id')->where('emails.email', $email);
            })->first();
            if ($customer) {
              Phone::create(array(
                'customer_id' => $customer->id,
                'phone' => $phone,
              ));
              break;
            }
          }
        }
        if (!$customer) {
          $date = new \DateTime();
          $date->setTimezone(new \DateTimeZone('Europe/Kiev'));
          $date_str = $date->format('Y-m-d H:i');

          $total_payout = (int) preg_replace('/\s+/u', '', $prom_customer['total_payout']);
          $customer = Customer::firstOrCreate(array(
            'name' => $prom_customer['client_full_name'],
            'skype' => (string) $prom_customer['skype'],
            'prom_id' => $prom_customer['id'],
            'comment' => (string) $prom_customer['comment'],
            'count_orders' => intval($prom_customer['orders_count']),
            'total_price' => $total_payout,
            'first_order' => $date_str,
            'last_order' => $date_str,
          ));
          foreach ($prom_customer['phones'] as $phone) {
            Phone::firstOrCreate(array(
              'customer_id' => $customer->id,
              'phone' => '+'.$phone,
            ));
          }
          foreach ($prom_customer['emails'] as $email) {
            Email::firstOrCreate(array(
              'customer_id' => $customer->id,
              'email' => $email,
            ));
          }
        }
      }
      if ($customer) {
        $customer->load('emails')->load('phones')->load(['orders' => function ($q) { $q->orderBy('prom_date_created', 'desc'); }]);
      }
      $messages = Message::where('phone', $message->phone)->get();
      $message->load('replies');

      return array('message' => $message, 'customer' => $customer, 'messages' => $messages);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        //
    }

    public function sendMessage(Request $request)
    {
      $inputs = $request->all();
      if (!isset($inputs['id'])) return;
      $api = new PromApi;
      return $api->sendMessage($inputs['id'], $inputs['message']);
    }
}
