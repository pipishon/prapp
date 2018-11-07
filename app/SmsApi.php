<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsApi extends Model
{
	protected $guarded = [];

  public $writer = null;


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

  public function sendSms($phone, $message)
  {
    $this->getAuth();
    $this->writer->startElement('message');
      $this->writer->startElement('from');
      $this->writer->text('Helgamade');
      $this->writer->endElement();
      $this->writer->startElement('text');
      $this->writer->text($message);
      $this->writer->endElement();
      $this->writer->startElement('recipient');
      $this->writer->text($phone);
      $this->writer->endElement();
    $this->writer->endElement();
    $this->writer->endElement();
    return $this->sendRequest($this->writer->outputMemory());
  }


  public function getSmsStatus ($sms_id)
  {
    $this->getAuth();
    $this->writer->startElement('sms_id');
    $this->writer->text($sms_id);
    $this->writer->endElement();
    $this->writer->endElement();
    return $this->sendRequest($this->writer->outputMemory());
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
    // $sAnswer = curl_exec($rCurl);
    curl_close($rCurl);
    $xml = simplexml_load_string($sAnswer, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_encode($xml);
    $array = json_decode($json, TRUE);
    return $array;
  }
    //
}
