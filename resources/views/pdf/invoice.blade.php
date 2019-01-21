<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
  body {
    font-size: 10px;
    padding: 40px;
  }
  .head-table td{
    padding: 8px 0;
  }
  h3 {
    display: block;
    text-align: center;
  }
h4 {
font-size: 12px;
}
  .products-table {
    border-collapse: collapse;
  }
  .products-table th{
    text-align: center;
    padding: 4px;
    font-weight: normal;
    border: 1px solid gray;
  }
  .products-table td{
    padding: 0 4px;
    border: 1px solid gray;
  }
</style>

</head>
<body>
<table class="head-table">
    <tr>
        <td>
            <strong>Поставщик</strong>
        </td>
        <td>
            Арт-студия Helgamade, 0661805460
        </td>
    </tr>
    <tr>
        <td>
            <strong>Заказчик:</strong>
        </td>
        <td>
            {{$data['customer']}}
        </td>
    </tr>
    <tr>
        <td>
            <strong>Плательщик:</strong>
        </td>
        <td>
            тот же
        </td>
    </tr>
</table>
<h3>ЗАКАЗ № {{$data['order_id']}} от {{$data['date']}}</h3>

<table class="products-table">
    <tr>
        <th>№</th><th>Артикул</th><th>Наименование товара</th> <th>Кол.</th><th>Цена</th><th>Сумма</th>
    </tr>
    @foreach($data['products'] as $index => $product)
    <tr>
        <td style="text-align: right;">
          {{$index + 1}}
        </td>
        <td>
          {{$product->sku}}
        </td>
        <td>
          {{$product->name}}
        </td>
        <td style="text-align: center;">
          {{$product->quantity}}
        </td>
        <td style="text-align: right;">
          {{$product->prom_price}}
        </td>
        <td style="text-align: right;">
          {{$product->prom_price * $product->quantity}}
        </td>
    </tr>
    @endforeach
    <tr>
      <td style="border: none;" colspan="3"></td>
      <td><strong>{{$data['sums']['quantity']}}</strong></td>
      <td style="border: none; text-align: right;" ><strong>Итого:</strong></td>
      <td><strong>{{$data['sums']['price']}}</strong></td>
    </tr>
</table>

<h4>Итого к оплате: {{$data['sums']['text']}}</h4>
</body>
</html>
