<?php
if (!isset($_GET['id'])) {
    // Redirect back to admin page if no ID is provided
    header('Location: admin.php');
    exit;
}

$id = $_GET['id']; // Get the ID from the query string
$config = json_decode(file_get_contents('config.json'), true);
$mongo = new MongoDB\Driver\Manager($config['mongodb']);

// Convert string ID back to MongoDB\BSON\ObjectId
$bsonId = new MongoDB\BSON\ObjectId($id);

// Delete operation
$bulk = new MongoDB\Driver\BulkWrite;
$bulk->delete(['_id' => $bsonId]);
$result = $mongo->executeBulkWrite($config['database'] . '.' . $config['collection'], $bulk);

// Redirect back to admin.php after deletion
header('Location: admin.php');
