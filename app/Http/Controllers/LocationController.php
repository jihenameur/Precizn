<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Setting;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use League\CommonMark\Util\Xml;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function GetLocationWithAdresse($street, $postcode, $city, $region)
    {
        $latlong = array();
        //$idsupplier = $idsupplier;
        // $street = $request->street;
        // $postcode = $request->postcode;
        // $city = $request->city;
        // $region = $request->region;
        $a = $street . ", " . $postcode . ", " . $city . ", " . $region;
        $address = urlencode($a);
        $link = 'https://maps.googleapis.com/maps/api/geocode/xml?address=' . $address . '&sensor=true_or_false&key=AIzaSyCYRBZBDovYe4GKiOH2PRyDtTWO6ymAZXA';
        $file = file_get_contents($link);
        if (!$file) {
            // echo "Err: No access to Google service: " . $a . "<br/>\n";
            return "Err: No access to Google service: " . $a;
        } else {
            $get = simplexml_load_string($file);

            if ($get->status == "OK") {
                $lat = (float) $get->result->geometry->location->lat;
                $long = (float) $get->result->geometry->location->lng;
                // echo "lat: " . $lat . "; long: " . $long . "; " . $a . "<br/>\n";
                array_push($latlong, ['lat' => $lat, 'long' => $long, 'latlong' => $a]);
                return $latlong;
            } else {
                // echo "Err: address not found: " . $a . "<br/>\n";
                return "Err: address not found: " . $a;
            }
        }
    }
    public function getdistances($from, $toList)
    {
        $coords = [];
        $from_latlong = '';
        $to_latlong = $from->lat . "," . $from->long;

        foreach ($toList as $key => $value) {
            array_push($coords, ['id' => $value['id'], 'lat' => $value['lat'], 'long' => $value['long']]);
            $from_latlong = $from_latlong . ($value['lat'] . "," . $value['long'] . "|");
        }

        $distance_data = file_get_contents(
            'https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=' . $from_latlong . '&destinations=' . $to_latlong . '&key=AIzaSyCYRBZBDovYe4GKiOH2PRyDtTWO6ymAZXA'
        );
        $distance_arr = json_decode($distance_data);
        $distances = array();
        foreach ($distance_arr->rows as $key => $element) {
            $distance = $element->elements[0]->distance->text;
            $duration = $element->elements[0]->duration->text;
            // The matching ID
            $id = $toList[$key];
            $distance = preg_replace("/[^0-9.]/", "",  $distance);
            $duration = preg_replace("/[^0-9.]/", "",  $duration);

            $distance = $distance * 1.609344;
            $distance = number_format($distance, 1, '.', '');
            $duration = number_format($duration, 1, '.', '');
            $delivpr = Setting::find(1);
            if ($distance <= 5) {
                $deliveryprice = $delivpr['delivery_price'];
            } else {
                $deliveryprice = $delivpr['delivery_price'] + 2;
            }

            $id['distance']=$distance;
            $id['time']=$duration;
            $id['deliveryprice']=$deliveryprice;

            array_push($distances, $id);

        }
        $array = collect($distances)->sortBy('distance')->toArray();

        return $array;
    }
    public function getSuppDistances()
    {
        $client =  Auth::user();
        $suppliers = Supplier::all();


        return  $this->getdistances($client, $suppliers);
    }
    public function getSuppDistancesSuppliers($suppliers)
    {
        $client =  Auth::user();


        return  $this->getdistances($client, $suppliers);
    }
    /* function GetDrivingDistance(Request $request)
{
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?key=AIzaSyCYRBZBDovYe4GKiOH2PRyDtTWO6ymAZXA&origins=" . $request->long1 . "," .  $request->lat1 . "&destinations=" . $request->long2 . "," .   $request->lat2 . "&mode=driving";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response, true);
    // dd($response_a);
    $status = "ERROR";
    $dist   = 0;
    $time   = 0;
    if ($response_a['rows'][0]['elements'][0]['status'] === 'OK') {
        $dist   = $response_a['rows'][0]['elements'][0]['distance']['text'];
        $time   = $response_a['rows'][0]['elements'][0]['duration']['text'];
        $status = "OK";
    }

    return array('status' => $status, 'distance' => $dist, 'time' => $time);
}
    function distance(Request $request)
{
    $theta = $request['lon1'] - $request['lon2'];
    $dist = sin(deg2rad($request['lat1'])) * sin(deg2rad($request['lat2'])) +  cos(deg2rad($request['lat1'])) * cos(deg2rad($request['lat2'])) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($request['unit']);

    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}*/
    public function GetCurrentLocation()
    {
        return view('LocationView');
    }
    function geoLocate($address)
    {
        try {
            $address =
                $lat = 0;
            $lng = 0;
            $apikey = "AIzaSyCYRBZBDovYe4GKiOH2PRyDtTWO6ymAZXA";
            $data_location = "https://maps.google.com/maps/api/geocode/json?key=" . $apikey . "&address=" . str_replace(" ", "+", $address) . "&sensor=false";
            $data = file_get_contents($data_location);
            usleep(200000);
            // turn this on to see if we are being blocked
            // echo $data;
            $data = json_decode($data);
            if ($data->status == "OK") {
                $lat = $data->results[0]->geometry->location->lat;
                $lng = $data->results[0]->geometry->location->lng;

                if ($lat && $lng) {
                    return array(
                        'status' => true,
                        'lat' => $lat,
                        'long' => $lng,
                        'google_place_id' => $data->results[0]->place_id
                    );
                }
            }
            if ($data->status == 'OVER_QUERY_LIMIT') {
                return array(
                    'status' => false,
                    'message' => 'Google Amp API OVER_QUERY_LIMIT, Please update your google map api key or try tomorrow'
                );
            }
        } catch (Exception $e) {
        }

        return array('lat' => null, 'long' => null, 'status' => false);
    }
}
