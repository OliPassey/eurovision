<?php
require_once 'vendor/autoload.php';

// Connect to the MongoDB database
$mongo = new MongoDB\Client('mongodb://10.0.3.12:27017');
$collection = $mongo->eurovision->votes;

// Get the submitted form data
$name = $_POST['name'];
$countryDropdowns = $_POST['country'];

// Calculate the vote totals for each country
$voteTotals = array();
foreach ($countryDropdowns as $pointValue => $countryCode) {
  if ($countryCode !== "") {
    if (!isset($voteTotals[$countryCode])) {
      $voteTotals[$countryCode] = (int) $pointValue;
    } else {
      $voteTotals[$countryCode] += (int) $pointValue;
    }
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
