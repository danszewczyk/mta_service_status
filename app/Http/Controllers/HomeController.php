<?php

namespace App\Http\Controllers;

use Config;

class HomeController extends Controller
{

	public function index($search = null)
	{	

		$content = file_get_contents('trips.txt');
		$rows = explode("\n",$content);
		$s = array();
		foreach($rows as $key => $row) {
		    $s[] = str_getcsv($row);
		    unset($s[$key][2], $s[$key][5], $s[$key][6]);

		    
		}

		$result = array_unique($s, SORT_REGULAR);
		
		//var_dump($result);
		
		if(isset($search)){
			foreach ($result as $trip) {
				similar_text($search, $trip[3], $percent);
				//var_dump($percent);

				$new_array = array();
				$new_array['trip'] = $trip;
				$new_array['percent'] = $percent;
				var_dump($new_array);
			}
		}
			

		


		//return view('home');
	}

	public function stop_info($stop_id)
	{

		$json = file_get_contents('http://bustime.mta.info/api/siri/stop-monitoring.json?key='. Config::get('app.mta_key') .'&MonitoringRef=' . $stop_id);
		$obj = json_decode($json);
		
		$buses = $obj->Siri->ServiceDelivery->StopMonitoringDelivery[0]->MonitoredStopVisit;

		//get a list of buses that stop here

		$bus_routes = array();
		foreach($buses as $bus)
		{
			array_push($bus_routes, $bus->MonitoredVehicleJourney->PublishedLineName);
		}

		$bus_routes = array_unique($bus_routes);
		

		return view('stop_info')->with([
			'buses' => $buses,
			'bus_routes' => $bus_routes
		]);
	}

	public function bus_info($bus_id)
	{

		$json = file_get_contents('http://bustime.mta.info/api/siri/vehicle-monitoring.json?key='. Config::get('app.mta_key') .'&VehicleRef=' . $bus_id);
		$obj = json_decode($json);
		
		$bus = $obj->Siri->ServiceDelivery->VehicleMonitoringDelivery[0]->VehicleActivity[0]->MonitoredVehicleJourney;

		$is_limited = false;

		if(substr($bus->DestinationName, 0, 4 ) == "LTD ")
		{
			$is_limited = true;
			$bus->DestinationName = substr($bus->DestinationName, 4 );

		}
		

		
		return view('bus_info')->with([
			'bus' => $bus,
			'is_limited' => $is_limited
		]);
	}

}