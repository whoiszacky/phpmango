<?php
session_start();
require_once '../src/db.php';

$db = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['media_id'], $_POST['status'])) {
    $mediaId = new MongoDB\BSON\ObjectId($_POST['media_id']);
    $status = $_POST['status'];
    $comment = $_POST['comment'] ?? ''; // Optional comment field
    $updatedBy = $_SESSION['user_id']; // Assuming admin's username is stored in session
    $timestamp = new MongoDB\BSON\UTCDateTime(new DateTime()); // Current timestamp

    // Update the media status, admin who updated it, add comment, and timestamp
    $result = $db->media->updateOne(
        ['_id' => $mediaId],
        [
            '$set' => [
                'status' => $status,
                'updated_by' => $updatedBy,
                'status_changed_at' => $timestamp, // Add timestamp of status update
                'comment' => $comment // Store the comment if provided
            ]
        ]
    );

    // Check if the update was successful
    if ($result->getModifiedCount() == 1) {
        $_SESSION['status_message'] = "Status updated successfully!";
    } else {
        $_SESSION['status_message'] = "Error: Status could not be updated.";
    }
}

// Redirect back to the profile page
header("Location: profile.php");
exit();
?>
