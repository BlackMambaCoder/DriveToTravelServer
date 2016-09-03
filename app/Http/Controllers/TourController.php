<?php

namespace App\Http\Controllers;

use App\Tour;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Input;

class TourController extends Controller
{
    const DATE_FORMAT = "d-M-Y H:i:s";
    const LOG_FILE_NAME = "tour_controller";

    public function __construct()
    {
    }

    public function store ()
    {
        $this->printToFile("\n>>>>>>>>>> store method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $userJsonObject = json_decode($requestData, true);

        $this->printToFile('users json object');
        $this->printToFile($requestData);

        $driversId = $userJsonObject['driver_id'];

        $driver = User::find($driversId);

        $this->printToFile("driver");
        $this->printToFile($driver);

        if ($driver == null)
        {
            $this->printToFile('No such driver: ' . $driversId);
            return response()->json('No such driver', 499);
        }

        $tour = new Tour();
        $tour->driver_id = $driver->id;
        $tour->save();

        unset($userJsonObject['driver_id']);
        unset($userJsonObject['id']);

        $tour->setMeta($userJsonObject);
        $tour->save();

        $this->printToFile('Tour created:');
        $this->printToFile($tour);

        return $tour;
    }

    public function getDriversTours()
    {
        $this->printToFile("\n>>>>>>>>>> getDriversTours <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
        $userJsonObject = json_decode($requestData, true);

        $this->printToFile('users json object');
        $this->printToFile($requestData);

        $driversId = $userJsonObject['id'];

        $driver = User::find($driversId);

        $this->printToFile("driver");
        $this->printToFile($driver);

        if ($driver == null)
        {
            $this->printToFile('No such driver: ' . $driversId);
            return response()->json('No such driver', 499);
        }

        $tours = $driver->tours;
        $this->printToFile('Tours of driver '. $driver->username .':');
        $this->printToFile($tours);

        return $tours;
    }

    public function getAllTours()
    {
        return Tour::all();
    }

    public function searchByLocation()
    {
        $this->printToFile("\n>>>>>>>>>> search by location method <<<<<<<<<<");
        $requestData    = file_get_contents('php://input');
//        $requestData    = '{ "startlocation": "newstara", "destinationlocation":"dest"}';
        $jsonObject = json_decode($requestData, true);

        $startLocation = $jsonObject['startlocation'];
        $destLocation = $jsonObject['destinationlocation'];

        $tours = Tour::all();

        $tourStart = $tours->where(Tour::START_LOCATION, $startLocation);
        $tourDest  = $tours->where(Tour::DESTINATION_LOCATION, $destLocation);

        return $tourStart->merge($tourDest);

    }



    public function search (Request $request)
    {
        $requestData    = file_get_contents('php://input');
        $userJsonObject = json_decode($requestData, true);
//        $postValue = $request->all();

        $tours = null;

        if (count($userJsonObject['data']) == 1)
        {
            // SEARCH BY DISTANCE

            $distance = $userJsonObject['data'][0];
            $tours = Tour::where('distance', $distance)->get();
        }
        else
        {
            $startLocation      = $userJsonObject['data'][0];
            $endLocation        = $userJsonObject['data'][1];
            $datetime           = $userJsonObject['data'][2];

            $query = Tour::query();

            $query->where('startlocation', $startLocation);
            $query->where('destinationlocation', $endLocation);
            $query->where('dateandtime', $datetime);

            $tours = $query->get();
        }

        return $tours;
    }

    private function rankDriver($user, $rankValue) {
        if ($user->rank == null) {
            $user->rank             = 0.0;
            $user->no_of_ranks      = 0.0;
        }

        $allRank                    = $user->rank *
                                        $user->no_of_ranks;
        $allRank                   += $rankValue;
        $user->no_of_ranks         += 1;
        $user->rank                 = $allRank /
                                        $user->no_of_ranks;

        $user->save();

        return $user->rank;
    }

    public function rankDriversTour () {
        $rankValue                  = Input::get('rank');
        $tourId                     = Input::get('tourid');

        $tour                       = Tour::find($tourId);

        if ($tour->rank == null) {
            $tour->rank                 = 0.0;
            $tour->no_of_ranks          = 0.0;
        }

        $allRank                    = $tour->rank *
                                        $tour->no_of_ranks;
        $allRank                   += $rankValue;
        $tour->no_of_ranks         += 1;
        $tour->rank                 = $allRank /
                                        $tour->no_of_ranks;

        $tour->save();

        $user                       = $tour->user;
        return $this->rankDriver($user, $tour->rank);
    }

    public function getNearTours() {
        $latitude                   = Input::get('latitude');
        $longitude                  = Input::get('latitude');
        $minDistance                = Input::get('mindistance');

        $tours                      = $this->filterToursByDistance(
                                            $latitude,
                                            $longitude,
                                            $minDistance
                                        );

        return $tours;
    }

    private function filterToursByDistance($latitude, $longitude, $minDistance) {
        $allToursCollection     = Tour::all();

        $tourSearchResult       = [];

        foreach ($allToursCollection as $tour) {
            if (($tourLatitude  = $tour->latitude) == null) {
                break;
            }

            if (($tourLongitude = $tour->longitude) == null) {
                break;
            }

            $distance               = $this->calculateDistance($tourLatitude, $tourLongitude, $latitude, $longitude);
            $distance               = abs($distance);

            if ($distance < $minDistance)
                $tourSearchResult[]     = $tour;
        }

        return $tourSearchResult;
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

    private function toRadians($inputArg) {
        return ($inputArg * pi()) / 180.0;
    }

    private function printToFile($message) {
        $date = date(self::DATE_FORMAT);

        $strMsg = "<<< " . $date . " >>> " . $message . "\n";

        $path = base_path("log/" . self::LOG_FILE_NAME);

        file_put_contents($path, $strMsg, FILE_APPEND | LOCK_EX);
    }
}
