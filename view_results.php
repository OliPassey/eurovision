<!DOCTYPE html>
<html>
<head>
	<title>Eurovision 2023 Results</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<div class="center">
	<img src="img/ESC2023_Ukraine_LIVERPOOL_RGB_White_600px.png" width="600">
</div>
<body class="dark-mode">
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
	<h2>Who has voted:</h2>
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

    // Check if there are any votes
    $hasVotes = false;
    foreach ($names as $name) {
        $command = new MongoDB\Driver\Command([
            'count' => 'votes',
            'query' => ['name' => $name]
        ]);
        $rows = $mongo->executeCommand('eurovision', $command);
        $count = $rows->toArray()[0]->n;
        if ($count > 0) {
            $hasVotes = true;
            break;
        }
    }

    // Display the list of voters
    echo '<div class="voters">';
    echo '<strong></strong> ';
    foreach ($names as $i => $name) {
        if ($i > 0) {
            echo ', ';
        }
        echo $name;
    }
        echo '</div>';


// Display the results section
if ($hasVotes) {
    echo '<div class="center">';
    echo '<h1></h1>';


// Group votes by country
$votesByCountry = array();
$csv = array_map('str_getcsv', file('countries.csv'));
array_shift($csv);
foreach ($csv as $row) {
    $countryName = $row[0];
    $countryCode = $row[1];
    $votesByCountry[$countryName] = array(
        'code' => $countryCode,
        'votes' => 0
    );
}

// Query for all votes
$query = new MongoDB\Driver\Query([]);
$rows = $mongo->executeQuery('eurovision.votes', $query);

foreach ($rows as $row) {
    foreach ($row->votes as $countryCode => $numVotes) {
        $countryName = $csv[array_search($countryCode, array_column($csv, 1))][0];
        $votesByCountry[$countryName]['votes'] += $numVotes;
    }
}

// Sort the countries by number of votes
uasort($votesByCountry, function($a, $b) {
    return $b['votes'] - $a['votes'];
});
	// Display the top three positions with flags
	echo '<div class="top-results">';
    echo '<div class="top-3">';
	echo '<div class="top-3-item">';
	if (count($votesByCountry) > 0) {
		reset($votesByCountry);
		$countryName = key($votesByCountry);
		$numVotes = current($votesByCountry);
		$flagSrc = "img/esc_" . strtolower(str_replace(' ', '_', $countryName)) . ".png";
		echo '<h2>Winner</h2>';
        echo '<div class="1st"><span class="country-name">' . $countryName . '</span></div>';
        echo '<div class="flags"><img src="' . $flagSrc . '" width="630" /></div><br>';
		//echo '<span class="num-votes"> - ' . $numVotes . ' votes</span>';
	}
	echo '</div>';
	echo '<div class="top-3-item">';
	if (count($votesByCountry) > 1) {
		next($votesByCountry);
		$countryName = key($votesByCountry);
		$numVotes = current($votesByCountry);
		$flagSrc = "img/esc_" . strtolower(str_replace(' ', '_', $countryName)) . ".png";
		echo '<h2>Second</h2>';
        echo '<div class="2nd"><span class="country-name">' . $countryName . '</span></div>';
        echo '<div class="flags"><img src="' . $flagSrc . '" width="530"  /></div><br>';
		//echo '<span class="num-votes"> - ' . $numVotes . ' votes</span>';
	}
	echo '</div>';
	echo '<div class="top-3-item">';
	if (count($votesByCountry) > 2) {
		next($votesByCountry);
		$countryName = key($votesByCountry);
		$numVotes = current($votesByCountry);
		$flagSrc = "img/esc_" . strtolower(str_replace(' ', '_', $countryName)) . ".png";
		echo '<h2>Third</h2>';
        echo '<div class="3rd"><span class="country-name">' . $countryName . '</span></div>';
        echo '<div class="flags"><img src="' . $flagSrc . '" width="430" /></div>';
		//echo '<span class="num-votes"> - ' . $numVotes . ' votes</span>';
	}
	echo '</div>';
	echo '</div>';
	?>

	<table>
		<tr>
			<th>Country</th>
			<th>Votes</th>
		</tr>
		<?php
        // Display the rest of the results table
        foreach ($votesByCountry as $countryName => $countryData) {
            echo '<tr>';
            $flagSrc = "img/flags/" . strtolower($countryData['code']) . ".png";
            echo '<td><img src="' . $flagSrc . '" width="30" height="20" /> ' . $countryName . '</td>';
            echo '<td>' . $countryData['votes'] . '</td>';
            echo '</tr>';
        }
        ?>
	</table>
	
	
    </body>
    </html>
<?php
    echo '</div>';
} else {
    echo '<style>.top-results { display: none; }</style>';
}
