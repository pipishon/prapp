<html>
<body>
<table>
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
<h1>ЗАКАЗ № {{$data['order_id']}} от {{$data['date']}}</h1>

<table>
    <tr>
        <td>№</td> <td>Наименование товара</td> <td>Кол.</td><td>Кол.</td><td>Цена</td><td>Сумма</td>
    </tr>
    <tr>
        <td>
        </td>
    </tr>
</table>
</body>
</html>
