<?php
session_start();
require_once '../src/db.php';

$db = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['media_id'], $_POST['status'])) {
    $mediaId = new MongoDB\BSON\ObjectId($_POST['media_id']);
    $status = $_POST['status'];
    $comment = $_POST['comment'] ?? '';
    $updatedBy = $_SESSION['user_id']; // Assuming admin's username is stored in session

    // Update the media status, admin who updated it, and add comment
    $result = $db->media->updateOne(
        ['_id' => $mediaId],
        [
            '$set' => [
                'status' => $status,
                'updated_by' => $updatedBy,
                'comment' => $comment
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
