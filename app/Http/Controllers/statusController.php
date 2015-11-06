<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class StatusController extends Controller
{

	public function index()
	{	

		return view('home');
	}

	public function status($line)
	{

	//$data = file_get_contents('serviceStatus10.txt'); //http://web.mta.info/status/serviceStatus.txt
	$data = file_get_contents('http://web.mta.info/status/serviceStatus.txt');
	$obj = simplexml_load_string($data) or die("Error: Cannot create object");
		//dd($obj);
	$result = array();
	$result['subway'] = [];	
	$result['bus'] = [];
	$result['rail'] = [];
		
	foreach($obj->subway->line as $line){
	 	if ($line->status != "GOOD SERVICE"){
			//only show lines with issues
			$status = $line->status;
			$text = $line->text;	

			//$alerts = explode('<br/><br/>\n', $text);
			

			$alerts = explode('<span class="Title', trim($text));
			array_shift($alerts);
			//var_dump($alerts);
			

			foreach ($alerts as $alert) {

				$alert = trim(preg_replace('/\s\s+/', ' ', $alert));
							
				//if planned work
				if (preg_match("/^PlannedWork\" >/", $alert)){

					

					$content = explode('<b>', $alert, 2);
					//var_dump($content); //dfkhgkjdshfgkhldsflkgh


					$content_1 = preg_split("/Days,\s|Day,\s|Evening,\s|Evenings,\s|Late Nights,\s|Late Night,\s|Nights,\s|Night,\s|Weekends,\s|Weekend,\s/", $content[1], null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
					
					//dd($content_1[1]);
					//$time_1 = end($content_1);
					//$time_2 = prev($content_1);

					//$content_2 = "$time_2$time_1";
					//dd($content[1]);

					

					$content_3 = explode('<br>', $content_1[1], 2);

					

					//$content_3 = explode('<br>', $content_2[1], 2);
					//var_dump($content);
					//var_dump($content_3);


					$title = str_replace(array("[FT]", "Late Nights", "Late Night", "Weekends", "Weekend", "Days",  "Day"), "", $content_1[0]);
					$title = preg_replace('/[\n]/', '<br/>', trim(strip_tags(preg_replace('/\<br clear=left\>/', ' ', $title))));
					$datetime = trim(ltrim(strip_tags($content_3[0]), ' ,') );

					$time_raw = explode(', ', $datetime, 2);
					//extract the days from this
					//<<<11:30 PM Fri to 5 AM Mon>>>>, until Nov 9
					//dd($time_raw);
					if (preg_match_all('/[a-zA-Z]{3}/', $time_raw[0], $matches)){
						

						//analyze words more than 3 characters here

						if(strpos($time_raw[0], 'midnight')){
							$time = str_replace('midnight', 'AM', $time_raw[0]);
						}else if(strpos($time_raw[0], 'beginning')){
							$time = str_replace('beginning', '', $time_raw[0]);
						}else{
							$time = $time_raw[0];
						}

					

						//var_dump($time);
						$time = preg_replace('/[a-zA-Z]{3}/', '', $time);
						$time = preg_replace('/\s\s+/', ' ', $time);
						$time = trim($time);
						
						$start_day = $matches[0][0];
						$end_day = $matches[0][1];

						$dates = $time_raw[1];

						$time = explode(' to ', $time);


					}else{
						//dd($time_raw);
						
						$days_dates = explode(', ', $time_raw[1], 2);
						//$days = explode(' to ', $days_dates[0]);

						$days = preg_split("/ to | and/", $days_dates[0], null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
						
						
						//$test = $days[1];

						$start_day = $days[0];

						if(isset($days[1])){
							//more than one day
							$end_day = $days[1];

						}else{
							$end_day = $days[0];

						}
						

						


						$dates = $days_dates[1];


						$time = explode(' to ', $time_raw[0]);

						
					}

					
					
					

					$date_sets = explode(' &#149; ', $dates );
					//dd($date_sets);

					$date_set_new = array();

					foreach ($date_sets as $key => $date_set) {
						
						if(strpos($date_set, ', ') !== false){
							//has days associated as well
							//separate days from dates
							$days_dates_add = explode(', ', $date_set);
							//put days in new variables
							$days_add = explode(' to ', $days_dates_add[0]);
							
							$start_day = $days_add[0];
							$end_day = $days_add[1];

							$date = explode(' - ', $days_dates_add[1]);

						}else{

							//what if only one day?
							$date = explode(' - ', $date_set);
							
						}

						


						if(preg_match("/until/", $date[0])){
							//this is an until
							// deal with later
							$date_until = str_replace('until ', '', $date[0]);
							
							$date_set_new[$key]['end_date'] = date('Y-m-d', strtotime($date_until));

						}else{
							
							
							$date_set_new[$key]['start_date'] = date('Y-m-d', strtotime($date[0]));

							if (isset($date[1]) && is_numeric($date[1])){
								// same month as start date
								$month = date('M', strtotime($date[0]));
								$date_fixed = $month . " " . $date[1];
								
								$date_set_new[$key]['end_date'] = date('Y-m-d', strtotime($date_fixed));
							}else if(isset($date[1])){
								$date_set_new[$key]['end_date'] = date('Y-m-d', strtotime($date[1]));
							}else{
								$date_set_new[$key]['end_date'] = date('Y-m-d', strtotime($date[0]));
							}
							
							



							
						}

						$date_set_new[$key]['start_day'] = $start_day;
						
						$date_set_new[$key]['end_day'] = $end_day;


						
					}



					

					$start_time = $time[0];
					if (isset($time[1])){
						$end_time = date('H:i', strtotime($time[1]));
					}else{
						$end_time = null;
					}
					
											
					//dd($details);
					$purifier = new \HTMLPurifier();
					$details = $purifier->purify($content_3[1]);

					$details = trim(strip_tags($details, '<br><table><tr><td></table></tr></td>'));
					$details = preg_replace("/(\.)\n/", '$1<br/>', $details);
					$details = preg_replace('#(<br */?>\s*)(<br */?>\s*)(<br */?>\s*)+#i', ' ', preg_replace('/[\n]+/', ' ', $details));
					$details = preg_replace("/^<br\s\/>/", '', $details);
					$details = preg_replace("/([^<>])<br>([^<>])/", '$1 $2', $details);
					$details = trim(preg_replace("/(\S)<br\s\/>(\S)/", '$1 $2', $details));

					
					
					$ada = false;
					if(preg_match("/This service change affects one or more ADA accessible stations. Please call 511 for help with planning your trip. If you are deaf or hard of hearing, use your preferred relay service provider or the free 711 relay/", $details)){
						//dd('handcap');
						$ada = true;
						$details = str_replace("This service change affects one or more ADA accessible stations. Please call 511 for help with planning your trip. If you are deaf or hard of hearing, use your preferred relay service provider or the free 711 relay.", '', $details);
						
					}

					$direction = null;
					if(stripos($title, '-bound')){
						//has a direction specific alert
						preg_match("/]\s(.*)\-bound/", $title, $matches);
						$direction = $matches[1];
					}else if(stripos($title, 'uptown')){
						$direction = 'uptown';
					}else if(stripos($title, 'downtown')){
						$direction = 'downtown';
					}
					//var_dump($content_3[1]);
					$clean_details = $details;




				
					preg_match("/\[([^\]]*)\]/", $title, $line);

					//get the direction affected

						
					

					

					if(!array_key_exists($line[1], $result['subway'])){
						$result['subway'][$line[1]] = array();
						//array_push($result['subway'], $new_line);
					}
					if(!array_key_exists('planned work', $result['subway'][$line[1]])){
						$result['subway'][$line[1]]['planned work'] = array();
					}

					

						
					

					


					//$new_result = $result['subway'][$line[1]]['planned work'];


					$new_result['line'] = $line[1];	
					$new_result['direction'] = $direction;
					$new_result['title'] = $title;
					$new_result['start_time'] = date('H:i', strtotime($start_time));
					$new_result['end_time'] = $end_time;
					$new_result['date_'] = $date_set_new;
					$new_result['date'] = $datetime;
					$new_result['details'] = $clean_details;
					$new_result['affects_ada'] = $ada;

					//array_push($result, $new_result);
					array_push($result['subway'][$line[1]]['planned work'], $new_result);


					//echo "<strong>TITLE:</strong>$title<br/><strong>DATE:</strong> $datetime<br/><strong>DETAILS:</strong> $clean_details<br/><strong>Affects ADA:</strong> $ada"; 
//echo "<hr/>"; 
					//print_r($content_3[1]);
					//dd($details);
					
					

					
				}else if(preg_match("/^Delay\">/", $alert)){
					//echo "DELAYS";
					//var_dump($alert);

					$content = explode('<span class="DateStyle">', $alert, 2);
					//var_dump($content); //dfkhgkjdshfgkhldsflkgh

					$content_2 = explode('<br/><br/> ', $content[1], 2);
					//var_dump($content_2);

					//var_dump($content);
					//var_dump($content_3);

					$datetime = $content_2[0];
					$details = $content_2[1];


					$datetime = preg_replace('/\s\s+/', ' ', trim(strip_tags(preg_replace("/&nbsp;/", ' ', $datetime) )));
					$details = trim(strip_tags(preg_replace("/Allow additional travel time.|\n/", '', $details)));
					
					preg_match_all("/\[([^\]]*)\]/", $details, $line);

					

					foreach ($line[1] as $line) {

						if(!array_key_exists($line, $result['subway'])){
							$result['subway'][$line] = array();
							//array_push($result['subway'], $new_line);
						}

						if(!array_key_exists('delays', $result['subway'][$line])){
							$result['subway'][$line]['delays'] = array();
							
						}



						$new_result = [];


						//$new_result['line'] = $line[1];	 
						$new_result['date'] = str_replace("Posted: ", '', $datetime);
						$new_result['details'] = $details;

						//array_push($result, $new_result);

						//array_unique($result['subway'][$line]['delays'], SORT_REGULAR);

						if(!empty($result['subway'][$line]['delays'])){

							$duplicate = false;
							foreach ($result['subway'][$line]['delays'] as $key => $value) {
								//check if duplicate
								if($result['subway'][$line]['delays'][$key]['details'] == $details){
									$duplicate = true;
								}

							}

							if($duplicate == false){
								array_push($result['subway'][$line]['delays'], $new_result);
							}


						}else{
							array_push($result['subway'][$line]['delays'], $new_result);
						
						}

						


						
							

						

						//array_push($result['subway'][$line]['delays'], $new_result);
						

					}
					
					//var_dump($line[1]);
					//var_dump($datetime);
					
					//var_dump($details);

					//remove duplicates


					//search for what is between [] and insert in approriate line
					//manage duplicates
						//for every instance of [], insert in line

					// echo "DELAYS";
					// $content = explode('<span class="DateStyle">', $alert, 2);
					// 	//dd($content);
					// $content_2 = explode('</span><br/><br/>\n', $content[1], 2);

					

					//echo "TITLE: $content<br/> DETAILS: $content_2";
				}else{
					
					//echo "not planned wrk";
					//var_dump($alert);
					//echo "<br/>";
					//delays
				}
						
						//echo $title;
						//echo $info;
						

					}

					//var_dump($alert);

				
				


				

				//print_r($line);
			}
			
		}

		
		ksort($result['subway'], SORT_NATURAL);

		//remove duplicate delays

		foreach ($result['subway'] as $line) {
			
			$line['delays'] = [];
			$line['delays'] = array_map("unserialize", array_unique(array_map("serialize", $line['delays'])));
		}
		//dd($obj);
		//dd(date('H:i', strtotime("12 midnight")) );
		 return $result;
		

			
	}
}