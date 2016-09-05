<?php

namespace App\Http\Controllers;

use App\Tour;
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
        return response()->json("test", 200);
    }

    public function store()
    {
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
            return response()->json('Username does not exist', 498);
        }

        if ($user->password != $userData['password']) {
            $this->printToFile('user password' . $userData['password'] . ' is incorrect');
            return response()->json('Password is incorrect', 499);
        }

        $this->printToFile("User logged in correctly");
        $this->printToFile($user);
        $user->friends;
        return $user;
    }

    public function addFriend() {
        $this->printToFile(">>>>>>>>>> addFriend method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $jsonObject = json_decode($requestData, true);

        $userId                     = $jsonObject['userid'];
        $friendsName                = $jsonObject['username'];

        $user                       = User::find($userId);
        $friendUser                 = User::where(
                                                'username',
                                                $friendsName
                                            )->first();

        $this->attachOne($user->friends(), $friendUser);
//        $this->attachOne($friendUser->friends(), $user);

        return response()->json('users are friends');
    }

    public function removeFriend() {
        $this->printToFile(">>>>>>>>>> removeFriend method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $jsonObject = json_decode($requestData, true);

        $userId                     = $jsonObject['userid'];
        $friendsName                = $jsonObject['username'];

        $user                       = User::find($userId);
        $friend                     = User::where(User::USERNAME, $friendsName)->first();

        $user->friends()->detach($friend->id);

        return $user->friends;
    }

    public function friendsWith() {
        $this->printToFile("\n>>>>>>>>>> store method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $userJsonObject = json_decode($requestData, true);

        $firstUserName              = $userJsonObject['firstuser'];
        $secondUserName             = $userJsonObject['seconduser'];

        $firstUser                  = User::where(
                                                'username',
                                                $firstUserName
                                            )->first();

        $secondUser                 = User::where(
                                                'username',
                                                $secondUserName
                                            )->first();

        if ($firstUser->friends->contains($secondUser->id))
        {
            return response()->json('are friends', 200);
        }

        return response()->json('not friends', 404);
    }

    public function storeUsersLocation () {
        $this->printToFile("\n>>>>>>>>>> search by location method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $jsonObject = json_decode($requestData, true);

        $usersId                    = $jsonObject['userid'];
        $latitude                   = $jsonObject['latitude'];
        $longitude                  = $jsonObject['longitude'];
        $altitude                   = $jsonObject['altitude'];
        $speed                      = $jsonObject['speed'];

        $user                       = User::find($usersId);
        $locationData               = [
                                            'latitude' => $latitude,
                                            'longitude' => $longitude,
                                            'altitude' => $altitude,
                                            'speed' => $speed
                                        ];

        $user->location()->create($locationData);
    }

    public function uploadBitmapImage()
    {
        $this->printToFile("\n>>>>>>>>>> upload bitmap method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $userJsonObject = json_decode($requestData, true);

        $this->printToFile($userJsonObject);
    }

    public function updateUserLocation()
    {
        $this->printToFile("\n>>>>>>>>>> upload bitmap method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $jsonObject = json_decode($requestData, true);

        $longitude = $jsonObject['longitude'];
        $latitude = $jsonObject['latitude'];
        $userId = $jsonObject['userid'];
        $distance = $jsonObject['distance'];

        $user = User::find($userId);

        if ($user->location == null)
        {
            $user->location()->create(['longitude' => $longitude, 'latitude' => $latitude]);
        }
        else
        {
            $location = $user->location;
            $location->longitude = -3.0;
            $location->latitude = -4.0;

            $location->save();
        }

        return $this->getNearUsers($user, $distance);
//
//        $tours = Tour::all();
//
//        foreach ($tours as $tour)
//        {
//            if (in_array($user->username, $tour->passengers))
//            {
//                return $tour->passengers;
//            }
//        }
//
//        return response()->json('error', 404);
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

    private function getNearUsers($user, $minDistance)
    {
        $usersFriends = $user->friends;
        $usersLatitude = $user->location->latitude;
        $usersLongitude = $user->location->longitude;
        $nearUsers = [];

        if ($usersLatitude == null || $usersLongitude == null)
        {
            return null;
        }

        foreach ($usersFriends as $friend)
        {
            if (
                $friend->location->latitude == null ||
                $friend->location->longitude == null
            )
            {
                continue;
            }

            $distance = $this->calculateDistance(
                            $usersLatitude,
                            $usersLongitude,
                            $friend->location->latitude,
                            $friend->location->longitude
                        );

            $distance = abs($distance);

            if ($distance < $minDistance)
            {
                $nearUsers[] = $friend;
            }
        }

        return $nearUsers;
    }

    private function calculateDistance($lat1Arg, $lng1Arg, $lat2Arg, $lng2Arg) {
        $earthRadius            = 6371000.0;

        $lat1Radians            = $this->toRadians($lat1Arg);
        $lat2Radians            = $this->toRadians($lat2Arg);

        $latDifference          = $this->toRadians($lat2Arg - $lat1Arg);
        $lngDifference          = $this->toRadians($lng2Arg - $lng1Arg);

        $a                      = sin($latDifference/2.0) * sin($latDifference/2.0) +
            cos($lat1Radians) * cos($lat2Radians) *
            sin($lngDifference / 2.0) * sin($lngDifference / 2.0);

        $c                      = 2 * atan2(sqrt($a), sqrt(1.0 - $a));

        return $earthRadius * $c;
    }


    public function storeTour()
    {
        $this->printToFile(">>>>>>>>>> store method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $userJsonObject = json_decode($requestData, true);

        $driversUsername = $userJsonObject['tourdriver'];

        $driver = User::where('username', $driversUsername)->first();

        if ($driver == null)
        {
            $this->printToFile('No such driver: ' . $driversUsername);
            return response()->json('No such driver', 499);
        }

        $tour = $driver->tours()->create();

        unset($userJsonObject['tourdriver']);

        $tour->setMeta($userJsonObject);
        $tour->save();

        $this->printToFile('Tour created:');
        $this->printToFile($tour);

        return $tour;
    }
}
