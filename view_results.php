<!DOCTYPE html>
<html>
<head>
	<title>Eurovision 2023 Results</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="dark-mode">
	<h2>List of voters</h2>
	<?php
	// Get the MongoDB connection
	$mongo = new MongoDB\Driver\Manager('mongodb://10.0.3.12:27017');

	// Query for distinct voter names
	$command = new MongoDB\Driver\Command([
		'distinct' => 'votes',
		'key' => 'name',
	]);
	$rows = $mongo->executeCommand('eurovision', $command);
	$names = $rows->toArray()[0]->values;

	// Display the list of voters
	echo '<div class="voters">';
	echo '<strong>List of voters:</strong> ';
	foreach ($names as $i => $name) {
		if ($i > 0) {
			echo ', ';
		}
		echo $name;
	}
	echo '</div>';
	?>

	<h1>Results</h1>
	<table>
		<tr>
			<th>Country</th>
			<th>Votes</th>
		</tr>
		<?php
		// Query for all votes
		$query = new MongoDB\Driver\Query([]);
		$rows = $mongo->executeQuery('eurovision.votes', $query);

		// Group votes by country
		$votesByCountry = array();
		$csv = array_map('str_getcsv', file('countries.csv'));
		array_shift($csv);
		foreach ($csv as $row) {
		  $countryName = $row[0];
		  $votesByCountry[$countryName] = 0;
		}
		foreach ($rows as $row) {
		  foreach ($row->votes as $countryCode => $numVotes) {
		    $countryName = $csv[array_search($countryCode, array_column($csv, 1))][0];
		    $votesByCountry[$countryName] += $numVotes;
		  }
		}

		// Sort the countries by number of votes
		arsort($votesByCountry);

		// Display the results table
		echo '<table>';
		//echo '<tr><th>Country</th><th>Votes</th></tr>';
		foreach ($votesByCountry as $country => $numVotes) {
		  echo '<tr><td>' . $country . '</td><td>' . $numVotes . '</td></tr>';
		}
		echo '</table>';
		?>
	
	<script>
		var autoRefresh = true;

		function toggleAutoRefresh() {
			autoRefresh = !autoRefresh;
			document.getElementById("autoRefreshButton").textContent = autoRefresh ? "Turn off auto-refresh" : "Turn on auto-refresh";
		}

		function checkAutoRefresh() {
			if (autoRefresh) {
				setTimeout(function() {
					location.reload();
				}, 5000);
			}
		}

		setInterval(checkAutoRefresh, 5000);
	</script>

	<button id="autoRefreshButton" onclick="toggleAutoRefresh()">Turn off auto-refresh</button>
</body>
</html>
