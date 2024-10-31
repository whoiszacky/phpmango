<?php
require '../vendor/autoload.php';

function getDbConnection() {
    // Create a new MongoDB client
    $client = new MongoDB\Client("mongodb://localhost:27017");
    
    // Select the database
    return $client->media_management; // Return the database object
}

?>
