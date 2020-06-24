<?php
class WeatherApi {
	
	private $apiVersion = '1.1'; // the version defined in API results
	
	private $baseUrl = 'https://api.weather.gov/';
	
	private $credentials = null;
	
	public function __construct(object $credentials){
		$this->credentials = $credentials;
	}
	
	protected function request(string $path, array $params = []){
		$ch = curl_init();
		$curlConfig = [
			CURLOPT_URL				=> $this->baseUrl . $path . '?' . http_build_query($params),
			// CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER	=> true,
			// CURLOPT_POSTFIELDS     => array(
				// 'field1' => 'some date',
				// 'field2' => 'some other data',
			// )
			CURLOPT_HTTPHEADER		=> [
				'Accept: application/json',
				'Content-Type: text/xml; charset=utf-8',
				'Access-Control-Allow-*',
				'User-Agent: (' . $this->credentials->domain . ', ' . $this->credentials->email . ')',
			],
		];
		curl_setopt_array($ch, $curlConfig);
		$result = curl_exec($ch);
		if(curl_errno($ch))
			throw new Exception(curl_error($ch));
		curl_close($ch);
		$result = json_decode($result);
		if(isset($result->{'@context'}->{'@version'})){
			$apiVersion = $result->{'@context'}->{'@version'};
			if($apiVersion !== $this->apiVersion)
				throw new Exception('APP Version has changed from ' . $apiVersion . ' to ' . $this->apiVersion);
		}
		return $result;
	}
	
}

class Zones extends WeatherApi {

	private $credentials = null;
	private $area = null;
	public $zones = [];

	public function __construct(object $credentials, string $area){
		parent::__construct($credentials);
		$this->credentials = $credentials;
		$this->area = $area;
		$this->getZonesForArea();
	}
	
	private function getZonesForArea(){
		$zones = $this->request('zones', [
			'area'				=> $this->area,
			'type'				=> 'forecast',
			'include_geometry'	=> 'false',
		]);
		if(!isset($zones->features))
			throw new Exception('Missing zones feature for area ' . $this->area);
		$zonesSimple = [];
		foreach($zones->features as $feature){
			$this->zones[$feature->properties->id] = new Zone($this->credentials, $feature);
		}
	}

}

class Zone extends WeatherApi {

	private $credentials = null;
	public $geometryCoordinates = null;
	public $geometryCoordinatesCentral = null;
	public $geometryCoordinatesFirst = null;
	public $hasFog = false;
	public $hasThunder = false;
	public $hasSnow = false;
	public $hasRain = false;
	public $feature = null;
	private $forecast = null;

	public function __construct(object $credentials, object $feature){
		parent::__construct($credentials);
		$this->credentials = $credentials;
		$this->feature = $feature;
		// $this->setGeometryCoordinates();
		// $this->setGeometryCoordinatesCentral();
		// $this->setGeometryCoordinatesFirst();
		$this->setForecast();
		$this->setGeometryCoordinatesFromForecast();
		$this->setGeometryCoordinatesCentralFromForecast();
		$this->setGeometryCoordinatesFirstFromForecast();
	}

	/* private function setGeometryCoordinates(){
		$coordinates = array();
		if(!isset($this->feature->geometry))
			throw new Exception('Missing coordinates for feature ' . $this->feature->id);
		foreach($this->feature->geometry->coordinates as $a){
			$coordinates[] = $a[0];
		}
		$this->geometryCoordinates = $coordinates;
	}
	
	private function setGeometryCoordinatesCentral(){
		foreach($this->feature->geometry->coordinates as $a){
			foreach($a[0] as $b){
				$centralCoordinates['x'][] = $b[1];
				$centralCoordinates['y'][] = $b[0];
			}
		}
		$centralCoordinates['x'] = array_sum($centralCoordinates['x']) / count($centralCoordinates['x']);
		$centralCoordinates['y'] = array_sum($centralCoordinates['y']) / count($centralCoordinates['y']);
		$this->geometryCoordinatesCentral = (object) $centralCoordinates;
	}

	private function setGeometryCoordinatesFirst(){
		$this->geometryCoordinatesFirst = (object) [
			'x' => $this->feature->geometry->coordinates[0][0][0][1],
			'y' => $this->feature->geometry->coordinates[0][0][0][0],
		];
	} */
	
	private function setGeometryCoordinatesFromForecast(){
		$coordinates = array();
		if(!isset($this->forecast->geometry))
			throw new Exception('Missing coordinates for forecast ' . $this->forecast->properties->zone);
		foreach($this->forecast->geometry->coordinates as $a){
			$coordinates[] = $a[0];
		}
		$this->geometryCoordinates = $coordinates;
	}
	
	private function setGeometryCoordinatesCentralFromForecast(){
		foreach($this->forecast->geometry->coordinates as $a){
			foreach($a[0] as $b){
				$centralCoordinates['x'][] = $b[1];
				$centralCoordinates['y'][] = $b[0];
			}
		}
		$centralCoordinates['x'] = array_sum($centralCoordinates['x']) / count($centralCoordinates['x']);
		$centralCoordinates['y'] = array_sum($centralCoordinates['y']) / count($centralCoordinates['y']);
		$this->geometryCoordinatesCentral = (object) $centralCoordinates;
	}

	private function setGeometryCoordinatesFirstFromForecast(){
		$this->geometryCoordinatesFirst = (object) [
			'x' => $this->forecast->geometry->coordinates[0][0][0][1],
			'y' => $this->forecast->geometry->coordinates[0][0][0][0],
		];
	}
	
	private function setForecast(){
		$this->forecast = $this->request('zones/forecast/' . $this->feature->properties->id . '/forecast');
	}

	public function getForecast(){
		$forecast = [
			'updated' => strtotime($this->forecast->properties->updated),
			'updatedReadable' => date('D, M j \a\t g:i A', strtotime($this->forecast->properties->updated)),
			'periods' => [],
		];
		foreach($this->forecast->properties->periods as $period){
			$forecast['periods'][] = (object) [
				'name'		=> $period->name,
				'fog'		=> $this->forecastHasFog($period->detailedForecast),
				'thunder'	=> $this->forecastHasThunder($period->detailedForecast),
				'snow'		=> $this->forecastHasSnow($period->detailedForecast),
				'rain'		=> $this->forecastHasRain($period->detailedForecast),
				'forecast'	=> $period->detailedForecast,
			];
		}
		$forecast['fog']		= $this->hasFog;
		$forecast['thunder']	= $this->hasThunder;
		$forecast['snow']		= $this->hasSnow;
		$forecast['rain']		= $this->hasRain;
		return (object) $forecast;
	}
	
	public function getProperties(){
		$properties = $this->feature->properties;
		unset($properties->{'@id'});
		unset($properties->{'@type'});
		unset($properties->id);
		// unset($properties->name);
		unset($properties->type);
		// unset($properties->state);
		unset($properties->cwa);
		unset($properties->forecastOffices);
		$properties->timeZone = $properties->timeZone[0];
		return $properties;
	}
	
	private function forecastHasFog(string $forecast){
		$hasFog = stristr($forecast, 'fog') ? true : false;
		if(true === $hasFog)
			$this->hasFog = true;
		return $hasFog;
	}
	
	private function forecastHasThunder(string $forecast){
		$hasThunder = stristr($forecast, 'thunder') ? true : false;
		if(true === $hasThunder)
			$this->hasThunder = true;
		return $hasThunder;
	}
	
	private function forecastHasSnow(string $forecast){
		$hasSnow = stristr($forecast, 'snow') ? true : false;
		if(true === $hasSnow)
			$this->hasSnow = true;
		return $hasSnow;
	}
	
	private function forecastHasRain(string $forecast){
		$hasRain = stristr($forecast, 'rain') ? true : false;
		if(true === $hasRain)
			$this->hasRain = true;
		return $hasRain;
	}

}
