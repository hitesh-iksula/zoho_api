<?php require_once('classes.php'); ?>
<?php require_once('users.php'); ?>

<?php

$client    = 'iksulapmt';
$time      = 'thisweek';
$timeLabel = array(
	'thisweek'   => 'This Week',
	'lastweek'   => 'Last Week',
	'thismonth'  => 'This Month',
	'lastmonth'  => 'Last Month',
	'yesterday'  => 'Yesterday'
);

$projects = array();

foreach($users as $user) {

	$authtoken = $user['authToken'];

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

	if($projectsData['result']['ProjectDetails']['ProjectDetail']) {

		$helper = new Formatting_Helper();
		$project = $projectsData['result']['ProjectDetails']['ProjectDetail'];

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

		if($logsData != 'No tasks found') {
			$project['tasks'] = $logsData;
			$project['user'] = $user;
			$projects[] = $project;
		}

	} else {

		foreach ($projectsData['result']['ProjectDetails'] as $index => $object) {
			$helper = new Formatting_Helper();
			$project = $helper->getArrayFromXmlObject($object->ProjectDetail);

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

			if($logsData != 'No tasks found') {
				$project['tasks'] = $logsData;
				$project['user'] = $user;
				$projects[] = $project;
			}

		}

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
						<h1><?php echo $project['project_name']; ?></h1>
					</div>
					<div class="pad_left sheet_type">
						<h2><?php echo 'Daily Status Report for '; ?><span><?php echo $timeLabel[$time]; ?></span></h2>
					</div>
					<div class="pad_left project_member">
						<h2><?php echo $project['user']['username']; ?></h2>
					</div>

					<?php $days = $project['tasks']; ?>
					<?php foreach($days as $tasks): ?>

						<div class="pad_left date_and_hours">
							<div class="date">Date: <span><?php echo $tasks['date']; ?></span></div>
							<div class="total_hrs">Total Hours of Work Done: <span><?php echo str_replace(".", ":", $tasks['total_loghours_perday']); ?></span></div>
						</div>

						<div class="task_list">
							<div class="task_row heading">
								<div class="task">Task</div>
								<div class="loghours">Hours</div>
							</div>
							<?php foreach($tasks['tasks'] as $task): ?>
								<div class="task_row">
									<div class="arrow_right"></div>
									<div class="task"><?php echo $task['task']; ?></div>
									<div class="loghours"><?php echo str_replace(".", ":", $task['loghours']); ?></div>
								</div>
							<?php endforeach; ?>
						</div>

					<?php endforeach; ?>
				
				</div>

			<?php endforeach; ?>

		</div>

	</body>

</html>