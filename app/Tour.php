<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kodeine\Metable\Metable;

class Tour extends Model
{
    use Metable;

    protected $metaTable = 'tours_meta';
}
