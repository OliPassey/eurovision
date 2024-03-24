<?php
require_once 'vendor/autoload.php';

// Load the config file
$config = json_decode(file_get_contents('config.json'), true);
$pointValues = $config['pointValues'];

// Connect to the MongoDB database
// Corrected connection to MongoDB and getting the collection
$mongo = new MongoDB\Client($config['mongodb']);
$collection = $mongo->selectCollection($config['database'], $config['collection']);

// Get the submitted form data
$name = $_POST['name'];
$countryDropdowns = $_POST['country'];

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
