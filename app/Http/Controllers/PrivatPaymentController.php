<?php

namespace App\Http\Controllers;

use App\PrivatPayment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrivatPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return PrivatPayment::orderBy('trandate', 'desc')->get();
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PrivatPayment  $privatPayment
     * @return \Illuminate\Http\Response
     */
    public function show(PrivatPayment $privatPayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PrivatPayment  $privatPayment
     * @return \Illuminate\Http\Response
     */
    public function edit(PrivatPayment $privatPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PrivatPayment  $privatPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PrivatPayment $privatPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PrivatPayment  $privatPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(PrivatPayment $privatPayment)
    {
        //
    }
}
