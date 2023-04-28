<?php
require_once 'vendor/autoload.php';

// Connect to the MongoDB database
$mongo = new MongoDB\Client('mongodb://10.0.3.12:27017');
$collection = $mongo->eurovision->votes;

// Get the submitted form data
$name = $_POST['name'];
$countryDropdowns = $_POST['country'];

// Load the config file
$config = json_decode(file_get_contents('config.json'), true);
$pointValues = $config['pointValues'];

// Calculate the vote totals for each country
$voteTotals = array();
foreach ($pointValues as $index => $value) {
  $countryCode = $countryDropdowns[$index];
  if (!isset($voteTotals[$countryCode])) {
    $voteTotals[$countryCode] = $value;
  } else {
    $voteTotals[$countryCode] += $value;
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
