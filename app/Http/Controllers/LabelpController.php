<?php

namespace App\Http\Controllers;

use App\Labelp;
use Illuminate\Http\Request;

class LabelpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Labelp::all();
        //
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
        Labelp::firstOrCreate(array('name' => $request->input('name')));
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Labelp  $labelp
     * @return \Illuminate\Http\Response
     */
    public function show(Labelp $labelp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Labelp  $labelp
     * @return \Illuminate\Http\Response
     */
    public function edit(Labelp $labelp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Labelp  $labelp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Labelp $labelp)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Labelp  $labelp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Labelp $labelp)
    {
        $labelp->delete();
        //
    }
}
