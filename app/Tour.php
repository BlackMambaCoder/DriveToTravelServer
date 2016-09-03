<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kodeine\Metable\Metable;

class Tour extends Model
{
    use Metable;

    const PASSENGERS            = 'passengers';
    const DESTINATION_LOCATION  = 'destinationlocation';
    const RANK                  = 'rank';
    const START_LOCATION        = 'startlocation';
    const DATE_AND_TIME         = 'dateandtime';

    protected $metaTable = 'tours_meta';

    protected $fillable = ['driver_id'];

    protected $appends = [
        'passengers',
        'destinationlocation',
        'rank',
        'startlocation',
        'dateandtime'
    ];

    public function driver()
    {
        return $this->hasOne('\App\User');
    }

    public function getPassengersAttribute()
    {
        return $this->getMeta('passengers');
    }

    public function getDestinationlocationAttribute()
    {
        return $this->getMeta(self::DESTINATION_LOCATION);
    }

    public function getRankAttribute()
    {
        return $this->getMeta(self::RANK);
    }

    public function getStartlocationAttribute()
    {
        return $this->getMeta(self::START_LOCATION);
    }

    public function getDateandtimeAttribute()
    {
        return $this->getMeta(self::DATE_AND_TIME);
    }
}
