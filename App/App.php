<?php
// load dependencies
define('BASE_DIR', dirname(__FILE__) . '/');
require(BASE_DIR . 'functions.php');
require(BASE_DIR . 'WeatherApi/WeatherApi.php');

// define app vars
define('BASE_URL', baseUrl());