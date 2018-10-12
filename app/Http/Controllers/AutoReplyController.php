<?php

namespace App\Http\Controllers;

use App\AutoReply;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AutoReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return AutoReply::all();
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
      return AutoReply::create(array(
        'message' => (string) $request->input('message'),
        'from' => Carbon::parse($request->input('from')),
        'to' => Carbon::parse($request->input('to')),
        'active' => true
      ));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AutoReply  $autoReply
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      return AutoReply::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AutoReply  $autoReply
     * @return \Illuminate\Http\Response
     */
    public function edit(AutoReply $autoReply)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AutoReply  $autoReply
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $autoReply = AutoReply::find($id);
        $inputs = $request->only('message', 'active');
        $date_times = $request->only('from', 'to');
        foreach ($inputs as $name => $val) {
          if ($val === null) continue;
          $autoReply->{$name} = $val;
        }
        foreach ($date_times as $name => $val) {
          if ($val === null) continue;
          $autoReply->{$name} = Carbon::parse($val)->setTimezone('Europe/Kiev');
        }
        $autoReply->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AutoReply  $autoReply
     * @return \Illuminate\Http\Response
     */
    public function destroy(AutoReply $autoReply)
    {
        //
    }
}
