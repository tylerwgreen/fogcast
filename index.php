<?php
require(dirname(__FILE__) . '/App/App.php');
$zonesCombined = [
	'OR',
	'WA',
	// 'ID',
];
foreach($zonesCombined as $k => $v){
	$zonesCombined[$k] = new Zones($v);
}
?><!doctype html>
<html class="no-js" lang="">
<head>
	<meta charset="utf-8">
	<title>Fogcast</title>
	<meta name="description" content="Fogcast">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/normalize.css">
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
	<table>
		<tbody>
			<?php foreach($zonesCombined as $zones): ?>
				<?php $alt = false; foreach($zones->zones as $zone):
					$forecast = $zone->getForecast();
				?>
					<tr class="<?= $alt ? 'alt' : ''; ?>">
						<td class="overview">
							<span class="<?= $zone->hasRain ? 'zone-has-rain' : ''; ?>">Rain</span>
							<span class="<?= $zone->hasThunder ? 'zone-has-thunder' : ''; ?>">Thunder</span>
							<span class="<?= $zone->hasSnow ? 'zone-has-snow' : ''; ?>">Snow</span>
							<span class="<?= $zone->hasFog ? 'zone-has-fog' : ''; ?>">Fog</span>
						</td>
						<td><a href="https://www.google.com/maps/search/<?= $zone->geometryCoordinatesCentral->x; ?>,<?= $zone->geometryCoordinatesCentral->y; ?>" target="_blank"><?= $zone->feature->properties->id; ?></a></td>
						<td><?= $zone->feature->properties->name; ?></td>
						<td class="updated"><?= date('y-m-d\<\b\r\/\>H:i:s', $forecast->updated); ?></td>
						<?php foreach($forecast->periods as $period): ?>
							<td class="forecast <?= $period->rain ? 'period-has-rain' : ''; ?> <?= $period->thunder ? 'period-has-thunder' : ''; ?> <?= $period->snow ? 'period-has-snow' : ''; ?> <?= $period->fog ? 'period-has-fog' : ''; ?>"><b><?= $period->name; ?>:</b> <?= $period->forecast; ?></td>
						<?php endforeach; ?>
					</tr>
				<?php $alt = $alt ? false :true; endforeach; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
</body>
</html>