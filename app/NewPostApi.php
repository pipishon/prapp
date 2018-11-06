<?php

namespace App;

class NewPostApi {
    private $key = 'b2aa728b253bc10bbb33e79c30d6498d';
    private $language = 'en';

	private function request($model, $method, $params = NULL) {
		$url = 'https://api.novaposhta.ua/v2.0/json/';

		$data = array(
			'apiKey' => $this->key,
			'modelName' => $model,
			'calledMethod' => $method,
			'language' => $this->language,
			'methodProperties' => $params
		);
		$post = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_PROXY, '10.0.0.80:3128');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        curl_close($ch);
		return json_decode($result, true);
	}

    public function getCities ()
    {
        $model = 'Address';
        $method = 'getCities';
        $params = array(
            'Page'=> '1',
            'Language'=> 'ru'
        );
        return $this->request($model, $method, $params);
    }

    public function getWarehousesTypes ()
    {
        $model = 'AddressGeneral';
        $method = 'getWarehouseTypes';
        $params = array(
            'Page'=> '1',
        );
        return $this->request($model, $method, $params);
    }

    public function getWarehouses ()
    {
        $model = 'AddressGeneral';
        $method = 'getWarehouses';
        $params = array(
            'Page'=> '1',
        );
        return $this->request($model, $method, $params);
    }

    public function getCounterpartyContactPersons ()
    {
        $model = 'Counterparty';
        $method = 'getCounterpartyContactPersons';
        $params = array(
            'Ref' => '1739e963-54fd-11e4-acce-0050568002cf',
            'Page'=> '1'
        );
        return $this->request($model, $method, $params);
    }

    public function  searchSettlementStreets ()
    {
        $model = 'Address';
        $method = 'searchSettlementStreets';
        $params = array(
            'StreetName' => "Незалежност",
            'SettlementRef'=> 'e715719e-4b33-11e4-ab6d-005056801329',
            'limit'=> '5'
        );
        return $this->request($model, $method, $params);
    }


    public function getTtn ($data)
    {
        $model = 'InternetDocument';
        /*if (isset($data['ref'])) {
          $method = 'update';
        } else {*/
          $method = 'save';
        //}

        $params = array (
          "NewAddress"=>"1",
          "PayerType"=> $data['payer'],
          "PaymentMethod"=>"Cash",
          "CargoType"=>"Parcel",
          "VolumeGeneral"=> $data['volume_general'] / 250,
          "Weight"=> $data['weight'],
          "ServiceType"=>"WarehouseWarehouse",
          "SeatsAmount"=> $data['places'],
          "Description"=>"Заказ Prom.ua",
          "Cost"=> $data['price'],
          "CitySender"=>"8d5a980d-391c-11dd-90d9-001a92567626",
          "Sender"=>"99584bde-388c-11e6-a54a-005056801333",
          "SenderAddress"=>"a30964e3-cbc5-11e4-a77a-005056887b8d",
          "ContactSender"=>"9994e5ab-388c-11e6-a54a-005056801333",
          "SendersPhone"=>"380679325925",
          "RecipientCityName"=> $data['city'],
          "RecipientArea"=>"",
          "RecipientAreaRegions"=>"",
          "RecipientAddress" => $data['warehouse'],
          "RecipientHouse"=>"",
          "RecipientFlat"=>"",
          "RecipientName"=> $data['name'],
          "RecipientType"=>"PrivatePerson",
          "RecipientsPhone"=> $data['phone'],
          "DateTime"=> $data['date'],
        );

        if ($data['backdelivery'] == '1') {
           $params["BackwardDeliveryData"] = array(array(
                "PayerType"=> $data['backpayer'],
                "CargoType"=> "Money",
                "RedeliveryString"=> $data['backprice'],
           ));
        }

        /*if (isset($data['ref'])) {
          $params['Ref'] = $data['ref'];
        }*/
        //return $params;

        return $this->request($model, $method, $params);
    }

    public function getSender ()
    {
        $model = 'Counterparty';
        $method = 'getCounterparties';
        $params = array(
            'CounterpartyProperty' => 'Sender',
            'Page'=> '1'
        );
        return $this->request($model, $method, $params);
    }

    public function track ($ttns)
    {
        $en_nums = $ttns;
        $documents = array();
        foreach ($en_nums as $num) {
            $documents[] = array(
                    "DocumentNumber" => $num,
                    "Phone" => "+380679325925"
                );
        }
        $model = 'TrackingDocument';
        $method = 'getStatusDocuments';
        $params = array(
            "Documents" => $documents
        );
        return $this->request($model, $method, $params);
    }
}
