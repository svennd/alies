<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tests extends Admin_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
	}

	// test if the login works :)
	// if this fails it would return a html redirect page
	public function test_connection_covetrus(string $user, string $pasw)
	{
		$url = "https://" . $user . ":". $pasw . "@online.medilab.be/dokter/stalen.json?days=7";
		$json_response = $this->req_curl_json($url);

		echo (!is_null(json_decode($json_response, true))) ? "OK" : "FAILED";
	}

	// wrapper around some curl setup
	// may require a specific php extension : php-curl
	private function req_curl_json(string $url)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		$json_response = curl_exec($curl);
		curl_close($curl);

		return $json_response;
	}

}
