@extends('templates.default')

@section('content')
	<h3>Stop Info</h3>

	<p>Which bus are you waiting for?</p>
	<ul>
@foreach($bus_routes as $bus_route)
		<li>{{ $bus_route }}</li>
@endforeach
	</ul>

<ul>
@foreach($buses as $bus)
	<li>{{ $bus->MonitoredVehicleJourney->PublishedLineName }} to {{ $bus->MonitoredVehicleJourney->DestinationName }} <i>{{$bus->MonitoredVehicleJourney->VehicleRef}} is {{ $bus->MonitoredVehicleJourney->MonitoredCall->Extensions->Distances->StopsFromCall}} stops away</i> <a href="{{ route('bus_info', substr($bus->MonitoredVehicleJourney->VehicleRef, -4)) }}">Track</a> </li>
@endforeach
</ul>



@stop