<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kodeine\Metable\Metable;

class Location extends Model
{
    use Metable;

    protected $fillable = [
        'longitude',
        'latitude'
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }
}
