<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	Route::post('upload_poster_image_16_9', 'UploadBunnyController@upload_poster_image_16_9');

	Route::get('proxy-server', 'ProxyServerController@aps_server_request_sending');
	Route::get('send-request-proxy-server', 'ProxyServerController@aps_server_request_sending_request');
});
