<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Kodeine\Metable\Metable;

class User extends Authenticatable
{
    use Metable;

    const USERNAME = 'username';

    protected $metaTable = 'users_meta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    public function location() {
        return $this->hasOne('App\Location');
    }

    public function friends() {
        return $this->belongsToMany('\App\User', 'friend_user', 'user_id', 'friends_id');
    }

    public function tours()
    {
        return $this->hasMany('\App\Tour', 'driver_id', 'id');
    }

    public function __toString()
    {
        $retData = $this->username . "; ". $this->password . "; " . $this->firstname . "; " . $this->lastname;

        return $retData;
    }
}
