<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SputnikEmail;
use App\Order;

class SputnikEmailController extends Controller
{
  public function sendMessage(Request $request)
  {
      //"requestId":"b56fb574-36d0-4798-9e1c-ba5e36dd0b84"
      $params = array(
        'from' => '"Александр с Helgamade" <aleksandr@helgamade.com.ua>', // отправитель в формате "Имя" <email@mail.com>
        'subject' => 'Тестовая тема',
        'htmlText' => 'ТЕСТ!',
        'plainText' => 'Простой текст сообщения', // вариант письма в виде простого текста
        'emails' => array('abyalonovich@gmail.com'),
      );
      //SputnikEmail::sendRequest('post', 'message/email', $params);
      $params = array (
          'ids' => 'b56fb574-36d0-4798-9e1c-ba5e36dd0b84'
      );
      SputnikEmail::sendRequest('get', 'message/status', $params);

  }

}
