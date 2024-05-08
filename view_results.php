<?php
// Get the MongoDB connection
// Load the config file
$config = json_decode(file_get_contents('config.json'), true);
$mongo = new MongoDB\Driver\Manager($config['mongodb']);
$databaseName = $config['database'];  // 'eurovision'
$collectionName = $config['collection'];  // 'votes'
$baseUrl = $config['baseUrl'];
// URL encode the base URL to ensure it's safely included as a query parameter
$encodedBaseUrl = urlencode($baseUrl);
// Construct the full URL for the QR code image
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $encodedBaseUrl;
// Generate the HTML <img> tag with the QR code URL
$qrCodeImgTag = "<img src=\"" . $qrCodeUrl . "\"> </br>";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Eurovision 2024 Results</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

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

<?php
// Query for distinct voter names
$command = new MongoDB\Driver\Command([
    'distinct' => $collectionName,
    'key' => 'name',
]);
$rows = $mongo->executeCommand($databaseName, $command);
$names = $rows->toArray()[0]->values;

// Check if there are any votes
$hasVotes = false;
foreach ($names as $name) {
    $command = new MongoDB\Driver\Command([
        'count' => $collectionName,
        'query' => ['name' => $name]
    ]);
    $rows = $mongo->executeCommand($databaseName, $command);
    $count = $rows->toArray()[0]->n;
    if ($count > 0) {
        $hasVotes = true;
        break;
    }
}

// Display the list of voters
echo '<div class="voters">';
echo '<strong>Voters: </strong>';
foreach ($names as $i => $name) {
    if ($i > 0) {
        echo ', ';
    }
    echo $name;
}
echo '</div>';
?>
<div class="center">
    <a href="index.php"><img src="img/esc_sweden_malmo_rgb_white.png" width="500"></a>
</div>
<div class="scan2vote">
    <?php echo $qrCodeImgTag; ?> </br>
</div>
<?php
// Display the results section
if ($hasVotes) {
    echo '<div class="center">';
    echo '<h1>Eurovision Voting Results</h1>';

// Load data from songs.csv and initialize the votesByCountry array
$votesByCountry = array();
$csv = array_map('str_getcsv', file('songs.csv'));
array_shift($csv); // Remove header line to skip the titles
foreach ($csv as $row) {
    $countryName = $row[0];
    $countryCode = $row[1];
    $artist = $row[2]; // Artist's name is in the third column
    $songTitle = $row[3]; // Song title is in the fourth column
    $votesByCountry[$countryName] = array(
        'code' => strtolower($countryCode), // Ensure code is lowercase
        'song' => $songTitle,
        'artist' => $artist,
        'votes' => 0
    );
}

// Process votes and store in votesByCountry as before
$namespace = $databaseName . '.' . $collectionName;
$query = new MongoDB\Driver\Query([]);
$rows = $mongo->executeQuery($namespace, $query);
foreach ($rows as $row) {
    foreach ($row->votes as $countryCode => $numVotes) {
        $index = array_search($countryCode, array_column($csv, 1));
        $countryName = $csv[$index][0];
        $votesByCountry[$countryName]['votes'] += $numVotes;
    }
}

// Sort countries by votes in descending order
uasort($votesByCountry, function($a, $b) {
    return $b['votes'] - $a['votes'];
});

// Prepare data for JSON output and save to results.json
$jsonData = array();
foreach ($votesByCountry as $countryName => $countryData) {
    array_push($jsonData, array(
        "country" => $countryName, 
        "points" => $countryData['votes'],
        "song" => $countryData['song'],
        "artist" => $countryData['artist']
    ));
}
file_put_contents('results.json', json_encode($jsonData, JSON_PRETTY_PRINT));

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
        echo '<td><img src="img/flags/' . htmlspecialchars($countryData['code']) . '.png" width="30" height="20" /> ' . htmlspecialchars($countryName) . '</td>';
        echo '<td>' . htmlspecialchars($countryData['votes']) . '</td>';
        echo '</tr>';
    }
    ?>
</table>

<button id="autoRefreshButton" onclick="toggleAutoRefresh()">Turn off auto-refresh</button>

</body>
</html>
<?php
echo '</div>';
} else {
    echo '<style>.top-results { display: none; }</style>';
    echo '<p>No votes have been recorded yet.</p>';
}
?>
