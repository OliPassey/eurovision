<?php
require_once 'vendor/autoload.php';

// Connect to the MongoDB database
$mongo = new MongoDB\Client('mongodb://10.0.3.12:27017');
$collection = $mongo->eurovision->votes;

// Get the submitted form data
$name = $_POST['name'];
$countryDropdowns = $_POST['country'];

// Check that all countries have been selected
if (count(array_filter($countryDropdowns)) < count($countryDropdowns)) {
  die('Error: Please vote for all countries.');
}

// Check that each country has a unique point value
$pointValues = array_keys($countryDropdowns);
$usedPointValues = array();
foreach ($pointValues as $index => $value) {
  $usedPointValues[] = $value;
  if (count(array_keys($usedPointValues, $value)) > 1) {
    die('Error: Each point value may only be used once.');
  }
}

// Calculate the vote totals for each country
$voteTotals = array();
foreach ($countryDropdowns as $code => $value) {
  $pointValue = $pointValues[$code];
  if (!isset($voteTotals[$value])) {
    $voteTotals[$value] = $pointValue;
  } else {
    $voteTotals[$value] += $pointValue;
  }
}


// Insert the vote document into the database
$voteDocument = array(
  'name' => $name,
  'votes' => $voteTotals,
  'timestamp' => new MongoDB\BSON\UTCDateTime()
);
$collection->insertOne($voteDocument);

// Redirect to the results page
header('Location: view_results.php');
exit();
?>
