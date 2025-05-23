<?php

/**
 * Class ProxyServerController
 */

use GuzzleHttp\Client;
use Video;

class ProxyServerController extends BaseController
{
	public function __construct()
	{
		Log::useFiles('/var/log/laravel/proxy.log');
	}

	public function aps_server_request_sending($payload = '', $amzn_debug_mode = false)
	{
		$url = "https://xyz.amazon-adsystem.com/e/mdtb/ads";
		if ($amzn_debug_mode) {
			$url = "https://xyz.amazon-adsystem.com/e/mdtb/ads?amzn_debug_mode=1";
		}
		Log::info("\n APS API URL: $url " . date('Y-m-d H:i:s'));

		if (empty($payload)) {
			$payload = file_get_contents('php://input');
		}
		Log::info("\n APS Payloads: " . $payload . date('Y-m-d H:i:s'));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type:application/json',
			'Connection:keep-alive',
		));
		$result = curl_exec($ch);
		Log::info("\n APS Response: " . $result . date('Y-m-d H:i:s'));

		curl_close($ch);
		return $result;
	}

	public function aps_server_request_sending_request($show_only_end_points = 0, $ip = 0, $channel_id, $custom_server_version)
	{
		if ($show_only_end_points == 0) {
			header('Content-Type: application/xml');
			header('Access-Control-Allow-Origin: *');
		}
		if (empty($ip)) {
			$ip = Request::ip();
		}
		$ua = urldecode(Request::get('ua'));
		$os = urldecode(Request::get('os'));
		$osv = urldecode(Request::get('osv'));
		$model = urldecode(Request::get('model'));
		$make = urldecode(Request::get('make'));
		$slot_id = urldecode(Request::get('slot_id'));
		$app_id = urldecode(Request::get('app_id'));
		$reqType = urldecode(Request::get('req_type'));
		$content_type = strtolower(trim(urldecode(Request::get('content_type'))));
		$slot_type = strtolower(trim(urldecode(Request::get('slot_type'))));
		$amzn_debug_mode = urldecode(Request::get('amzn_debug_mode'));
		$content_id = urldecode(Request::get('content_id'));
		$content_rating = urldecode(Request::get('rating'));
		$genre = urldecode(Request::get('content_genre'));
		$category = urldecode(Request::get('category'));
		$content_len = urldecode(Request::get('content_duration'));
		$ifa = urldecode(Request::get('did'));
		$domain = urldecode(Request::get('domain'));
		$us_privacy = urldecode(Request::get('us_privacy'));
		$dnt = urldecode(Request::get('dnt'));
		$device_height = urldecode(Request::get('device_height'));
		$device_width = urldecode(Request::get('device_width'));
		$connection_type = urldecode(Request::get('connection_type'));
		$language = urldecode(Request::get('language'));
		$geo_lat = urldecode(Request::get('geo_lat'));
		$geo_lon = urldecode(Request::get('geo_lon'));
		$geo_country = urldecode(Request::get('geo_country'));
		$imp_id = urldecode(Request::get('imp_id'));
		$imp_video_height = urldecode(Request::get('imp_video_height'));
		$imp_video_width = urldecode(Request::get('imp_video_width'));
		$cb = urldecode(Request::get('cb'));
		$content_title = urldecode(Request::get('content_title'));
		$content_livestream = urldecode(Request::get('content_livestream'));
		$_sstz = urldecode(Request::get('_sstz'));
		$custom_app_version = urldecode(Request::get('custom_app_version'));
		$custom_app_version = empty($custom_app_version) ? '' : $custom_app_version;

		//geting geo details 
		$geo = urldecode(Request::get('geoip_country_code'));
		$ua = empty($ua) || $ua == '{ua}' ? $_SERVER['HTTP_USER_AGENT'] : $ua;

		//content ID
		$content_id = empty($content_id) || $content_id == '{video_id}' ? rand(50000, 110000) : $content_id;
		//content viewer rating (how can view this content)
		$content_rating = empty($content_rating) || $content_rating == '{video_rating}' ? 'TV-PG' : $content_rating;
		//content rating
		$genre = empty($genre) || $genre == '{content_genre}' ? 'Action,Adventure' : $genre;
		//content category
		$category = empty($category) || $category == '{category}' ? 'Action,Adventure' : $category;
		//content length
		$content_len = empty($content_len) || $content_len == '{content_duration}' ? '1500' : $content_len;
		//Identifier for Advertising (IFA) 'a0d5cd20-68ec-4f45-bdd0-673aaedc88d2'
		$ifa = empty($ifa) || $ifa == '{device_ifa}' ? $this->random_ifa_id() : $ifa;
		//domain
		$domain = empty($domain) ? '1stud.io' : $domain;
		//us_privacy
		$us_privacy = empty($us_privacy) || $us_privacy == '{us_privacy}' ? '1---' : $us_privacy;
		//device DNT(Do Not Track)
		$dnt = $dnt == 1 && $dnt != '{dnt}' ? '1' : '0';
		//device height
		$device_height = empty($device_height) || $device_height == '{device_height}' ? '720' : $device_height;
		//device width
		$device_width = empty($device_width) || $device_width == '{device_width}' ? '1080' : $device_width;
		//device connection type
		$connection_type = empty($connection_type) || $connection_type == '{connection_type}' ? '2' : $connection_type;
		//device lanauage
		$language = empty($language) || $language == '{language}' ? 'en' : $language;
		//geo  Latitude
		$geo_lat = empty($geo_lat) ? $geo['lat'] : $geo_lat;
		//geo  longitude
		$geo_lon = empty($geo_lon) ? $geo['lon'] : $geo_lon;
		//geo  country
		$geo_country = empty($geo_country) ? $geo['country'] : $geo_country;
		//imp  id
		$imp_id = empty($imp_id) ? '1' : $imp_id;
		//imp  video height
		$imp_video_height = empty($imp_video_height) || $imp_video_height == '{player_height}' ? '640' : $imp_video_height;
		//imp  video width
		$imp_video_width = empty($imp_video_width) || $imp_video_width == '{player_width}' ? '480' : $imp_video_width;

		$app_info = $this->get_app_info($channel_id);
		$app_id = $app_info['app_id'];
		$app_url = $app_info['app_url'];
		$bundle_name = $app_info['bundle_name'];
		$bundle_id =  $app_info['bundle_id'];
		$channe_name =  $app_info['channe_name'];

		//Operating system
		$os = empty($os) || $os == '{os}' ? 'rokuos9' : $os;
		//Operating system version
		$osv = empty($osv) || $osv == '{osv}' ? '9.10' : $osv;
		//device model
		$model = empty($model) || $model == '{model}' ? '480X - Roku Ultra' : $model;
		//device make
		$make = empty($make) || $make == '{make}' ? 'roku' : $make;

		$amzn_debug_mode = $amzn_debug_mode == 1 ? true : false;
		$content_type = $content_type == 'live' && $content_type != '{content_type}' ? 'live' : 'vod';
		$slot_type = $slot_type == 'preroll' && $slot_type != '{slot_type}' ? 'preroll' : 'midroll';
		if ($content_type == 'live') {
			$slot_id = 'ad41a232-1452-416a-8d7c-d614192baeb7';
		} elseif (($content_type == 'vod') && ($slot_type == 'preroll')) {
			$slot_id = 'b99345d9-249c-4d62-9f74-474a09d604ad';
		} else {
			$slot_id = '1380fa76-5f79-4a3b-b505-9bbd329cffff';
		}
		// Preparing payload for the APS
		$payload = '
		{
			
			"app": {
			"bundle": "' . $bundle_id . '",
			"domain": "' . $domain . '",
			"id": "' . $app_id . '",
			"name": "' . $bundle_name . '",
			"content": {
			"id": "' . $content_id . '",
			"contentrating": "' . $content_rating . '",
			"genre": "' . $genre . '",
			"channel": "' . $bundle_name . '",
			"len": "' . $content_len . '"
			}
			},
			"regs": {
			"ext": {
			"us_privacy": "' . $us_privacy . '"
			}
			},
			"device": {
			"dnt": ' . $dnt . ',
			"h": ' . $device_height . ',
			"w": ' . $device_width . ',
			"ifa": "' . $ifa . '",
			"ip": "' . $ip . '",
			"connectiontype": ' . $connection_type . ',
			"language": "' . $language . '",
			"make": "' . $make . '",
			"model": "' . $model . '",
			"os": "' . $os . '",
			"osv": "' . $osv . '",
			"ua": "' . $ua . '",
			"geo": {
			"lat": ' . $geo_lat . ',
			"lon": ' . $geo_lon . ',
			"country": "' . $geo_country . '"
			}
			},
			"id": "' . time() . '",
			"imp": [{
			"id": "' . $imp_id . '",
			"video": {
			"h": ' . $imp_video_height . ',
			"w": ' . $imp_video_width . ',
			"ext": {
			"slotId": "' . $slot_id . '"
			}
			}
			}]
			}

		';
		$response = $this->aps_server_request_sending($payload, $amzn_debug_mode);
		$res = json_decode($response);
		if (empty($res)) {
			return  'false';
		}
		if (!empty($res->ext)) {
			$keyValuePair = $this->creating_key_values_pair($res);
			Log::info("\n Key  Value Pair: " . json_encode($keyValuePair, JSON_PRETTY_PRINT) . '  ' . date('Y-m-d H:i:s'));
		}

		if ($reqType == 'aps') {
			$this->send_key_values_to_aps($keyValuePair);
		} else {
			$us_privacy = '1---';
			return $this->send_key_values_to_spring_server($keyValuePair, $ip, $ua, $us_privacy, $app_id, $bundle_name, $ifa, $app_url, $bundle_id, $genre, $category, $cb, $channel_id, $channe_name, $_sstz, $content_title, $content_livestream, $content_rating, $show_only_end_points, $custom_app_version, $custom_server_version);
		}
	}

	function get_app_info($channel_id)
	{
		$app_info = [];
		switch ($channel_id) {
			case 459:
				$app_info = [
					'app_id' => 'aa273dc0dcec4e4684692ec7ab16ddef',
					'app_url' => 'https://channelstore.roku.com/details/6e91c3d25be3363bb47200afe413fded/free-movies-plus',
					'bundle_name' => 'free-movies-plus-roku',
					'bundle_id' => '588207',
					'channe_name' => 'Free_Movies_Plus_Roku',
					'os' => 'rokuos9',
					'osv' => '9.10',
					'model' => '480X - Roku Ultra',
					'make' => 'roku'
				];
				break;
			default:
				return false;
		}
		return $app_info;
	}

	function send_key_values_to_aps($key_values)
	{
		$end_point = 'https://c.amazon-adsystem.com/%%PATTERN:amznregion%%/e/mdtb/vast?brmId=%%PATTERN:amznbrmId%%&ps=[AMZNSLOTS_VALUE]';
		if (empty($key_values)) {
			return false;
		}
		$end_point = str_ireplace('%%PATTERN:amznregion%%', $key_values['amznregion'], $end_point);
		$end_point = str_ireplace('%%PATTERN:amznbrmId%%', $key_values['amznbrmId'], $end_point);
		$end_point = str_ireplace('[AMZNSLOTS_VALUE]', implode(',', $key_values['amznslots']), $end_point);

		Log::info("\n APS server end_point: " . $end_point . '  ' . date('Y-m-d H:i:s'));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $end_point);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/xml',
			'Accept: application/xml'
		));
		$result = curl_exec($ch);
		Log::info("\n APS server response: " . $result . '  ' . date('Y-m-d H:i:s'));
		if (empty($result)) {
			$xml_document = new \DOMDocument('1.0', "UTF-8");
			$xml_document->preserveWhiteSpace = false;
			$xml_document->formatOutput = true;
			$error = $xml_document->createElement('error');
			$error = $xml_document->appendChild($error);
			$error_code = $xml_document->createElement('code');
			$error_code = $error->appendChild($error_code);
			$message = $xml_document->createElement('message');
			$message = $error->appendChild($message);
			$error_code_value = $xml_document->createTextNode('500');
			$message_text = $xml_document->createTextNode('Empty Reponse form APS server');
			$error_code_value = $error_code->appendChild($error_code_value);
			$message_text = $message->appendChild($message_text);
			$xml = $xml_document->saveXML();
			flush();
			echo $xml;
			die();
		}
		curl_close($ch);
		echo $result;
		die();
	}

	function creating_key_values_pair($res)
	{
		$keyValuePair = [];
		$trackKeys = ['amznregion', 'amznbrmId', 'bid'];
		foreach ($res->ext  as $key => $value) {
			if (in_array($key, $trackKeys)) {
				foreach ($value as $val) {
					$keyValuePair[$key] = $val;
				}
			}
		}
		foreach ($res->seatbid  as $key => $value) {
			if (in_array($key, $trackKeys)) {
				foreach ($value as $bidKey => $bidValue) {
					foreach ($bidValue[0]->ext->targeting->amznslots as $slotValue) {
						$keyValuePair['amznslots'][] = $slotValue;
					}
				}
			}
		}
		return $keyValuePair;
	}

	function send_key_values_to_spring_server($key_values, $ip, $ua, $us_privacy, $app_id, $bundle_name, $ifa, $app_url, $bundle_id, $content_genre, $category, $cb, $channel_id,  $channe_name, $_sstz, $content_title, $content_livestream, $rating, $show_only_end_points, $custom_app_version, $custom_server_version)
	{
		$sp_id = $this->get_channel_spring_server_and_id($channel_id);
		$spring_server_url = 'https://tv.springserve.com/rt/' . $sp_id;
		$sping_params = '?w=1920&h=1080&cb={{ CACHEBUSTER }}&ip={{ IP }}&ua={{ USER_AGENT }}&app_bundle={{ APP_BUNDLE }}&app_name={{ APP_NAME }}&app_store_url={{ APP_STORE_URL }}&did={{ DEVICE_ID }}&us_privacy={{ US_PRIVACY }}&schain={{ SCHAIN }}&amznregion={{ AMZNREGION }}&amznbrmId={{ AMZNBRMID }}&ps={{ AMZNSLOTS }}&preroll=[PREROLL]&content_genre={{ content_genre }}&category={{ category }}&_sstz={{ _sstz }}&content_title={{ content_title }}&content_livestream={{ content_livestream }}&channe_name={{ channe_name }}&rating={{ rating }}&pod_max_dur=150&Language=en&lmt=ROKU_ADS_LIMIT_TRACKING&ic=IAB-5';

		$end_point = $spring_server_url . $sping_params;

		if (empty($key_values)) {
			$key_values['amznregion'] = '{{ AMZNREGION }}';
			$key_values['amznbrmId'] = '{{ AMZNBRMID }}';
			$key_values['amznslots'] = ['{{ AMZNSLOTS }}'];
		}
		if (empty($bundle_id)) {
			$bundle_id = '{{ APP_BUNDLE }}';
		}
		$end_point = str_ireplace('{{ AMZNREGION }}', $key_values['amznregion'], $end_point);
		$end_point = str_ireplace('{{ AMZNBRMID }}', $key_values['amznbrmId'], $end_point);
		$end_point = str_ireplace('{{ AMZNSLOTS }}', implode(',', $key_values['amznslots']), $end_point);
		$end_point = str_ireplace('{{ IP }}', $ip, $end_point);
		$end_point = str_ireplace('{{ USER_AGENT }}', rawurlencode($ua), $end_point);
		$end_point = str_ireplace('{{ US_PRIVACY }}', rawurlencode($us_privacy), $end_point);
		$end_point = str_ireplace('{{ APP_STORE_URL }}', urlencode($app_url), $end_point);
		$end_point = str_ireplace('{{ APP_BUNDLE }}', rawurlencode($bundle_id), $end_point);
		$end_point = str_ireplace('{{ APP_NAME }}',   rawurlencode($bundle_name), $end_point);
		$end_point = str_ireplace('{{ DEVICE_ID }}',  rawurlencode($ifa), $end_point);
		$end_point = str_ireplace('{{ content_genre }}',  rawurlencode($content_genre), $end_point);
		$end_point = str_ireplace('{{ category }}',  rawurlencode($category), $end_point);
		$end_point = str_ireplace('{{ CACHEBUSTER }}',  rawurlencode($cb), $end_point);
		$end_point = str_ireplace('{{ content_title }}',  rawurlencode($content_title), $end_point);
		$end_point = str_ireplace('{{ _sstz }}',  rawurlencode($_sstz), $end_point);
		$end_point = str_ireplace('{{ content_livestream }}',  rawurlencode($content_livestream), $end_point);
		$end_point = str_ireplace('{{ channe_name }}',  rawurlencode($channe_name), $end_point);
		$end_point = str_ireplace('{{ rating }}',  rawurlencode($rating), $end_point);
		Log::info("\n spring_server end_point: " . $end_point . '  ' . date('Y-m-d H:i:s'));
		if ($show_only_end_points == 1) {
			Log::info("\n spring_server end_point: " . $end_point . '  ' . date('Y-m-d H:i:s'));
			return $end_point . '&custom_app_version=' . rawurlencode($custom_app_version) . '&custom_server_version=' . rawurlencode($custom_server_version) . '&aps_response=1';
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $end_point);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/xml',
			'Accept: application/xml'
		));
		$result = curl_exec($ch);
		Log::info("\n spring_server response: " . $result . '  ' . date('Y-m-d H:i:s'));
		if (empty($result)) {
			$xml_document = new \DOMDocument('1.0', "UTF-8");
			$xml_document->preserveWhiteSpace = false;
			$xml_document->formatOutput = true;
			$error = $xml_document->createElement('error');
			$error = $xml_document->appendChild($error);
			$error_code = $xml_document->createElement('code');
			$error_code = $error->appendChild($error_code);
			$message = $xml_document->createElement('message');
			$message = $error->appendChild($message);
			$error_code_value = $xml_document->createTextNode('500');
			$message_text = $xml_document->createTextNode('Empty Reponse form spring server');
			$error_code_value = $error_code->appendChild($error_code_value);
			$message_text = $message->appendChild($message_text);
			$xml = $xml_document->saveXML();
			flush();
			echo $xml;
			die();
		}
		curl_close($ch);
		echo $result;
		die();
	}

	function get_channel_spring_server_and_id($channel_id)
	{
		switch ($channel_id) {
			case '459':
				$spring_server_id = '10092';
				break;
			case '460':
				$spring_server_id = '9699';
				break;
			case '461':
				$spring_server_id = '9925';
				break;
			default:
				$spring_server_id = '10092';
				break;
		}
		return $spring_server_id;
	}
}