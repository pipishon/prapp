<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Customer;
use App\Rfc;
use Carbon\Carbon;

class RfcController extends Controller
{
    public function index()
    {
        $this->updateAutoStatus();

        $auto_statuses = DB::table('customers')
            ->select('auto_status', DB::Raw('count(*) as qty'))
            ->groupBy('auto_status')->get()->pluck('qty', 'auto_status'); //$select->get();
        $mapStatuses = array(
            '' => 'хз',
            'new' => 'Новые',
            'perspective' => 'Перспективные',
            'suspended' => 'Подвисшие',
            'sleep' => 'Спящие',
            'one_time' => 'Одноразовые',
            'loyal' => 'Лояльные',
            'vip' => 'VIP',
            'risk' => 'В зоне риска',
            'lost' => 'Потери',
            'lost_vip' => 'Потери VIP',
        );
        $total = Customer::count();
        return array('statuses' => $auto_statuses, 'map' => $mapStatuses, 'total' => $total);
    }

    public function store()
    {
        $this->updateAutoStatus();
        $statuses = DB::table('customers')
            ->select('auto_status', DB::Raw('count(*) as qty'))
            ->groupBy('auto_status')->get()->pluck('qty', 'auto_status')->toArray(); //$select->get();
        unset($statuses['']);

        Rfc::updateOrCreate(array(
            'date' => Carbon::now()->format('Y-m-d')
        ), $statuses);
    }

    public function updateAutoStatus ()
    {
        DB::table('customer_statistics')
            ->update(['days_after_last_order' => DB::Raw('DATEDIFF(CURDATE(), last_order)')]);
        $qty_days =  DB::table('customers')
            ->join('customer_statistics', 'customers.id', '=', 'customer_statistics.customer_id')
            ->update([
                'customers.auto_status' => DB::Raw('
                CASE
                    WHEN customer_statistics.count_orders = 1 AND customer_statistics.days_after_last_order < 45 THEN "new"
                    WHEN customer_statistics.count_orders = 2 AND customer_statistics.days_after_last_order < 45 THEN "perspective"
                    WHEN customer_statistics.count_orders < 3 AND customer_statistics.days_after_last_order > 45 AND  customer_statistics.days_after_last_order < 90 THEN "suspended"
                    WHEN customer_statistics.count_orders < 3 AND customer_statistics.days_after_last_order > 90 AND  customer_statistics.days_after_last_order < 360 THEN "sleep"
                    WHEN customer_statistics.count_orders = 1 AND customer_statistics.days_after_last_order > 360 THEN "one_time"
                    WHEN customer_statistics.count_orders = 2 AND customer_statistics.days_after_last_order > 360 THEN "sleep"
                    WHEN customer_statistics.count_orders > 2 AND customer_statistics.count_orders < 10 AND customer_statistics.days_after_last_order < 90 THEN "loyal"
                    WHEN customer_statistics.count_orders > 9 AND customer_statistics.days_after_last_order < 90 THEN "vip"
                    WHEN customer_statistics.count_orders > 2 AND customer_statistics.days_after_last_order > 90 AND  customer_statistics.days_after_last_order < 360 THEN "risk"
                    WHEN customer_statistics.count_orders > 2 AND customer_statistics.count_orders < 10 AND customer_statistics.days_after_last_order > 360 THEN "lost"
                    WHEN customer_statistics.count_orders > 9 AND customer_statistics.days_after_last_order > 360 AND customer_statistics.days_after_last_order THEN "lost_vip"
                END
                ')
            ]);
    }

}
