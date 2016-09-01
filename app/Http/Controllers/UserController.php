<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

//use App\Http\Requests;

class UserController extends Controller
{
    const DATE_FORMAT = "d-M-Y H:i:s";
    const LOG_FILE_NAME = "user_controller";

    public function index()
    {
        return;// '{zdravko :pijandura}';
    }

    public function store(Request $request) {
        $this->printToFile(">>>>>>>>>> store method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $userJsonObject = json_decode($requestData, true);

        $userData       = [
            'username' => $userJsonObject['username'],
            'password' => $userJsonObject['password']
        ];

        $user           = User::where('username', $userData['username'])->first();


        $this->printToFile($user);

        if ($user != null) {
            $this->printToFile('user ' . $userData['username'] . ' exists');
            return response()->json('Username exists', 409);
        }

        $user           = User::create($userData);

        unset($userJsonObject['username']);
        unset($userJsonObject['password']);
        unset($userJsonObject['id']);

        $this->printToFile('user without username');
        $this->printToFile(implode("; ", $userJsonObject));

        $user->setMeta($userJsonObject);
        $user->save();

        $user->friends;

        $this->printToFile("user to return: " . $user);

        return $user;
    }

    public function authenticate () {
        $this->printToFile(">>>>>>>>>> authenticate method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $userJsonObject = json_decode($requestData, true);


        $this->printToFile($requestData);

        $userData       = [
            'username' => $userJsonObject['username'],
            'password' => $userJsonObject['password']
        ];

        $user           = User::where('username', $userData['username'])->first();

        $this->printToFile($user);

        if ($user == null) {
            $this->printToFile('user ' . $userData['username'] . ' does not exist');
            return response()->json('Username does not exist', 404);
        }

        if ($user->password != $userData['password']) {
            $this->printToFile('user password' . $userData['password'] . ' is incorrect');
            return response()->json('Password is incorrect', 401);
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

    private function printToFile($message) {
        $date = date(self::DATE_FORMAT);

        $strMsg = $date . " <<< USER CONTROLLER >>> " . $message . "\n";

        $path = base_path("log/" . self::LOG_FILE_NAME);

        file_put_contents($path, $strMsg, FILE_APPEND | LOCK_EX);
    }
}
