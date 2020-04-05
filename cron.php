<?php
require(dirname(__FILE__) . '/App/App.php');
$message = 'Success!';
try{
	$weatherData = weatherDataGet(
		$config->weatherAPI,
		[
			'OR',
			'WA',
			'ID',
		]
	);
	weatherDataCache($weatherData);
}catch(Exception $e){
	$message = 'Error!';
}
require(TEMPLATE_HEADER);
?>
<body id="cron">
	<p><?= $message; ?></p>
<?php require(TEMPLATE_FOOTER); ?>
