<?php
require(dirname(__FILE__) . '/App/App.php');
$zonesCombined = [
	'OR',
	'WA',
	// 'ID',
];
foreach($zonesCombined as $k => $v){
	// $cacheFile = DIR_DATA . $v . '.json';
	// if(CACHE_WEATHER_READ){
		// $zone = json_decode(file_get_contents($cacheFile));
	// }else{
		// $zone = new Zones($v);
		// if(CACHE_WEATHER)
			// file_put_contents($cacheFile, json_encode($zone));
	// }
	$zonesCombined[$k] = new Zones($v);
}
?><!doctype html>
<html class="no-js" lang="">
<head>
	<meta charset="utf-8">
	<title>Fogcast</title>
	<meta name="description" content="Fogcast">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?= URL_CSS; ?>style.css">
	<!--
	<link rel="stylesheet/less" type="text/css" href="<?= URL_LESS; ?>style.less" />
	-->
</head>
<body>
	<table>
		<tbody>
			<?php foreach($zonesCombined as $zones): ?>
				<?php $alt = false; foreach($zones->zones as $zone):
					$forecast = $zone->getForecast();
				?>
					<tr class="<?= $alt ? 'alt' : ''; ?>">
						<td class="map-tile-cell">
							<div class="map-tile-wrap">
								<img src="<?= URL_IMG  . '/map/tiles/' . $zone->feature->properties->id; ?>.png" alt="map tile"/>
							</div>
						</td>
						<td class="meta-cell">
							<div class="overview">
								<span class="<?= $zone->hasRain ? 'zone-has-rain' : ''; ?>">Rain</span>
								<span class="<?= $zone->hasThunder ? 'zone-has-thunder' : ''; ?>">Thunder</span>
								<span class="<?= $zone->hasSnow ? 'zone-has-snow' : ''; ?>">Snow</span>
								<span class="<?= $zone->hasFog ? 'zone-has-fog' : ''; ?>">Fog</span>
							</div>
							<div class="hourly-forecast-link">
								<a
									href="https://forecast.weather.gov/MapClick.php?w0=t&w1=td&w2=wc&w3=sfcwind&w3u=1&w4=sky&w5=pop&w6=rh&w7=rain&w8=thunder&w9=snow&w10=fzg&w11=sleet&w12=fog&w13u=0&w16u=1&AheadHour=0&Submit=Submit&FcstType=graphical&textField1=<?= $zone->geometryCoordinatesCentral->x; ?>&textField2=<?= $zone->geometryCoordinatesCentral->y; ?>&site=all&unit=0&dd=&bw="
									target="_blank"
									><?= $zone->feature->properties->id; ?><br/>Hourly Forecast</a>
							</div>
							<div class="map-link">
								<a
									href="https://www.google.com/maps/search/@<?= $zone->geometryCoordinatesCentral->x; ?>,<?= $zone->geometryCoordinatesCentral->y; ?>,12z"
									target="_blank"
									><?= $zone->feature->properties->name; ?><br/>Map</a>
							</div>
							<div class="updated">
								<?= date('y-m-d\<\b\r\/\>H:i:s', $forecast->updated); ?>
							</div>
						</td>
						<?php foreach($forecast->periods as $period): ?>
							<td class="forecast-cell">
								<div class="forecast-wrap">
									<div class="forecast-types">
										<span class="rain <?= $period->rain ? 'period-has-rain' : ''; ?>">Rain</span>
										<span class="thunder <?= $period->thunder ? 'period-has-thunder' : ''; ?>">Thunder</span>
										<span class="snow <?= $period->snow ? 'period-has-snow' : ''; ?>">Snow</span>
										<span class="fog <?= $period->fog ? 'period-has-fog' : ''; ?>">Fog</span>
									</div>
									<div class="forecast">
										<b><?= $period->name; ?>:</b> <?= $period->forecast; ?>
									</div>
								</div>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php $alt = $alt ? false :true; endforeach; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<!--
	<script>
		less = {
			env: "development",
			async: false,
			fileAsync: false,
			poll: 100,
			functions: {},
			dumpLineNumbers: "comments",
			relativeUrls: false,
			rootpath: ":/a.com/"
		};
	</script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js"></script>
	<script>less.watch();</script>
	<script src="<?= URL_JS; ?>script.js"></script>
	-->
</body>
</html>
