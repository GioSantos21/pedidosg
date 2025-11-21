<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correlative extends Model
{
    protected $fillable = ['branch_id', 'prefix', 'initial', 'final', 'counter', 'counter_record'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
