<pre><?php

date_default_timezone_set('Asia/Calcutta');

$project = 'iksulapmt';
$authtoken = 'ef57228bf54d70f3ba88fb0f0e7f5cd1';
$happilyUnmarriedProjectId = 'e1bbda3d5fbf17921dad61706403a9e261dbcaf447b2f163';
$time = 'lastmonth';

function xml2array ( $xmlObject, $out = array () ) {
	foreach ( (array) $xmlObject as $index => $node ) {
		$out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;
	}
	return $out;
}

// set POST variables
$url = "https://projectsapi.zoho.com/portal/$project/api/private/xml/logs?authtoken=$authtoken";
$fields = array(
	'projId' => $happilyUnmarriedProjectId,
	'view' => $time
);

// url-ify the data for the POST
$fields_string = '';
foreach($fields as $key => $value) {
	$fields_string .= $key . '=' . $value . '&';
}
$fields_string = rtrim($fields_string, '&');

// open connection
$ch = curl_init();

// set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// execute post
$result = curl_exec($ch);

// close connection
curl_close($ch);

// get result
$response = xml2array(simplexml_load_string($result));
// print_r($response);

$accumulatedData = array();

foreach($response['result']['TimeLogDetails'] as $logObject) {
	
	$logEntry = xml2array($logObject->TimeLogDetail);
	print_r($logEntry);

	$logDate = explode(" ", $logEntry['logdate']);
	$logDate = strtotime($logDate[0]);
	$logTimeFormat = $logEntry['loghours'];							// 3.26
	$logTimeHours = floor($logTimeFormat);							// 3
	$logTimeMinutes = $logTimeFormat - $logTimeHours;				// 0.26
	$logTime = ($logTimeHours * 60) + ($logTimeMinutes * 100);		// (3 * 60) + (0.26 * 100) = 206

	if($accumulatedData[$logDate]) {
		$accumulatedData[$logDate] = $accumulatedData[$logDate] + $logTime;
	} else {
		$accumulatedData[$logDate] = $logTime;
	}

}

// print_r($accumulatedData);
// echo "Hours: " . date('H:i', mktime(0, array_sum($accumulatedData))) . "<br/>";;
// echo "Days: " . count($accumulatedData) . "<br/>";

$totalHoursInPeriod = array_sum($accumulatedData);
$totalDaysInPeriod = count($accumulatedData);

/* foreach ($accumulatedData as $timestamp => $logTime) {
	echo date('H:i', mktime(0, $logTime)) . "<br/>";
} */

?></pre>
