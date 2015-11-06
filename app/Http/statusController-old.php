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

		$data = file_get_contents('serviceStatus3.txt');
		$obj = simplexml_load_string($data) or die("Error: Cannot create object");
		//print_r($obj);
		$result = array();
		//dd($obj);
		foreach($obj->subway->line as $line){
			if ($line->status != "GOOD SERVICE"){
				//only show lines with issues
				$status = $line->status;
				$text = $line->text;

				//get the title
				//echo $text;
				
					
					

					$alerts = explode('\n\n', $text);
					// $str = 'string';
					// $alerts = preg_split('\n\n', $text, null, PREG_SPLIT_NO_EMPTY);
					//$alerts = preg_split( "/ ('<span class=\"TitlePlannedWork\" >Planned Work</span>'|'<span class=\"TitleDelay\">Delays</span>') /", $text );
					//$alerts = array_splice($alerts, 1);

					foreach ($alerts as $alert) {
						// $start = strpos($alert, '<a>');
						// $end = strpos($alert, '</a>', $start);
						
											

						// $title = substr($alert, $start, $end-$start+4);
						// $title = html_entity_decode(strip_tags($title));

						//echo $title . "<hr/>";
						$alert = trim(preg_replace('/\s\s+/', ' ', $alert));
						//dd(strip_tags($alert));

						
						//if planned work
						if (preg_match("/^<span class=\"TitlePlannedWork\" >/", $alert)){
							//planned work

							$dom = new \DOMDocument;
				@$dom->loadHTML($text);
				$books = $dom->getElementsByTagName('b');
				    print_r( $books->item(0)->nodeValue );
				    echo "<hr/>";
				

							$alert = preg_split("/\n/", $alert, 5); //type of alert
							
							
							$alert[0] = trim(strip_tags($alert[0])); //title

							

							

							$alert[1] = strip_tags($alert[1]);
							$alert[2] = strip_tags($alert[2]); //time of day, time, days, dates
							$alert[2] = explode(', ', $alert[2]);
							preg_match("/\[([^\]]*)\]/", $alert[1], $matches);
							$alert[3] = $matches[1];
							//$alert[3] = strip_tags($alert[3]);

							//clean up the times
							
							if(preg_match("/beginning/", $alert[2][1])){
								$alert[2][1] = preg_replace('/beginning\s/', '', $alert[2][1]);

								$start_time_carbon = Carbon::parse($alert[2][1]);
								$start_time = $start_time_carbon->toTimeString();
								$end_time = '';

							}else{
								$alert[2][1] = explode(' to ', $alert[2][1]);

								if(preg_match("/midnight^/", $alert[2][1][0])){
									$alert[2][1][0] = preg_replace('/midnight/', 'AM', $alert[2][1][0]);

									$start_time_carbon = Carbon::parse($alert[2][1][0]);
									$start_time = $start_time_carbon->toTimeString();
									$end_time = "";
									
								}else if(preg_match("/midnight^/", $alert[2][1][1])){
									$alert[2][1][1] = preg_replace('/midnight/', 'AM', $alert[2][1][1]);

									$end_time_carbon = Carbon::parse($alert[2][1][1]);
									$end_time = $end_time_carbon->toTimeString();
								}else{
									$start_time_carbon = Carbon::parse($alert[2][1][0]);
									$start_time = $start_time_carbon->toTimeString();
									

									$end_time_carbon = Carbon::parse($alert[2][1][1]);
									$end_time = $end_time_carbon->toTimeString();
								}


								
							}

						



							


							//clean up days
							$days = array();
							if (preg_match("/to/", $alert[2][2])) {
								//it is a range of days
								//lets separate them into ['start'] and ['end'] keys

								$alert[2][2] = explode(' to ', $alert[2][2]);
								$day_start = $alert[2][2][0];
								$day_end = $alert[2][2][1];

								$day_start = Carbon::parse($day_start);
								$day_start = $day_start->format('l');

								$day_end = Carbon::parse($day_end);
								$day_end = $day_end->format('l');

								$days['start'] = $day_start;
								$days['end'] = $day_end;
							}else{
								//it is just one day
								//let it be
								$days = $alert[2][2];
							}

							//clean up dates
							$dates_set = array();
							if (preg_match("/ &#149; /", $alert[2][3])) {
								//there are multiple sets of start and end dates

								$dates_separated = explode(' &#149; ', $alert[2][3]);	

								foreach ($dates_separated as $key => $dates) {
									
									if (preg_match("/ - /", $dates)) {
										//there is a range

										$dates = explode(' - ', $dates);
										
										

										$dates_carbon = Carbon::parse($dates[0]);

										//if the day is in the past, must be the next year


										$date_start = $dates_carbon->toDateString();

										$dates[0] = $date_start;
										
										// since the end date doesnt have a month, 
										// we are gonna use the one from the start date

										$month = $dates_carbon->month;

										$date_end_carbon = Carbon::now();

										$date_end_carbon->month = $month;
										$date_end_carbon->day = $dates[1];

										if($date_end_carbon->lt(Carbon::now())){
											$date_end_carbon->addYear();
										}

										$dates_set[$key]['start'] = $dates[0];
										$dates_set[$key]['end'] = $date_end_carbon->toDateString();

										

									}else{
										//just one day

										$dates_carbon = Carbon::parse($alert[2][3]);

										//if the day is in the past, must be the next year

										if($dates_carbon->lt(Carbon::now()->setTime(0, 0, 0) ) ){
											$dates_carbon->addYear();
										}

										$date_start = $dates_carbon->toDateString();

										$dates_set = $date_start;
									}
								}

							}else{

								$dates = $alert[2][3];

								if (preg_match("/ - /", $dates)) {
									//there is a range

									$dates = explode(' - ', $dates);



									$dates_carbon = Carbon::parse($dates[0]);

									//if the day is in the past, must be the next year

									

									$date_start = $dates_carbon->toDateString();

									$dates[0] = $date_start;
									
									// since the end date doesnt have a month, 
									// we are gonna use the one from the start date

									$month = $dates_carbon->month;

									$date_end_carbon = Carbon::now();

									$date_end_carbon->month = $month;
									$date_end_carbon->day = $dates[1];

									if($date_end_carbon->lt(Carbon::now())){
										$date_end_carbon->addYear();
									}


									$dates_set['start'] = $dates[0];
									$dates_set['end'] = $date_end_carbon->toDateString();

									

								}else{
									//just one day
									$dates_carbon = Carbon::parse($alert[2][3]);

									//if the day is in the past, must be the next year

									if($dates_carbon->lt(Carbon::now()->setTime(0, 0, 0) ) ){
										$dates_carbon->addYear();
									}

									$date_start = $dates_carbon->toDateString();

									$dates_set = $date_start;
								}
							}




							$new_alert = array();
							



							$new_alert['line'] = $alert[3];
							$new_alert['type'] = $alert[0];
							$new_alert['title'] = $alert[1];
							$new_alert['details'] = $alert[4];
							$new_alert['date'] = [];
								$new_alert['date']['time_of_day'] = $alert[2][0];
								$new_alert['date']['days'] = $days;
								$new_alert['date']['dates'] = [];
									$new_alert['date']['dates'] = $dates_set;
								$new_alert['date']['time'] = [];
									$new_alert['date']['time']['start'] = $start_time;
									$new_alert['date']['time']['end'] = $end_time;
								
							
							

						

							array_push($result, $new_alert);
							

						}else{
							echo "not planned work";
							echo "<br/>";
							//delays
						}
						
						//echo $title;
						//echo $info;
						

					}

					//var_dump($alert);

				
				


				

				//print_r($line);
			}
			
		}

		

		

		dd($result);	
	}
}