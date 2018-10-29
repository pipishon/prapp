<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OrderDayStatistic;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class OrderDayStatisticController extends Controller
{
    public function index ()
    {
        $period = CarbonPeriod::create('2015-01-01', '2015-01-25');
        $result = array();

        foreach($period as $date) {
            $where_raw = '2 = (SELECT COUNT( DISTINCT id)
                FROM orders u
                WHERE k.prom_date_created >= u.prom_date_created
                AND k.customer_id = u.customer_id GROUP BY u.customer_id)';
            $result[] = DB::table('orders as k')
                ->join('order_statuses as st', 'k.id', '=', 'st.id')
                ->select('days_prev_order')
                ->whereDate('prom_date_created', '>=', $date)
                ->whereRaw($where_raw)->avg('days_prev_order');
        }
        return $result;
        /*return DB::table('orders as k')->select(DB::raw($inner_select), DB::raw('MAX(prom_date_created), count(customer_id) as num'))->whereDate('prom_date_created', '<', $date)
            ->groupBy('customer_id')->havingRaw('num > 2')->get();*/
    }
    //
}
