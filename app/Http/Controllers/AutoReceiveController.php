<?php

namespace App\Http\Controllers;

use App\AutoReceive;
use Illuminate\Http\Request;
use App\MessageTemplate;
use Carbon\Carbon;

class AutoReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return AutoReceive::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return AutoReceive::create($request->only(
            'active',
            'time_from',
            'time_to',
            'day_from',
            'day_to',
            'sms_template_id',
            'email_template_id'
        ));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AutoReceive  $autoReceive
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return self::template();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AutoReceive  $autoReceive
     * @return \Illuminate\Http\Response
     */
    public function edit(AutoReceive $autoReceive)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AutoReceive  $autoReceive
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $arr = array (
            'active',
            'time_from',
            'time_to',
            'day_from',
            'day_to',
            'sms_template_id',
            'email_template_id'
        );
        return AutoReceive::where('id', $id)->update($request->only($arr));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AutoReceive  $autoReceive
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        AutoReceive::find($id)->delete();
    }

    public static function template($type = 'sms')
    {
        date_default_timezone_set('Europe/Kiev');
        $day = Carbon::now()->dayOfWeek - 1;
        if ($day == -1) $day = 6;
        $time = Carbon::now()->toTimeString();
        $auto_receive = AutoReceive::where('day_from', '<=', $day)->where('day_to', '>=', $day)
            ->whereTime('time_from', '<=', $time)->whereTime('time_to', '>=', $time)
            ->where('active', true)->first();
        if (is_null($auto_receive)) return null;
        $template = MessageTemplate::find($auto_receive->{$type.'_template_id'});
        if (is_null($template)) return null;
       return $template->template;
    }
}
