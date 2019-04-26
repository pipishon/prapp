<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
  body {
    font-size: 11px;
    padding: 10px 15px 30px 15px;
    color: black;
    line-height: 1;
  }
  .head-table td{
    padding: 8px 0;
	font-size: 12px;
	 }
  h3 {
	font-size: 16px;
    display: block;
    text-align: center;
	color: black;
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
	height: 30;
	font-weight: normal;
    border: 1px solid black;
	border-width: thin;
  }
  .products-table td{
    padding: 0 4px;
    border: 1px solid black;
	border-width: thin;
  }
</style>

</head>
<body>
@foreach ($pdfs_data as $data)
<div style="page-break-inside: avoid; @if ($data['second']) transform: rotate(180deg); margin-top: 40px; @endif">
    <table class="head-table">
        <tr>
            <td>
                <strong>Поставщик:</strong>
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

    <table width="510" class="products-table">
        <tr>
            <th width="15">№</th><th width="60">Артикул</th><th>Наименование товара</th> <th width="25">Кол.</th>
            @if ($data['with_discount'])
                <th width="30">Цена</th>
                <th width="25">Скидка, %</th>
                <th width="30">Цена со скидкой</th>
                <th width="35">Сумма со скидкой</th>
            @else
                <th width="30">Цена</th>
                <th width="35">Сумма</th>
            @endif
        </tr>
        @foreach($data['products'] as $index => $product)
        <tr>
            <td style="text-align: right;">
              {{$loop->iteration}}
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
            @if ($data['with_discount'])
                <td style="text-align: right;">
                  {{number_format($product->pdf_price, 2)}}
                </td>
                <td style="text-align: right;">
                  {{number_format($product->discount, 2)}}
                </td>
                <td style="text-align: right;">
                  {{number_format(($product->pdf_price - $product->pdf_price * $product->discount / 100), 2)}}
                </td>
                <td style="text-align: right;">
                  {{number_format(($product->pdf_price - $product->pdf_price * $product->discount / 100) * $product->quantity, 2)}}
                </td>
            @else
                <td style="text-align: right;">
                  {{number_format(($product->pdf_price - $product->pdf_price * $product->discount / 100), 2)}}
                </td>
                <td style="text-align: right;">
                  {{number_format(($product->pdf_price - $product->pdf_price * $product->discount / 100) * $product->quantity, 2)}}
                </td>
            @endif
        </tr>
        @endforeach
        <tr>
          <td style="border: none;" colspan="3"></td>
          <td style="text-align:center; font-size: 12px;"><strong>{{$data['sums']['quantity']}}</strong></td>
          @if ($data['with_discount'])
          <td style="border: none; text-align: right;" colspan="3"><strong>Итого:</strong></td>
          @else
          <td style="border: none; text-align: right;" ><strong>Итого:</strong></td>
          @endif
          <td style="text-align:right; font-size: 12px;"><strong>{{number_format($data['sums']['price'], 2)}}</strong></td>
        </tr>
    </table>

    <h4>Итого к оплате: {{$data['sums']['text']}}</h4>
</div>
@endforeach
</body>
</html>
