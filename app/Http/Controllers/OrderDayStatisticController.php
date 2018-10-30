<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OrderDayStatistic;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class OrderDayStatisticController extends Controller
{
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

    public function index ()
    {
        return OrderDayStatistic::all();
    }
    //
}
