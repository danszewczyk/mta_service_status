<?php

Route::get('/', [
	'uses' => '\App\Http\Controllers\HomeController@index',
	'as' => 'home'
]);

Route::get('/{search}', [
	'uses' => '\App\Http\Controllers\HomeController@index',
	'as' => 'home'
]);

Route::get('stop/{stop_id}', [
	'uses' => '\App\Http\Controllers\HomeController@stop_info',
	'as' => 'stop_info'
]);

Route::get('bus/{bus_id}', [
	'uses' => '\App\Http\Controllers\HomeController@bus_info',
	'as' => 'bus_info'
]);

//mta.dev/status/R
//mta.dev/service/q58

Route::get('status/', [
	'uses' => '\App\Http\Controllers\StatusController@index',
	'as' => 'status.index'
]);

Route::get('status/{line}', [
	'uses' => '\App\Http\Controllers\StatusController@status',
	'as' => 'status'
]);