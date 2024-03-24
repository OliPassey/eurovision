<?php
// Load the config file and MongoDB connection
$config = json_decode(file_get_contents('config.json'), true);
$mongo = new MongoDB\Driver\Manager($config['mongodb']);
$databaseName = $config['database'];
$collectionName = $config['collection'];

// Fetch all records from the MongoDB collection
$namespace = $databaseName . '.' . $collectionName;
$query = new MongoDB\Driver\Query([]);
$rows = $mongo->executeQuery($namespace, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=2">
</head>
<body>
    <h1>Admin Panel - Records Management</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Details</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row->_id); ?></td>
                <td>
                    <!-- Customize this part based on your data structure -->
                    Name: <?php echo htmlspecialchars($row->name); ?><br>
                    Votes: <?php echo htmlspecialchars(json_encode($row->votes)); ?>
                </td>
                <td>
                    <a href="delete.php?id=<?php echo urlencode($row->_id); ?>">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>


