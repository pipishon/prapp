<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Order;

class Sms extends Model
{
	protected $guarded = [];

  public $writer = null;
    //
  public function getAuth() {
    $login = 380679325925;
    $password = 'crmhm';
    $this->writer = new \XmlWriter();
    $this->writer->openMemory();
    $this->writer->startDocument('1.0', 'utf-8');
    $this->writer->startElement('request');
    $this->writer->startElement('auth');
      $this->writer->startElement('login');
      $this->writer->text($login);
      $this->writer->endElement();
      $this->writer->startElement('password');
      $this->writer->text($password);
      $this->writer->endElement();
    $this->writer->endElement();
  }

  public function sendSms()
  {
    $this->getAuth();
    $this->writer->startElement('message');
      $this->writer->startElement('from');
      $this->writer->text('Helgamade');
      $this->writer->endElement();
      $this->writer->startElement('text');
      $this->writer->text($this->message);
      $this->writer->endElement();
      $this->writer->startElement('recipient');
      $this->writer->text($this->phone);
      $this->writer->endElement();
    $this->writer->endElement();
    $this->writer->endElement();
    $result = $this->sendRequest($this->writer->outputMemory());
    if (!isset($result['name'])) die('Sms service error');
    if ($result['name'] == 'Complete') {
      $this->api_id = $result['sms_id'];
    } else {
      $this->status = $result['description'];
    }
    return $this->save();
  }


  public function checkSmsStatus ()
  {
    $this->getAuth();
    $this->writer->startElement('sms_id');
    $this->writer->text($this->api_id);
    $this->writer->endElement();
    $this->writer->endElement();
    $result = $this->sendRequest($this->writer->outputMemory());
    $this->status = $result['description'];
    $this->save();
  }

  public function sendRequest($xml)
  {
    $sUrl  = 'https://letsads.com/api';
    $rCurl = curl_init($sUrl);
    curl_setopt($rCurl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($rCurl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($rCurl, CURLOPT_HEADER, 0);
    curl_setopt($rCurl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($rCurl, CURLOPT_POST, 1);
    $sAnswer = curl_exec($rCurl);
    curl_close($rCurl);
    $xml = simplexml_load_string($sAnswer, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_encode($xml);
    $array = json_decode($json, TRUE);
    return $sAnswer;
  }
}
