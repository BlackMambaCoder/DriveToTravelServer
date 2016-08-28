<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

//use App\Http\Requests;

class UserController extends Controller
{
    public function index()
    {
        return;// '{zdravko :pijandura}';
    }

    public function store() {
        $requestData    = Input::get('user');

        $userData       = [
            'username' => $requestData['username'],
            'password' => $requestData['password']
        ];

        $user           = User::create($userData);

        unset($requestData['username']);
        unset($requestData['password']);

        $user->setMeta($requestData);
        $user->save();

        $user->friends;

        return $user;
    }

    public function authenticate () {
        $username = Input::get('username');
        $password = Input::get('password');

        if (($user = User::where('username', $username)->first()) == null) {
            return 'false username';
        }

        if ($user->password != $password) {
            return 'false password';
        }

        $user->friends;
        return $user;
    }

    public function addFriend() {
        $userId                     = Input::get('userid');
        $friendsName                = Input::get('username');

        $user                       = User::find($userId);
        $friendUser                 = User::where(
                                                'username',
                                                $friendsName
                                            )->first();

        $this->attachOne($user->friends(), $friendUser);
    }

    public function removeFriend() {
        $userId                     = Input::get('userid');
        $friendId                   = Input::get('friendid');

        $user                       = User::find($userId);

        $user->friends()->detach($friendId);

        return $user->friends;
    }

    public function friendsWith() {
        $firstUserName              = Input::get('firstuser');
        $secondUserName             = Input::get('seconduser');

        $firstUser                  = User::where(
                                                'username',
                                                $firstUserName
                                            )->first();

        $secondUser                 = User::where(
                                                'username',
                                                $secondUserName
                                            )->first();

        if ($firstUser->friends->contains($secondUser->id)){
            return "yes";
        }
        else {
            return "no";
        }
    }

    public function storeUsersLocation () {
        $usersId                    = Input::get('userid');
        $latitude                   = Input::get('latitude');
        $longitude                  = Input::get('longitude');
        $altitude                   = Input::get('altitude');
        $speed                      = Input::get('speed');

        $user                       = User::find($usersId);
        $locationData               = [
                                            'latitude' => $latitude,
                                            'longitude' => $longitude,
                                            'altitude' => $altitude,
                                            'speed' => $speed
                                        ];

        $user->location()->create($locationData);
    }

    private function attachOne($query, $object) {
        if ($query->get()->isEmpty()) {
            $query->attach($object);
        }
        else {
            $objects = $query->get()->push($object);
            $query->sync($objects);
        }
    }
}
