<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PrivatPayment extends Model
{
	protected $guarded = [];

    public static function getFromApi ()
    {
        $today = Carbon::now()->format('d.m.Y');
        $url = 'https://api.privatbank.ua/p24api/rest_fiz';
        $data = '<oper>cmt</oper><wait>0</wait><test>0</test><payment id=""><prop name="sd" value="'.$today.'" /><prop name="ed" value="'.$today.'" /><prop name="card" value="5168742221438645" /></payment>';
        $password = '5Z3Ni6j21Vx8QRPyN79UbzQp4uz15EpP';
        $signature = sha1(md5($data.$password));
        $xml = '<?xml version="1.0" encoding="UTF-8"?><request version="1.0"><merchant><id>147889</id><signature>'.$signature.'</signature></merchant><data>'.$data.'</data></request>';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        $xml = simplexml_load_string($data);
        foreach ($xml->data->info->statements->statement as $statement) {
            $row = array();
            foreach ($statement->attributes() as $name => $val) {
                $name = (string) $name;
                switch ($name) {
                    case 'appcode':
                        $row[$name] = (int) $val;
                        break;
                    case 'trandate':
                        $row[$name] = (string) $val;
                        break;
                    case 'trantime':
                        $row[$name] = (string) $val;;
                        break;
                    case 'amount':
                    case 'cardamount':
                        $row[$name] = floatval($val);
                        break;
                    case 'rest':
                        $row[$name] = floatval($val);
                        break;
                    case 'terminal':
                    case 'description':
                        $row[$name] = (string) $val;
                        break;
                    default:
                        break;
                }
            }
            $row['trandate'] = Carbon::parse( $row['trandate'].' '.$row['trantime'] );
            unset($row['trantime']);
            self::firstOrCreate(array('appcode' => $row['appcode']), $row);
        }
    }

}
