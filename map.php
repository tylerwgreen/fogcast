<?php
require(dirname(__FILE__) . '/App/App.php');
$weather = weatherDataGetCached();
$mapsApiKey = 'AIzaSyAlLUqHhCgRGeQXqIu07k4Rl0DBy8z_4fQ';
require(TEMPLATE_HEADER);
?>
<body id="map">
	<div id="data-container">
		<div id="zone-data">
			<div id="zone-id" class="zone-data-item">Zone ID</div>
			<ul id="zone-overview">
				<li id="fog">Fog</li>
				<li id="thunder">Thunder</li>
				<li id="snow">Snow</li>
				<li id="rain">Rain</li>
			</ul>
			<div id="zone-updated" class="zone-data-item">Updated</div>
			<div id="zone-name" class="zone-data-item">Zone Name</div>
		</div>
		<div id="period-data">
			<ul id="period-names"></ul>
		</div>
	</div>
	<div id="map-container"></div>
	<div id="period-selector">
		<ul id="periods-list"></ul>
	</div>
	<div id="forecast-modal">
		<div id="forecast-modal-controls">
			<a id="forecast-modal-btn-close" href="#">Close</a>
		</div>
		<ul id="forecast-data">
			<li id="forecast-data-id-wrap">
				<a href="#" id="forecast-data-id" title="Hourly forecast" target="_blank"></a>
			</li>
			<li id="forecast-data-updated"></li>
			<li id="forecast-data-name-wrap">
				<a href="#" id="forecast-data-name" title="Map" target="_blank"></a>
			</li>
		</ul>
		<ul id="forecast-periods"></ul>
	</div>
	<script>var zonesData = <?= json_encode($weather); ?>;</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= $mapsApiKey; ?>&callback=initMap"></script>
	<script src="<?= URL_JS; ?>map.js"></script>
<?php require(TEMPLATE_FOOTER); ?>