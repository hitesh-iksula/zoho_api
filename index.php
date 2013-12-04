<pre>

<?php require_once('classes.php'); ?>

<?php

$client    = 'iksulapmt';
$time      = 'lastweek';

// Hitesh Pachpor & Happily Unmarried
// $projectId = 'e1bbda3d5fbf17921dad61706403a9e261dbcaf447b2f163';
$authtoken = 'ef57228bf54d70f3ba88fb0f0e7f5cd1';

$params = array(
	'auditIndex' => '1',
	'range'      => '60',
	'status'     => 'active'
);

$projectsObject = new Zoho_API_Call();

$projectsObject->setClient($client);
$projectsObject->setAuthToken($authtoken);
$projectsObject->setApiType('projects');
$projectsObject->setParams($params);

$projectsData = $projectsObject->getResponse();

?>

<?php // print_r($projectsData); ?>

<?php

foreach ($projectsData['result']['ProjectDetails'] as $index => $object) {
	$helper = new Formatting_Helper();
	$project = $helper->getArrayFromXmlObject($object->ProjectDetail);

	echo "<h2>" . $project['project_name'] . "</h2>";

	$projectId = $project['project_id'];
	$params = array(
		'projId' => $projectId,
		'view' => $time
	);

	$logsObject = new Zoho_Log_Retriever();

	$logsObject->setClient($client);
	$logsObject->setProjectId($projectId); // specific
	$logsObject->setAuthToken($authtoken);
	$logsObject->setApiType('logs');
	$logsObject->setTimePeriod($time); // specific
	$logsObject->setParams($params);

	$logsData = $logsObject->getFormattedResponse();

	print_r($logsData);

}

?>

<?php

// exit;
// Mihir Bhende & SportXs
/*$projectId = 'e1bbda3d5fbf17921dad61706403a9e2c4cb5e2f0cbd7495';
$authtoken = 'c6996e7813bdd9dc8f337ab8b21e9b71';*/

// Satyendra Mishra & SonicSense
/*$projectId = 'e1bbda3d5fbf179216c156d53d3ca05557c6a6bbf93694ff';
$authtoken = '8565da793c5af305635ffa77200bb8d8';*/

/*$params = array(
	'projId' => $projectId,
	'view' => $time
);

$logsObject = new Zoho_Log_Retriever();

$logsObject->setClient($client);
$logsObject->setProjectId($projectId); // specific
$logsObject->setAuthToken($authtoken);
$logsObject->setApiType('logs');
$logsObject->setTimePeriod($time); // specific
$logsObject->setParams($params);

$logsData = $logsObject->getFormattedResponse();*/

?>

<!-- <pre><?php // print_r($logsData); ?></pre> -->
