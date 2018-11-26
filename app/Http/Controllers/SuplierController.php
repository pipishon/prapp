<?php

namespace App\Http\Controllers;

use App\Suplier;
use Illuminate\Http\Request;

class SuplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Suplier::withCount('products')->orderBy('name')->get();
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
        Suplier::firstOrCreate(array('name' => $request->input('name')));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Suplier  $suplier
     * @return \Illuminate\Http\Response
     */
    public function show(Suplier $suplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Suplier  $suplier
     * @return \Illuminate\Http\Response
     */
    public function edit(Suplier $suplier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Suplier  $suplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Suplier $suplier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Suplier  $suplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Suplier $suplier)
    {
        $suplier->delete();
        //
    }
}
