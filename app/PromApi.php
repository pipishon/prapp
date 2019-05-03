<?php

namespace App;
use App\Replies;

class PromApi {
    public $token = '604adbd051c60e348b7d10122221756f4c92a89b';
    public $host = 'my.prom.ua';

    function make_request($method, $url, $body) {
        $headers = array (
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $this->host . $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_PROXY, '10.0.0.80:3128');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if (strtoupper($method) == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * Получить список
     * @param string $type Тип списка. Возможны значения 'products', 'clients', 'orders', 'groups'
     * @param array $params Массив параметров.
     * @return array
     */

    function getList($type = '', $params = NULL) {
        if (!in_array($type, ['products', 'clients', 'orders', 'groups', 'messages'])) {
          return NULL;
        }
        $url = '/api/v1/'.$type.'/list';
        if ( !is_null($params) && is_array($params) ) {
          $url .= '?'.http_build_query($params);
        }
        $method = 'GET';
        $response = $this->make_request($method, $url, NULL);
        return $response;
    }

    /**
     * Получить элемент
     * @param string $type Тип списка. Возможны значения 'products', 'clients', 'orders', 'groups'
     * @param array $params Массив параметров.
     * @return array
     */

    function getItem($id = 0, $type = '', $params = NULL) {
        if (is_array($id) || $id == 0 || intval($id) == 0) {
            return NULL;
        }
        if (!in_array($type, ['products', 'clients', 'orders', 'groups', 'messages'])) {
            return NULL;
        }
        $url = '/api/v1/'.$type.'/'.$id;
        $method = 'GET';
        $response = $this->make_request($method, $url, NULL);
        return $response;
    }

    function getCustomerByPhone ($phone = '') {
      $params = array('search_term' => $phone);
      $customers = $this->getList('clients', $params)['clients'];

      return (count($customers) > 0) ? $customers[0] : array();
    }

    function getMessages ($last_id = null) {
      $params = array('limit' => 20);
      /*if ($last_id != null) {
        $params['last_id'] = $last_id;
      }*/
      return $this->getList('messages', $params)['messages'];
    }

    function setMessageStatus ($id = null, $status = '') {
      if ($id == null) return;
      $url = '/api/v1/messages/set_status';
      $method = 'POST';
      $params = array(
        'id' => array((int) $id),
        'status' => $status
      );

      $response = $this->make_request($method, $url, $params);//'setMessageStatus';//$this->make_request($method, $url, $params);
      return array('response' => $response, 'params' => $params);
    }


    function sendMessage ($id = null, $message = '') {
      if ($id == null) return;
      $url = '/api/v1/messages/reply';
      $method = 'POST';

      $params = array(
        'id' => (int) $id,
        'message' => $message
      );

      $replies = Replies::create(array(
        'message_id' => $id,
        'message' => $message
      ));

      $response = $this->make_request($method, $url, $params);//'sendMessage';//$this->make_request($method, $url, $params);
      return array('response' => $response, 'params' => $params);
    }

    function setOrderStatus($id, $status, $cancellation_reason = NULL, $cancellation_text = NULL)
    {
      $url = '/api/v1/orders/set_status';
      $method = 'POST';
      $body = array (
           'status'=> $status,
           'ids'=> [(int) $id]
      );
      if ( $status === 'canceled' ) {
        $body['cancellation_reason'] = $cancellation_reason;
        if ($cancellation_text != NULL) {
          $body['cancellation_text'] = $cancellation_text;
        }
      }

      $response = $this->make_request($method, $url, $body);
      return $response;
    }

/*
    function getAllProducts () {
        $groups = $this->getList('groups')['groups'];
        sleep(10);
        $products = array();
        foreach($groups as $group) {
          $group_id = $group['id'];
          $last_id = 0;
          $i = 0;
          do  {
            $params = array( 'group_id' => $group_id, 'limit' => 100);
            if ($last_id != 0) {
              $params['last_id'] = $last_id;
            }
            $prom_prods = $this->getList('products', $params)['products'];
            sleep(10);
            $last_id = end($prom_prods)['id'];
            $products = array_merge($products, $prom_prods);
            $i++;
          } while (!empty($prom_prods) || $i < 10);
        }
        return $products;
    }
 */

    function getOpenOrders () {
      $i = 0;
      $last_id = 0;
      $statuses = ['pending', 'received'];
      $orders = array();
      foreach ($statuses as $status) {
        do  {
          $params = array( 'status' => $status, 'limit' => 100);
          if ($last_id != 0) {
            $params['last_id'] = $last_id;
          }
          $prom_orders = $this->getList('orders', $params)['orders'];
          if (count($prom_orders) == 100) {
            sleep(10);
          }
          $last_id = end($prom_orders)['id'];
          $orders = array_merge($orders, $prom_orders);
          $i++;
        } while (!empty($prom_orders) || $i < 10);
      }
      return $orders;
    }
}
