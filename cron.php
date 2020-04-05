<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require(dirname(__FILE__) . '/App/App.php');
$message = 'Success!';
try{
	$weatherData = weatherDataGet([
		'OR',
		'WA',
		'ID',
	]);
	weatherDataCache($weatherData);
	// var_dump($weatherData);
}catch(Exception $e){
	// var_dump($e);
	$message = 'Error!';
}
require(TEMPLATE_HEADER);
?>
<body id="cron">
	<p><?= $message; ?></p>
<?php require(TEMPLATE_FOOTER); ?>
