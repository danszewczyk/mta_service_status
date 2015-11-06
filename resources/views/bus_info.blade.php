@extends('templates.default')

@section('content')
	<h3>Bus Info</h3>
	<h4>{{ $bus->PublishedLineName }}
	@if ($is_limited == true) 
		Limited
	@endif
	 to {{ $bus->DestinationName }} (#{{ substr($bus->VehicleRef, -4) }})</h4>


	

	<img src="https://maps.googleapis.com/maps/api/staticmap?center={{ $bus->VehicleLocation->Latitude }},{{ $bus->VehicleLocation->Longitude }}&markers={{ $bus->VehicleLocation->Latitude }},{{ $bus->VehicleLocation->Longitude }}&zoom=17&size=600x300&maptype=roadmap
&key=AIzaSyBAfxJdn7tsFL0sLG39CUM3LwGmQG5eXXk" />

	<h4>Feedback</h4>
	<p>Occupancy: <a href="#">Empty</a> | <a href="#">Moderate</a> | <a href="#">Full</a></p>
	<p>Cleanliness: <a href="#">Clean</a> | <a href="#">Moderate</a> | <a href="#">Dirty</a></p>
	<p>Driving: <a href="#">1</a> | <a href="#">2</a> | <a href="#">3</a> | <a href="#">4</a> | <a href="#">5</a></p>
	<p>Line: <a href="#">Short</a> | <a href="#">Moderate</a> | <a href="#">Long</a></p>


	{{ var_dump($bus) }}

@stop