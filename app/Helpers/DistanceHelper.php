<?php

namespace App\Helpers;

class DistanceHelper
{
    public function calculateDistanceByLongLat(array $listStartPoints, array $target)
    {
        $distances = [];
        foreach ($listStartPoints as $point) {
            $theta = $point["long"] - $target["long"];
            $dist = sin(deg2rad($point["lat"])) * sin(deg2rad($target["lat"])) + cos(deg2rad($point["lat"])) * cos(deg2rad($target["lat"])) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            array_push($distances, $miles * 1.609344);
        }
        return $distances;
    }

}
