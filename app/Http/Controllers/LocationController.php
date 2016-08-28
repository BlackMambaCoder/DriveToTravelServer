<?php

namespace App\Http\Controllers;

use App\Location;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class LocationController extends Controller
{
    public function store (Request $request, $userIdParam) {
        $requestData            = $request->get('location');
        $user                   = User::find($userIdParam);

        if ($user == null) {
            return "not found";
        }

        if (count($user->location()->get()->all()) == 0) {
            $location               = $this->createLocation($requestData, $user);
        }
        else {
            $location               = $this->updateLocation($requestData, $user);
        }

        $this->usersFriendsLocation();
    }

    private function createLocation($requestData, $user) {
        $locationData           = [
            'longitude' => $requestData['longitude'],
            'latitude'  => $requestData['latitude']
        ];

        $location               = $user->location()->create($locationData);

        unset($requestData['longitude']);
        unset($requestData['latitude']);

        $location->setMeta($requestData);
        $location->save();

        return $location;
    }

    private function updateLocation($requestData, $user) {
        $location               = $user->location;

        $location->longitude    = $requestData['longitude'];
        $location->latitude     = $requestData['latitude'];

        unset($requestData['longitude']);
        unset($requestData['latitude']);

        $location->setMeta($requestData);
        $location->save();

        return $location;
    }

    private function usersFriendsLocation() {

    }
}
