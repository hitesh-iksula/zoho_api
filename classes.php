<?php

date_default_timezone_set('Asia/Calcutta');

/*
 * 
 * 
 * Base class for making API calls to Zoho Projects
 * 
 * 
 */
class Zoho_API_Call {

	// class variables
	private $_url = '';
	private $_params = '';
	private $_paramString = '';
	private $_ch;

	private $_client = '';
	private $_projectId = '';
	private $_authToken = '';
	private $_timePeriod = '';
	private $_apiType = '';

	// set client
	public function setClient($client) {
		$this->_client = $client;
	}

	// set project id
	public function setProjectId($projectId) {
		$this->_projectId = $projectId;
	}

	// set authentication token for user
	public function setAuthToken($authToken) {
		$this->_authToken = $authToken;
	}

	// set API type
	public function setApiType($apiType) {
		$this->_apiType = $apiType;
	}

	// set time period
	public function setTimePeriod($timePeriod) {
		$this->_timePeriod = $timePeriod;
	}

	// set API parameters
	public function setParams($params) {
		$this->_params = $params;
		$this->generateParamString();
	}

	// set final URL after acquiring all parameters
	public function setUrl() {

		$url = "https://projectsapi.zoho.com/";
		$url .= "portal/" . $this->_client . "/api/private/xml/";
		$url .= $this->_apiType . "?";
		$url .= "authtoken=" . $this->_authToken;

		$this->_url = $url;

	}

	// generates serialized parameter string from parameter array
	public function generateParamString() {

		$fields_string = '';
		foreach($this->_params as $key => $value) {
			$fields_string .= $key . '=' . $value . '&';
		}
		$this->_paramString = rtrim($fields_string, '&');

	}

	// make CURL request
	public function executeCurl() {

		// set URL
		$this->setUrl();

		// open connection
		$this->_ch = curl_init();

		// set the url, number of POST vars, POST data
		curl_setopt($this->_ch, CURLOPT_URL, $this->_url);
		curl_setopt($this->_ch, CURLOPT_POST, 1);
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $this->_paramString);
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);

		// execute post
		$result = curl_exec($this->_ch);

		// close connection
		curl_close($this->_ch);
		return $result;

	}

	// wrapper function for getting response as an array via XML
	public function getResponse() {

		$helper = new Formatting_Helper();
		$response = $helper->getArrayFromXmlObject(simplexml_load_string($this->executeCurl(), null, LIBXML_NOCDATA));
		return $response;

	}

}

/*
 * 
 * 
 * Class for making "Task Logs" API call to Zoho Projects
 * 
 * 
 */
class Zoho_Log_Retriever extends Zoho_API_Call {
	
	// Returns raw API call response
	public function getResponse() {
		return parent::getResponse();
	}

	// Returns formatted response making use of helper internally
	public function getFormattedResponse() {
		
		$response = $this->getResponse();
		/*echo "<pre>"; print_r($response); exit;*/
		$helper = new Formatting_Helper();
		$accumulatedData = $helper->formatLogResponse($response);
		return $accumulatedData;

	}

}

/*
 * 
 * 
 * Helper class to return:
 * 
 * Formatted / filtered arrays,
 * Return Array from XML object & so on
 * 
 * 
 */
class Formatting_Helper {

	// returns a data object with custom formatting for Logs
	public function formatLogResponse($response) {

		if($response['error']) {
			return $response['error']['message'];
		}
		if(empty($response['result'])) {
			return 'No tasks found';
		}

		$accumulatedData = array();
		$i = 0;

		foreach($response['result']['TimeLogDetails'] as $logObject) {
			
			$logEntry = $this->getArrayFromXmlObject($logObject->TimeLogDetail);

			// logdate is in "Date Time" format, only Date is required
			$logDateRaw = explode(" ", $logEntry['logdate']);
			$logDate = strtotime($logDateRaw[0]);

			$logHours = $logEntry['loghours'];

			if($logEntry['loghours'] > 0) {
				$accumulatedData[$logDate]['date'] = $logDateRaw[0];
				$accumulatedData[$logDate]['total_loghours_perday'] = $logEntry['total_loghours_perday'];
				$accumulatedData[$logDate]['tasks'][$i]['task'] = htmlspecialchars_decode($logEntry['task']);
				$accumulatedData[$logDate]['tasks'][$i]['loghours'] = $logEntry['loghours'];
			}

			$i++;

		}

		return $accumulatedData;

	}

	// returns array of given XML object
	public function getArrayFromXmlObject($xmlObject) {

		$out = array();
		foreach ((array)$xmlObject as $index => $node) {
			$out[$index] = (is_object($node)) ? $this->getArrayFromXmlObject($node) : $node;
		}
		return $out;

	}

	// add all values in hour.minute format in array and return value in hour.minute format
	// maybe this is a useless function for logs
	public function addHours($values) {
		$accumulant = 0;
		foreach($values as $value) {
			$hours = floor($value);
			$minutes = $value - $hours;
			$totalMinutes = ($hours * 60) + ($minutes * 100);
			$accumulant = $accumulant + $totalMinutes;
		}
		$returnValue = floor( $accumulant / 60 ) . "." . ( $accumulant % 60 );
		return $returnValue;
	}

}
