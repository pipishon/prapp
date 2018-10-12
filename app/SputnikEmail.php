<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SputnikEmail extends Model
{
	protected $guarded = [];

    public static function getActivity ()
    {
        $login = 'pitalov@gmail.com';
        $password = 'komandor';

        $params = array(
           'dateFrom' => Carbon::now('Europe/Kiev')->subMinute(60)->format('Y-m-d\TH:i:s'),
           'dateTo' => Carbon::now('Europe/Kiev')->format('Y-m-d\TH:i:s')
        );

        $ch = curl_init();
       // curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        $query = '?'.http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, 'https://esputnik.com/api/v2/contacts/activity'.$query);
        curl_setopt($ch,CURLOPT_USERPWD, $login.':'.$password);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        return json_decode($output, true);
    }

    public function sendRequest($type = 'get', $path = 'version', $params = array())
    {
        $login = 'pitalov@gmail.com';
        $password = 'komandor';

        $ch = curl_init();
       // curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        $query = '';
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            if (!empty($params)) {
                $query = '?'.http_build_query($params);
            }
        }
        curl_setopt($ch, CURLOPT_URL, 'https://esputnik.com/api/v1/'.$path.$query);
        curl_setopt($ch,CURLOPT_USERPWD, $login.':'.$password);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        return json_decode($output, true);
    }

    public function subscribe()
    {
        $contact = array(
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'channels' => array(
                array('type'=>'email', 'value' => $this->email),
            )
        );
        $params = array(
            'contact' => $contact,
            'groups' => 'Неподтвержденные контакты'
        );
        $result = $this->sendRequest('post', 'contact/subscribe', $params);
        if (isset($result['id'])) {
            $this->client_id = $result['id'];
            $this->save();
        }

    }

    public function sendEvent ($type, $params)
    {
        $params['email'] = $this->email;
        $arr = array();
        foreach ($params as $key => $val) {
            $arr[] = array(
                'name' => $key,
                'value' => $val
            );
        }
        $data = array(
            'eventTypeKey' => $type,
            'keyValue' => $this->email,
            'params' => $arr
        );
        $result = $this->sendRequest('post', 'event', $data);
    }

    //
}
