<?php

namespace App\Http\Controllers;

use App\Order;
use App\PromApi;
use App\Sms;
use App\Settings;
use App\MessageTemplate;
use App\NewPostTtnTrack;
use App\Dictionary;
use App\SputnikEmail;
use App\MessageEmail;
use Carbon\Carbon;
use App\NewPostCity;
use App\NewPostApi;
use App\NewPostTtn;
use Illuminate\Http\Request;
use NumberToWords\NumberToWords;
use App\DiscountProduct;

class MassActionController extends Controller
{
  public function statusDelivered (Request $request)
  {
    $ids = $request->input('ids');
    $api = new PromApi;
    $orders = Order::whereIn('id', $ids)->get();
    $responce = array();
    foreach ($orders as $order) {
      $order->status = 'delivered';
      $order->statuses->delivered = Carbon::now();
      $order->push();
      $responce[] = $api->setOrderStatus($order->prom_id, 'delivered');
    }
    return $responce;
    //return '';
  }

  public function sendTtn (Request $request)
  {
    $ids = $request->input('ids');
    $orders = Order::whereIn('id', $ids)->get();
    $result = array('phone' => array(), 'email' => array());
    foreach ($orders as $order) {
        if ($order->statuses->ttn_string != '' && $order->validet['statuses']['Клиент'] == '1') {
            $sms = Sms::firstOrCreate(array('order_id' => $order->prom_id, 'type' => 'ttn'));
            $sms->status = null;
            $template_id = Settings::where('name', 'template_ttn_sms')->first()->value;
            $template = MessageTemplate::find($template_id)->template;
            $message = str_replace('$ttn$', $order->statuses->ttn_string, $template);
            $phone = ($order->statuses->custom_phone) ?  $order->statuses->custom_phone : $order->phone;
            $sms->message = $message;
            $sms->phone = $phone;
            $result['phone'][] = $order->id;
            $order->statuses->ttn_status = '1';
            $order->statuses->ttn_phone = '1';
            $sms->sendSms();
            $email = ($order->statuses->custom_email) ?  $order->statuses->custom_email : $order->email;
            if ($email) {
                $order_id = $order->prom_id;
                $type = 'ttn';
                $sputnik_email = SputnikEmail::where('email', $email)->first();
                if (!$sputnik_email) {
                    $sputnik_email = SputnikEmail::create(['email' => $email]);
                    $sputnik_email->subscribe();
                }
                $message_email = MessageEmail::create(array(
                    'email' => $email,
                    'order_id' => $order_id,
                    'type' => 'ttn',
                    'send_at' => Carbon::now()
                ));
                //$ukr_post_str = Dictionary::where('to', 'Укрпочта')->first()->from;
                $trigger = ($order->delivery_option == 'Укрпочта') ? 'api-send-ttn-ukrpost' : 'api-send-ttn-newpost';
                $params = array(
                    'ttn' => $order->statuses->ttn_string,
                    'invoice' = $this->getPdfLink($order_id)
                );
                $result['email'][] = $order->id;
                $sputnik_email->sendEvent($trigger, $params);
                $order->statuses->ttn_email = '1';
            }
            $order->push();
        }
    }
    return $result;
  }

    public function getPdfLink ($order_id)
    {
        $ivlen = openssl_cipher_iv_length('AES-128-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $iv = 'p/Ȅ����';
        return 'http://my.helgamade.com.ua/invoice?hash='.rawurlencode(openssl_encrypt($order_id, 'AES-128-CBC', 'sercet', 0, $iv));
    }

  public function createTtn (Request $request)
  {
    $ids = $request->input('ids');//Request $request
    $orders = Order::whereIn('id', $ids)->get();
    $result = array();
    foreach ($orders as $order) {
        if ($order->statuses->ttn_string == '' &&
            $order->validet['success'] == 'all' &&
            (int) $order->statuses->shipment_place == 1
        ) {
            $phone = ($order->statuses->custom_phone) ?  $order->statuses->custom_phone : $order->phone;
            $price = ($order->statuses->payment_price) ?  $order->statuses->payment_price : $order->price;
            $price = ceil(floatval($price));
            $data = array(
                'order_id' => $order->id,
                'payer' => 'Recipient',
                'volume_general' => '0.1',
                'phone' => $phone,
                'price' => $price,
                'backpayer' => 'Recipient',
                'backprice' => $price,
                'date' => Carbon::now()->format('Y-m-d'),
            );
            $data['backdelivery'] = ($order->statuses->payment_status == 'Наложенный') ? '1': '0';
            $data['weight'] = floatval(str_replace(',', '.', $order->statuses->shipment_weight));

            $new_post_ttn = NewPostTtn::where('order_id', $order->id)->first();
            if ($new_post_ttn == null) {
                $new_post_address = NewPostCity::isAddressValid($order->delivery_address);
                $new_post_city = NewPostCity::where('description', '=', $new_post_address['city'])->first();
                $new_post_warehouse = $new_post_city->warehouses()->where('description', $new_post_address['warehouse'])->first();
                $data['city'] = $new_post_address['city'];
                $data['full_address'] = $order->delivery_address;
                $data['warehouse'] = $new_post_warehouse->ref;
                $data['name'] = $order->client_last_name.' '.$order->client_first_name;
                $new_post_ttn = NewPostTtn::firstOrNew($data);
            } else {
                $data['city'] = $new_post_ttn->city;
                $data['full_address'] = $new_post_ttn->full_address;
                $data['warehouse'] = $new_post_ttn->warehouse;
                $data['name'] = $new_post_ttn->name;
                $data['phone'] = $new_post_ttn->phone;
            }
            $new_post_ttn->update($data);
            $data['date'] = Carbon::now()->format('d.m.Y');
            $data['places'] = 1;
            $np = new NewPostApi();
            $responce = $np->getTtn($data);
            if ((bool) $responce['success'] == true) {
                $np_data = $responce['data'][0];
                $new_post_ttn->int_doc_number = $np_data['IntDocNumber'];
                $new_post_ttn->estimated_delivery_date = Carbon::parse($np_data['EstimatedDeliveryDate']);
                $new_post_ttn->cost_on_site = $np_data['CostOnSite'];
                $new_post_ttn->ref = $np_data['Ref'];
                $new_post_ttn->save();
                $order->statuses->ttn_string = $np_data['IntDocNumber'];
                $order->push();
                NewPostTtnTrack::updateOrCreate(array('order_id' => $order->id),
                    array(
                        'customer_id' => $order->customer->id,
                        'prom_id' => $order->prom_id,
                        'ref' => $np_data['Ref'],
                        'int_doc_number' => $np_data['IntDocNumber'],
                        'estimate_delivery_date' => $new_post_ttn->estimated_delivery_date,
                        'status' => '',
                        'status_code' => 0,
                        'redelivery' => $new_post_ttn->backdelivery,
                        'redelivery_sum' => $new_post_ttn->backprice,
                        'phone' => $new_post_ttn->phone,
                        'full_name' => $new_post_ttn->name,
                        'city' => $new_post_ttn->city,
                        'warehouse' => '',
                        'warehouse_ref' => $new_post_ttn->warehouse,
                        'recipient_address' => $new_post_ttn->full_address,
                        'date_created' => $new_post_ttn->date,
                        'date_first_day_storage' => null,
                        'document_weight' => $new_post_ttn->weight,
                        'check_weight' => 0,
                        'document_cost' => $new_post_ttn->cost_on_site,
                    )
                );
                $result[$order->id] = $new_post_ttn;
            }
        }
    }
    return $result;
  }

  public function createPdf (Request $request)
  {
      $ids = $request->input('ids');
      $orders = Order::whereIn('id', $ids)->whereIn('id', function ($query) use ($ids) {
          $query->from('order_statuses')->select('order_id')->where('bill', 0)->whereIn('order_id', $ids);
      });
      if ($request->has('bill_required')) {
          $orders =  $orders->whereIn('customer_id', function($query) {
              $query->from('customers')->select('id')->whereNotNull('bill_required');
          });
      }
      $orders = $orders->get();
      $pdfs_data = array();
      foreach ($orders as $order) {
        $order->statuses->bill = 1;
        $order->push();
        $order->products = $order->products->sortBy(function($product) {
            $hash = ($product->sort1) ? $product->sort1 : 0;
            return $hash.$product->name;
        });
      //$with_discount = $request->input('with_discount');
        $sums = array(
            'quantity' => 0,
            'price' => 0
        );
        $with_discount = false;
          foreach ($order->products as $product) {
            $product->pdf_price = ($product->order_price != null) ? $product->order_price : $product->price;
            $sums['quantity'] += $product->quantity;
            $sums['price'] += $product->quantity * ($product->pdf_price - $product->pdf_price * $product->discount / 100);
            if ($product->discount) {
              $with_discount = true;
            }
          }
          $numberToWords = new NumberToWords();
          $currencyTransformer = $numberToWords->getCurrencyTransformer('ru');
          $title = $currencyTransformer->toWords($sums['price']*100, 'UAH');
          $sums['text'] =  mb_strtoupper(mb_substr($title, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($title, 1, null,'UTF-8');
          $data = array(
            'customer' => $order->client_first_name.' '.$order->client_last_name,
            'order_id' => $order->prom_id,
            'date' => Carbon::parse($order->prom_date_created)->format('d.m.Y'),
            'products' => $order->products,
            'sums' => $sums,
            'with_discount' => $with_discount,
            'second' => false,
            'margin' => 0,
          );
          $pdf = \PDF::loadView('pdf.invoice', array('data' => $data));
          $pdf->setPaper(array(0, 0, 595.28, 841.89));
          $GLOBALS['height'] = 0;
          $dompdf = $pdf->getDomPDF();

          $dompdf->setCallbacks(
              array(
                'myCallbacks' => array(
                  'event' => 'end_frame', 'f' => function ($infos) {
                    $frame = $infos["frame"];
                    if (strtolower($frame->get_node()->nodeName) === "body") {
                        $padding_box = $frame->get_padding_box();
                        $GLOBALS['height'] += $padding_box['h'];
                    }
                  }
                )
              )
            );
          $output = $pdf->output();
          $data['height'] = $GLOBALS['height'];
          $pdfs_data[] = $data;
      }

      usort($pdfs_data, function ($a, $b) {
          return $a['height'] < $b['height'];
      });
      $sorted_pdfs_data = array();
      $last = count($pdfs_data) - 1;
      foreach ($pdfs_data as $key => $row) {
          if ($key > $last) continue;
          if ($row['height'] + $pdfs_data[$last]['height'] > 841.8897637795 || $key == $last) {
              $sorted_pdfs_data[] = $row;
          } else {
              $sorted_pdfs_data[] = $row;
              $pdfs_data[$last]['second'] = true;
              $pdfs_data[$last]['margin'] = ((841.8897637795 - ($row['height'] + $pdfs_data[$last]['height']))*1.333333) - 20;
              $sorted_pdfs_data[] = $pdfs_data[$last];
              $last = $last - 1;
          }
      }

      $pdf_all = \PDF::loadView('pdf.mass_invoice', array('pdfs_data' => $sorted_pdfs_data));
      $pdf_all->setPaper(array(0, 0, 595.2755905512, 841.8897637795));
      return $pdf_all->download('orders.pdf');
  }

  public function setDiscount (Request $request)
  {
      $discount_id = $request->input('discount');
      $ids = $request->input('ids');
      foreach ($ids as $id) {
          DiscountProduct::updateOrCreate(array('product_id' => $id), array('discount_id' => $discount_id));
      }
  }

  public function removeDiscount (Request $request)
  {
      $ids = $request->input('ids');
      DiscountProduct::whereIn('product_id', $ids)->delete();
  }


}


