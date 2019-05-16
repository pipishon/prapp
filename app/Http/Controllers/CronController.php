<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cron;
use Illuminate\Support\Facades\DB;

class CronController extends Controller
{

    public function index (Request $request)
    {
      return Cron::all();
    }

    public function store (Request $request)
    {
        $items = $request->input('items');
        foreach ($items as $item) {
            Cron::where('id', $item['id'])->update($item);
        }
    }

}
