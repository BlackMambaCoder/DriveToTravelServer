<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kodeine\Metable\Metable;

class Tour extends Model
{
    use Metable;

    protected $metaTable = 'tours_meta';

    protected $fillable = ['driver_id'];

    public function driver()
    {
        return $this->hasOne('\App\User');
    }
}
