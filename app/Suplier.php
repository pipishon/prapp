<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suplier extends Model
{
	protected $guarded = [];

    public function products ()
    {
        return $this->hasMany('App\ProductSuplier');
    }

    //
}
