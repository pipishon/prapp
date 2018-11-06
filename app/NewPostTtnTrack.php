<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewPostTtnTrack extends Model
{
	protected $guarded = [];

    public function scopeSearch ($query, $input)
    {
      if (isset($input['int_doc_number'])) {
          $query = $query->where('int_doc_number', 'LIKE', '%'.$input['int_doc_number'].'%');
      }
      if (isset($input['prom_id'])) {
          $query = $query->where('prom_id', 'LIKE', '%'.$input['prom_id'].'%');
      }
      if (isset($input['name'])) {
          $query = $query->where(function ($query) use ($input) {
              $query->where('full_name', 'LIKE', '%'.$input['name'].'%')
                    ->orWhere('phone', 'LIKE', '%'.$input['name'].'%');
          });
      }
    }
}
