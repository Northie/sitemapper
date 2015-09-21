<?php

class curl {

	private $client;
	private $options = array(
		\CURLOPT_RETURNTRANSFER=>true
	);

	public function __construct() {
		$this->client = curl_init();
	}

	public function post($url, $data = false, $headers = false) {

		curl_setopt($this->client, \CURLOPT_URL, $url);

		if (is_array($data)) {
			$query = http_build_query($data);

			curl_setopt($this->client, \CURLOPT_POST, count($data));
			curl_setopt($this->client, \CURLOPT_POSTFIELDS, $query);
		}

		if (is_array($headers)) {
			curl_setopt($this->client, \CURLOPT_HTTPHEADER, $headers);
		}


		foreach ($this->options as $opt=> $value) {
			curl_setopt($this->client, $opt, $value);
		}

		return curl_exec($this->client);
	}

	public function get($url, $data = false, $headers = false) {

		if (is_array($data)) {
			$query = http_build_query($data);
			if (strpos($url, '?') > -1) {
				$url.="&" . $query;
			} else {
				$url.="?" . $query;
			}
		}

		curl_setopt($this->client, \CURLOPT_URL, $url);
		curl_setopt($this->client, \CURLOPT_HTTPGET, true);

		if (is_array($headers)) {
			curl_setopt($this->client, \CURLOPT_HTTPHEADER, $headers);
		}

		foreach ($this->options as $opt=> $value) {
			curl_setopt($this->client, $opt, $value);
		}

		return curl_exec($this->client);
	}

	public function put($url, $data = false, $headers = false) {

		curl_setopt($this->client, \CURLOPT_URL, $url);
		curl_setopt($this->client, \CURLOPT_CUSTOMREQUEST, "PUT");

		if (is_array($data)) {
			$query = http_build_query($data);

			curl_setopt($this->client, \CURLOPT_POST, count($data));
			curl_setopt($this->client, \CURLOPT_POSTFIELDS, $query);
		}

		if (is_array($headers)) {
			curl_setopt($this->client, \CURLOPT_HTTPHEADER, $headers);
		}


		foreach ($this->options as $opt=> $value) {
			curl_setopt($this->client, $opt, $value);
		}

		return curl_exec($this->client);
	}

	public function delete($url, $data = false, $headers = false) {

		curl_setopt($this->client, \CURLOPT_URL, $url);
		curl_setopt($this->client, \CURLOPT_CUSTOMREQUEST, "DELETE");

		if (is_array($data)) {
			$query = http_build_query($data);

			curl_setopt($this->client, \CURLOPT_POST, count($data));
			curl_setopt($this->client, \CURLOPT_POSTFIELDS, $query);
		}

		if (is_array($headers)) {
			curl_setopt($this->client, \CURLOPT_HTTPHEADER, $headers);
		}


		foreach ($this->options as $opt=> $value) {
			curl_setopt($this->client, $opt, $value);
		}

		return curl_exec($this->client);
	}

	//map required fucntions to http verb methods

	public function create() {
		return call_user_func_array(array($this, 'post'), func_get_args());
	}

	public function read() {
		return call_user_func_array(array($this, 'get'), func_get_args());
	}

	public function update() {
		return call_user_func_array(array($this, 'put'), func_get_args());
	}

	/*
	  public function delete() {	//already defined - common name

	  }
	 */

	public function getAdapter() {
		return $this->client;
	}

	public function __destruct() {
		curl_close($this->client);
	}

}
