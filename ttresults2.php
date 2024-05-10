<?php
// Load the MongoDB configuration
$config = json_decode(file_get_contents('config.json'), true);
$mongo = new MongoDB\Driver\Manager($config['mongodb']);
$databaseName = $config['database'];  // 'eurovision'
$collectionName = $config['collection'];  // 'votes'

// Move the logic for generating results.json here
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
?>

<script>
function startTime() {
  offset = 0; //add an hour for GMT British SUmmertime
  var today = new Date();
  var h = today.getUTCHours();
  
  //United Kingdom British Summertime Adjustment 1 Hour Ahead
  if (h+offset == 24) h = -1;

  var m = today.getUTCMinutes();
  var s = today.getUTCSeconds();
  h = h + offset;
  if (h > 24) {
    h = h - 24;
  }
  if (h < 0) {
    h = h + 24;
  }
  h = checkTime(h);
  m = checkTime(m);
  s = checkTime(s);
  document.getElementById('clock').innerHTML = h + ":" + m + "/" + s;
  var t = setTimeout(function() {
    startTime()
  }, 500);
}

function checkTime(i) {
  if (i < 10) {
    i = "0" + i
  };
  return i;
}

function loadResults() {
  fetch('results.json')  // Adjust path to your results.json
    .then(response => response.json())
    .then(data => displayResults(data))
    .catch(error => console.error('Error loading the results:', error));
}

function displayResults(results) {
    const resultsPerPage = 5;
    let currentPage = 0;
    const totalPages = Math.ceil(results.length / resultsPerPage);

    function renderPage(page) {
        const leaderboard = document.getElementById('leaderboard');
        leaderboard.innerHTML = '<p class="result-header">Pos&nbsp;Pts&nbsp;Country&nbsp;Song</p>'; // Header for clarity
        const start = page * resultsPerPage;
        const end = start + resultsPerPage;
        const pageResults = results.slice(start, end);

        pageResults.forEach((result, index) => {
            leaderboard.innerHTML += `
            <div class="result-row">
                <span class="position">${start + index + 1}</span>
                <span class="points">&nbsp;&nbsp;${result.points}</span>
                <span class="country-name">&nbsp;&nbsp;&nbsp;${result.country}</span><br>
                <span class="song">${result.song}</span>
            </div>`;
        });
    }

    function nextPage() {
        currentPage = (currentPage + 1) % totalPages;
        renderPage(currentPage);
    }

    renderPage(0); // Initially render the first page
    setInterval(nextPage, 5000); // Change page every 5 seconds
}


document.addEventListener('DOMContentLoaded', function() {
  startTime();
  loadResults();
});
</script>
<head>
    <title>Eurovision 2024 Results</title>
    <link rel="stylesheet" type="text/css" href="ttstyles.css?v=2">
</head>

<div class="mycontainer">
  <p>&nbsp;&nbsp;CEEFAX 1 324 Sat 11 May 2024<div id="clock"></div></p>
  <img class="bbc" src="img/bbc2.webp" alt="" />
  <p class="yellow"><span class="downabit">&nbsp;EUROVISION SONG CONTEST 2024 RESULTS</span></p><br><br>
  <div id="leaderboard"></div>
  <p>&nbsp;</p>
</div>
