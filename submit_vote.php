<?php
require_once 'vendor/autoload.php';

// Connect to the MongoDB database
$mongo = new MongoDB\Client('mongodb://10.0.3.12:27017');
$collection = $mongo->eurovision->votes;

// Get the submitted form data
$name = $_POST['name'];
$votes = $_POST['votes'];

// Check that exactly 10 countries were selected
if (count($votes) != 10) {
  die('Error: You must select exactly 10 countries.');
}

// Check that each point value is used only once
$pointValues = array(12, 10, 8, 7, 6, 5, 4, 3, 2, 1);
$usedPointValues = array();
foreach ($votes as $code) {
  $pointValue = intval($_POST['points'][$code]);
  if (in_array($pointValue, $usedPointValues)) {
    die('Error: Each point value may only be used once.');
  }
  $usedPointValues[] = $pointValue;
}

// Calculate the vote totals for each country
$voteTotals = array();
foreach ($votes as $code) {
  $pointValue = intval($_POST['points'][$code]);
  $voteTotals[$code] = $pointValue;
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
