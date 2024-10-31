<?php
session_start();
require '../src/db.php'; // Ensure you include your DB connection logic

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mediaId = $_POST['media_id'];
    $status = $_POST['status'];

    // Update the status of the media in the database
    $db = getDbConnection(); // Ensure your DB connection is set up
    $db->media->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($mediaId)],
        ['$set' => ['status' => $status]]
    );

    // Redirect back to the profile page after updating
    header('Location: profile.php'); 
    exit();
}
?>
