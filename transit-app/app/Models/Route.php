<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $guarded = [];

    public function transport()
    {
        return $this->belongsTo(Transport::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
