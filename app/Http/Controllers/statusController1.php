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
		
	$result = array();
		
	foreach($obj->subway->line as $line){
	 	if ($line->status != "GOOD SERVICE"){
			//only show lines with issues
			$status = $line->status;
			$text = $line->text;	

			$alerts = explode('\n\n', $text);

			foreach ($alerts as $alert) {

				$alert = trim(preg_replace('/\s\s+/', ' ', $alert));
							
				//if planned work
				if (preg_match("/^<span class=\"TitlePlannedWork\" >/", $alert)){

					
					preg_match("/\<b\>(.*)/", $alert, $goto_url);

					$content = explode('<b>', $alert, 2);
					//var_dump($content); //dfkhgkjdshfgkhldsflkgh

					$content_2 = explode('</b>', $content[1], 2);
					//var_dump($content_2);

					$content_3 = explode('<br>', $content_2[1], 2);
					//var_dump($content);
					//var_dump($content_3);

					$title = $content_2[0];
					$title = preg_replace('/[\n]/', '<br/>', trim(strip_tags(preg_replace('/\<br clear=left\>/', ' ', $title))));
					$datetime = trim(strip_tags($content_3[0]));
					$details = $content_3[1];
					echo "$title<br/>$datetime<br/>$details";

					echo "<hr/>"; 

				

							
						



							

							

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

		

		

		dd($obj);	
	}
}