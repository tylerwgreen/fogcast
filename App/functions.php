<?php
function baseUrl(){
	return 'http://' . $_SERVER['SERVER_NAME'] . '/';
}

function weatherDataGet(array $stateZonesCombined){
	$weatherData = array();
	foreach($stateZonesCombined as $stateZone){
		$zones = new Zones($stateZone);
		foreach($zones->zones as $zoneID => $zone){
			$forecast = $zone->getForecast();
			$weatherData[$zoneID]['forecast']			= $forecast;
			$weatherData[$zoneID]['properties']			= $zone->getProperties();
			$weatherData[$zoneID]['coordinates']		= $zone->geometryCoordinates;
			$weatherData[$zoneID]['coordinatesCentral']	= $zone->geometryCoordinatesCentral;
		}
	}
	return $weatherData;
}

function weatherDataGetCached(){
	return json_decode(file_get_contents(WEATHER_CACHE_FILE));
}

function weatherDataCache($data){
	if(WEATHER_CACHE)
		file_put_contents(WEATHER_CACHE_FILE, json_encode($data));
}