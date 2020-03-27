<?php
// load dependencies
define('DIR_BASE', dirname(dirname(__FILE__)) . '/');
define('DIR_APP', DIR_BASE . 'app/');
require(DIR_APP . 'functions.php');
require(DIR_APP . 'WeatherApi/WeatherApi.php');

// define app vars
define('DIR_DATA', DIR_BASE		. 'data/');

define('URL_BASE', baseUrl());
define('URL_IMG', baseUrl()		. 'img/');
define('URL_CSS', baseUrl()		. 'css/');
define('URL_LESS', baseUrl()	. 'less/');
define('URL_JS', baseUrl()		. 'js/');

// define('CACHE_WEATHER', true);