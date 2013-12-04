<?php require_once('classes.php'); ?>

<?php

$client    = 'iksulapmt';
$time      = 'yesterday';

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

<?php

$projects = array();

foreach ($projectsData['result']['ProjectDetails'] as $index => $object) {
	$helper = new Formatting_Helper();
	$project = $helper->getArrayFromXmlObject($object->ProjectDetail);

	// echo "<h2>" . $project['project_name'] . "</h2>";
	// print_r($project);

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

	// print_r($logsData);
	if($logsData != 'No tasks found') {
		$project['tasks'] = current($logsData);
		$projects[] = $project;
	}

}

?>

<!doctype html>
<html>
	<head>
		<link rel="stylesheet" href="style.css"/>
		<link href='http://fonts.googleapis.com/css?family=Raleway:400,300,600' rel='stylesheet' type='text/css'>
	</head>
	<body>

		<div class="projects">

			<?php foreach($projects as $project): ?>

				<div class="project">
					
					<div class="pad_left project_name">
						<h1><?php echo $project['project_name'] ?></h1>
					</div>
					<div class="pad_left project_member">
						<h2><?php echo 'Hitesh Pachpor' ?></h2>
					</div>

					<?php $tasks = $project['tasks']; ?>
					<div class="pad_left date_and_hours">
						<div class="date">Date: <span><?php echo $tasks['date'] ?></span></div>
						<div class="total_hrs">Total Hours of Work Done: <span><?php echo $tasks['total_loghours_perday'] ?></span></div>
					</div>

					<div class="task_list">
						<div class="task_row heading">
							<div class="task">Task</div>
							<div class="loghours">Hours</div>
						</div>
						<?php foreach($tasks['tasks'] as $task): ?>
							<div class="task_row">
								<div class="arrow_right"></div>
								<div class="task"><?php echo $task['task'] ?></div>
								<div class="loghours"><?php echo $task['loghours'] ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				
				</div>

			<?php endforeach; ?>

		</div>

	</body>
</html>