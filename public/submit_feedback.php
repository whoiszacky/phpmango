<?php
require '../vendor/autoload.php';
require '../src/feedback.php';

session_start();

// Establish database connection
$client = new MongoDB\Client("mongodb://localhost:27017");
$db = $client->media_management;

$feedback = new Feedback($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate session
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php'); // Redirect to login if not authenticated
        exit();
    }

    $mediaId = $_POST['media_id'];
    $username = $_SESSION['user_id'];
    $message = $_POST['feedback_message'];

    if ($feedback->submitFeedback($mediaId, $username, $message)) {
        header('Location: profile.php?feedback=success'); // Redirect back to profile
        exit();
    } else {
        header('Location: profile.php?feedback=failure'); // Handle error
        exit();
    }
}
?>
