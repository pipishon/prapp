<?php

namespace App\Http\Controllers;

use App\Dictionary;
use Illuminate\Http\Request;

class DictionaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      if ($request->input('type') != null) {
        $result = array();
        $dictionary = Dictionary::where($request->input('type'), '1')->get();
        foreach ($dictionary as $row) {
          $result[$row['from']] = $row['to'];
        }
        return $result;
      }
      return Dictionary::all();
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
        return Dictionary::create($request->only(
            'from',
            'to',
            'delivery',
            'payment'
        ));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dictionary  $dictionary
     * @return \Illuminate\Http\Response
     */
    public function show(Dictionary $dictionary)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Dictionary  $dictionary
     * @return \Illuminate\Http\Response
     */
    public function edit(Dictionary $dictionary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Dictionary  $dictionary
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $arr = array (
            'from',
            'to',
            'delivery',
            'payment'
        );
        return Dictionary::where('id', $id)->update($request->only($arr));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Dictionary  $dictionary
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      Dictionary::find($id)->delete();
    }
}
