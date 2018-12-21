<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OrderDayStatistic;
use App\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class OrderDayStatisticController extends Controller
{
    public function calcToday (Request $request)
    {
        $this->calcStatisticForDay(Carbon::today(), true);
        return;
    }

    public function calcMonth (Request $request)
    {
        $end = Carbon::today();
        $start = Carbon::today();
        $start->subMonth();
        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            $this->calcStatisticForDay($date);
        }
        return;
    }

    public function calcStatisticForDay ($date = null, $debug = false)
    {
        if ($date == null) {
            $date = Carbon::today();
        }
        $orders_pending = Order::whereDate('prom_date_created', $date)->where('status', '<>', 'canceled')->get();
        $orders_count = Order::whereDate('prom_date_created', $date)->where('status', '<>', 'canceled')->count();
        $orders_sum = Order::whereDate('prom_date_created', $date)->where('status', '<>', 'canceled')->join('order_statuses', 'order_statuses.order_id', 'orders.id')->sum('order_statuses.payment_price');
        $orders_delivered_sum = Order::whereDate('order_statuses.delivered', $date)->where('status', 'delivered')->select('orders.*')->join('order_statuses', 'order_statuses.order_id', 'orders.id')->sum('order_statuses.payment_price');
        $orders_delivered_count = Order::whereDate('order_statuses.delivered', $date)->where('status', 'delivered')->select('orders.*')->join('order_statuses', 'order_statuses.order_id', 'orders.id')->count();
        $orders_delivered = Order::join('order_statuses', 'order_statuses.order_id', 'orders.id')->select('orders.*')->whereDate('order_statuses.delivered', $date)->where('status', 'delivered')->with('products')->get();
        $purchase_sum = 0;
        if ($debug) {
            echo '<strong>Принятые</strong>';
            echo '<table style="text-align: center;">';
            echo '<tr>';
                echo '<th>id</th>';
                echo '<th>К оплате</th>';
            echo '</tr>';
            foreach ($orders_pending as $order) {
                echo '<tr>';
                echo '<td>'.$order->prom_id.'</td>';
                echo '<td>'.$order->statuses->payment_price.'</td>';
                echo '</tr>';
            }
            echo '</table>';

        echo '<strong>Выполненные</strong>';
        echo '<table style="text-align: center;">';
        echo '<tr>';
            echo '<th>id</th>';
            echo '<th>К оплате</th>';
            echo '<th>Прибыль</th>';
        echo '</tr>';
        }
        foreach ($orders_delivered as $order) {
            $sum = 0;
            if ($debug) {
                echo '<tr>';
                echo '<td>'.$order->prom_id.'</td>';
                echo '<td>'.$order->statuses->payment_price.'</td>';
            }
            foreach ($order->products as $product) {
                $sum += $product->purchase * $product->quantity;
            }
            if ($debug) {
                echo '<td>'.($order->statuses->payment_price - $sum).'</td>';
                echo '</tr>';
            }
            $purchase_sum += $sum;
        }
        if ($debug) {
            echo '</table>';
        }
        $earn = $orders_delivered_sum - $purchase_sum;

        $margin = ($orders_delivered_sum != 0) ? $earn * 100 / $orders_delivered_sum : 0;
        OrderDayStatistic::updateOrCreate(array('date'=> $date), array(
            'quantity' => $orders_count,
            'quantity_delivered' => $orders_delivered_count,
            'price_pending' => round($orders_sum),
            'price_delivered' => round($orders_delivered_sum),
            'earn_delivered' => round($earn),
            'margin_delivered' => round($margin)
        ));

        if ($debug) {
            echo '<div><strong>Итого</strong></div>';
            echo 'Кол-во:'.$orders_count.'шт. Сумма за день:'. round($orders_sum) . ' грн. Кол-во выполненных:'.$orders_delivered_count.' Сумма выполненых:'. round($orders_delivered_sum). ' грн. Заработано:' . round($earn) .'грн Маржа:' . round($margin) .'%';
        }
    }

    public function recalcStatistics (Request $request)
    {
        $last_date = Carbon::parse('2018-10-31');
        $start = Carbon::parse('2014-09-30');
        $date = ($request->has('day')) ? Carbon::parse($request->input('day')) : $start ;
        $next_date = Carbon::parse($date->toDateString());
        $next_date->addMonth();
        $period = CarbonPeriod::create($date, $next_date);
        $avr = array();
        foreach ($period as $date) {
            $avr[$date->toDateString()] = array();
            for ($i = 2; $i < 5; $i++) {
                $where_raw = $i.' = (SELECT COUNT( DISTINCT id)
                    FROM orders u
                    WHERE k.prom_date_created >= u.prom_date_created
                    AND k.customer_id = u.customer_id GROUP BY u.customer_id)';
                $avr[$date->toDateString()][$i] = DB::table('orders as k')
                    ->join('order_statuses as st', 'k.id', '=', 'st.id')
                    ->select('days_prev_order')
                    ->whereDate('prom_date_created', '<=', $date)
                    ->whereRaw($where_raw)->avg('days_prev_order');
            }
            OrderDayStatistic::updateOrCreate(array('date'=> $date), array(
                'second' => $avr[$date->toDateString()][2],
                'third' => $avr[$date->toDateString()][3],
                'fourth' => $avr[$date->toDateString()][4]
            ));
        }

        $next_date = $date->addDay();
        $result = array(
            'day' => ($next_date->lessThan($last_date)) ? $next_date->toDateString() : false,
            'avr' => $avr,
        );
        return $result;
    }

    public function index (Request $request)
    {
        $this->calcStatisticForDay();
        if ($request->has('from')) {
            return OrderDayStatistic::whereDate('date', '>=', $request->input('from'))->whereDate('date', '<=', $request->input('to'))->orderBy('date', 'asc')->get();
        }
        return OrderDayStatistic::all();
    }
    //
}
