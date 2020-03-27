<?php
class WeatherApi {
	
	private $baseUrl = 'https://api.weather.gov/';
	
	public function __construct(){
		
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
				'User-Agent: (fogcast.freshgreenmedia.com, freshgreenmedia@gmail.com)',
			],
		];
		curl_setopt_array($ch, $curlConfig);
		$result = curl_exec($ch);
		if(curl_errno($ch))
			throw new Exception(curl_error($ch));
		curl_close($ch);
		return json_decode($result);
	}
	
}

class Zones extends WeatherApi {

	private $area = null;
	public $zones = [];

	public function __construct(string $area){
		$this->area = $area;
		$this->getZonesForArea();
	}
	
	private function getZonesForArea(){
		$zones = $this->request('zones', [
			'area'				=> $this->area,
			'type'				=> 'forecast',
			'include_geometry'	=> 'false',
		]);
		$zonesSimple = [];
		foreach($zones->features as $feature)
			$this->zones[$feature->properties->id] = new Zone($feature);
	}

}

class Zone extends WeatherApi {

	public $geometryCoordinatesCentral = null;
	public $geometryCoordinatesFirst = null;
	public $hasFog = false;
	public $hasThunder = false;
	public $hasSnow = false;
	public $hasRain = false;
	public $feature = null;
	private $forecast = null;

	public function __construct(object $feature){
		$this->feature = $feature;
		$this->setGeometryCoordinatesCentral();
		$this->setGeometryCoordinatesFirst();
		$this->setForecast();
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
	}
	
	private function setForecast(){
		$this->forecast = $this->request('zones/forecast/' . $this->feature->properties->id . '/forecast');
	}
	
	public function getForecast(){
		$forecast = [
			'updated' => strtotime($this->forecast->updated),
			'periods' => [],
		];
		foreach($this->forecast->periods as $period){
			$forecast['periods'][] = (object) [
				'name'		=> $period->name,
				'fog'		=> $this->forecastHasFog($period->detailedForecast),
				'thunder'	=> $this->forecastHasThunder($period->detailedForecast),
				'snow'		=> $this->forecastHasSnow($period->detailedForecast),
				'rain'		=> $this->forecastHasRain($period->detailedForecast),
				'forecast'	=> $period->detailedForecast,
			];
		}
		return (object) $forecast;
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