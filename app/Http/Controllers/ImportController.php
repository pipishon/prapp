<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Order;
use App\Product;
use App\OrderProduct;
use App\Customer;
use App\Phone;
use App\Email;
use Carbon\Carbon;

class ImportController extends Controller
{
    public function index ()
    {
      return view('import', array('uploaded_file_name' => '', 'lines'=>''));
    }

    public function date ($str, $format = false, $import = true) {
      if ($format) {
        return \DateTime::createFromFormat('d.m.y H:i', $str)->format('Y-m-d H:i');
      }
      $f = ($import) ?  'd.m.y H:i' : 'Y-m-d H:i';
      return \DateTime::createFromFormat($f, $str);
    }

    public function upload (Request $request)
    {
      $request->file('csvfile')->storeAs('csvfile', 'csvfile.csv');
      $filename = $request->file('csvfile')->getClientOriginalName();
      $linecount = 0;
      $handle = fopen($request->file('csvfile')->getRealPath(), "r");
      while(!feof($handle)){
        $line = fgets($handle);
        $linecount++;
      }
      fclose($handle);
      return view('import', array( 'uploaded_file_name' => $filename, 'lines' => $linecount - 1));
    }


    public function importproducts (Request $request)
    {
      $start_row = (int) $request->input('start_row');
      $path = storage_path('app/csvfile').'/csvfile.csv';

      $skip = 0;
      $i = 0;
      $imported_data = array();
      $imported_result = array(
         'product' => 0,
      );

      if(($handle = fopen($path, 'r')) !== false) {
          while(($data = fgets($handle)) !== false && $i < 200) {
            if ($skip <  $start_row) {$skip++; continue;}
            $data = explode(';', $data);

            /*$data = array(
                'ref' => $data[0],
                'name' => $data[1],
                'price' => $data[2],
                'units' => $data[3],
                'main_image' => $data[4],
                'available' => $data[5],
                'quantity' => $data[6],
                'group_id' => $data[7],
                'category' => $data[8],
                'prom_id' => $data[9],
                'subgroup_id' => $data[10],
                'link' => $data[11],
            );*/

            $image = trim(explode(',', $data[4])[0]);

            Product::updateOrCreate(array('sku' => $data[0]), array(
                'name' => $data[1],
                'price' => floatval(str_replace(',', '.', $data[2])),
                'units' => $data[3],
                'main_image' => $image,
                'min_balance' => intval($data[6]),
                'group_id' => $data[7],
                'category' => $data[8],
                'prom_id' => $data[9],
                'subgroup_id' => $data[10],
                'link' => trim($data[11]),
            ));


            //$imported_data = $this->processData($data, $order_id);
            //$order_id = $imported_data['order_id'];
            $imported_result['product']++;
            $i++;
          }
      }
      $result = array( 'end_row' => $start_row + $i, 'start_row' => $start_row);

      $result = array_merge($result, $imported_result);

      return $result;

    }

    public function import (Request $request)
    {
      $start_row = (int) $request->input('start_row');
      $order_id = ($request->input('order_id')) ? $request->input('order_id') : 0;
      $path = storage_path('app/csvfile').'/csvfile.csv';

      $skip = 0;
      $i = 0;
      $imported_data = array();
      $imported_result = array(
         'customer' => 0,
         'product' => 0,
         'phone' => 0,
         'email' => 0,
         'order' => 0,
      );

      if(($handle = fopen($path, 'r')) !== false) {
          while(($data = fgetcsv($handle)) !== false && $i < 200) {
            if ($skip <  $start_row) {$skip++; continue;}

            $imported_data = $this->processData($data, $order_id);
            $order_id = $imported_data['order_id'];
            foreach($imported_result as $key => $val) {
              $imported_result[$key] += $imported_data[$key];
            }
            $i++;
          }
      }
      $result = array( 'end_row' => $start_row + $i, 'start_row' => $start_row, 'order_id' =>  $order_id);

      $result = array_merge($result, $imported_result);

      return $result;
      /*
      $product = new Product;
      $product->name = $data[10];
      $product->sku = $data[9];
      $product->price = $data[13];
      $product->save();


       */
      return view('import', array('imported' => $total_imported));
    }

    private function processData($data, $order_id = 0)
    {
      $stats_map = array(
        'Отменен' => 'canceled',
        'Выполнен' => 'delivered',
        'Принят' => 'received'
      );
      $result = array(
         'customer' => 0,
         'product' => 0,
         'phone' => 0,
         'email' => 0,
         'order' => 0,
      );

        $data = array(
          'order_id' => $data[0],
          'date' => $data[1],
          'customer_name' => $data[2],
          'phone' => $data[3],
          'email' => $data[4],
          'address' => $data[5],
          'delivery' => $data[6],
          'payment' => $data[7],
          'status' => $data[8],
          'sku' => $data[9],
          'product_name' => $data[10],
          'quantity' => $data[11],
          'order_price' => $data[12],
          'product_price' => $data[13],
          'ttn' => $data[14],
          'price_discount' => $data[15]
        );

        $order = null;

        if (!Order::where('prom_id', $data['order_id'])->exists()) {
          $customer_id = 0;

          $phone = Phone::where('phone', $data['phone'])->first();
          if (!empty($phone)) {
            $customer_id = $phone->customer_id;
          }
          if ($customer_id == 0) {
            $email = Email::where('email', $data['email'])->first();
            if (!empty($email)) {
              $customer_id = $email->customer_id;
            }
          }

          if (is_null(Customer::where('id', $customer_id)->first())) $result['customer']++;
          $customer = Customer::firstOrNew(array('id' => $customer_id));
          $customer_name = trim($data['customer_name']);
          $customer_names = $customer->name;
          if (is_array($customer_names)) {
            if (!in_array($customer_name, $customer_names)) {
              $customer->name = $customer_name;
            }
          } else {
            $customer->name = $customer_name;
          }

          if (!isset($customer->first_order) || $this->date($data['date']) > $this->date($customer->first_order, false, true)) {
              $customer->first_order = $this->date($data['date'], true);
          }

          if (!isset($customer->last_order) || $this->date($data['date']) < $this->date($customer->last_order, false, true)) {
              $customer->last_order = $this->date($data['date'], true);
          }


          $customer->count_orders = $customer->count_orders + 1;
          $customer->total_price = $customer->total_price + (int) $data['order_price'];
          $customer->aver_price = (isset($customer->aver_price)) ? (int) ($customer->aver_price + (int) $data['order_price']) / 2 : (int) $data['order_price'];

          $customer->save();


          if ($data['phone'] != '') {
            if (!Phone::where('customer_id', '=', $customer->id)->exists()) $result['phone']++;
            $phone = Phone::firstOrCreate(array(
              'customer_id' => $customer->id,
              'phone' => $data['phone'],
            ));
          }

          if ($data['email'] != '') {
            if (!Email::where('customer_id', $customer->id)->exists()) $result['email']++;
            $email = Email::firstOrCreate(array(
              'customer_id' => $customer->id,
              'email' => $data['email'],
            ));
          }


          $price = ($data['price_discount'] == '') ? $data['order_price'] : $data['price_discount'];
          $date = $this->date($data['date'], true);
          if (!Order::where('prom_id', $data['order_id'])->exists()) $result['order']++;
          $order = Order::firstOrCreate(array('prom_id' => $data['order_id']),
            array(
              'prom_id' => $data['order_id'],
              'status' => $stats_map[$data['status']],
              'customer_id' => $customer->id,
              'delivery_option' => $data['delivery'],
              'delivery_address' => $data['address'],
              'payment_option' => $data['payment'],
              'price' => $price,
              'phone' => $data['phone'],
              'email' => $data['email'],
              'client_first_name' => $data['customer_name'],
              'client_notes' => '',
              'comment' => '',
              'prom_date_created' => $date
            ));
        }

        if (!Product::where('sku', $data['sku'])->exists()) $result['product']++;
        $product = Product::firstOrCreate(array('sku' => $data['sku']),
          array(
            'name' => $data['product_name'],
            'sku' => $data['sku'],
            'price' => $data['product_price'],
          ));

        if ($order == null) {
          $order = Order::where('prom_id', $data['order_id'])->first();
        }

        $order_id = $order->prom_id;
        $result['order_id'] = $order_id;

        $order_product = OrderProduct::firstOrCreate(array(
          'product_id' => $product->id,
          'order_id' => $order->id,
          'quantity' => (int) $data['quantity'],
        ));
        return $result;
    }
    //
}
