<?php

namespace App\Http\Controllers;

use App\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return MessageTemplate::all();
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
      $message = MessageTemplate::create(['name' => $request->input('name')]);
      return $request->all();
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MessageTemplate  $messageTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(MessageTemplate $messageTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MessageTemplate  $messageTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(MessageTemplate $messageTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MessageTemplate  $messageTemplate
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
      MessageTemplate::find($id)->fill($request->only('name', 'template'))->save();
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MessageTemplate  $messageTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      MessageTemplate::find($id)->delete();
    }
}
