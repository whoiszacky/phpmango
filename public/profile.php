<?php
session_start();
require '../src/db.php'; // Ensure this path is correct
require '../src/auth.php';
require '../src/media.php';
require '../src/feedback.php'; // Make sure to include the feedback handler

// Get the database connection
$db = getDbConnection();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['user_id'];
$user = $db->users->findOne(['username' => $username]);
$mediaHandler = new Media($db);
$feedbackHandler = new Feedback($db); // Initialize feedback handler

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $file = $_FILES['media_file'];
    if ($mediaHandler->uploadMedia($file, $username)) {
        $uploadMessage = "File uploaded successfully!";
    } else {
        $uploadMessage = "There was an error uploading your file.";
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['media_id']) && isset($_POST['comment'])) {
    $mediaId = $_POST['media_id'];
    $comment = $_POST['comment'];
    $feedbackHandler->addComment($mediaId, $username, $comment);
}

// Retrieve uploaded media
$uploadedMedia = $mediaHandler->getMediaByUser($username);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Media Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        img, video {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="bg-gray-200 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-3xl">
        <h1 class="text-3xl font-semibold text-center mb-6 text-gray-800">Welcome, <?php echo htmlspecialchars($user->username); ?></h1>

        <h2 class="text-xl font-semibold mb-4 text-gray-700">Upload Media</h2>
        <form method="post" enctype="multipart/form-data" class="mb-6">
            <div class="mb-4">
                <label class="block text-gray-600 mb-2">Select Media File:</label>
                <input type="file" name="media_file" class="border rounded-md w-full p-2 text-gray-600" required accept="image/*,video/*" id="mediaFileInput">
            </div>
            <div id="mediaPreview" class="mt-4 hidden">
                <h3 class="font-semibold text-gray-700">Preview:</h3>
                <div id="previewContainer"></div>
            </div>
            <button type="submit" class="bg-blue-600 text-white rounded-md py-2 px-4 w-full hover:bg-blue-500 transition duration-200">Upload</button>
        </form>

        <?php if (isset($uploadMessage)): ?>
            <p class="mt-4 text-center text-gray-700"><?php echo $uploadMessage; ?></p>
        <?php endif; ?>

        <h2 class="text-xl font-semibold mt-6 mb-2 text-gray-700">Your Uploaded Media</h2>
        <ul class="list-disc pl-5 space-y-4">
            <?php foreach ($uploadedMedia as $media): ?>
                <li class="bg-gray-50 border rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <a href="<?php echo htmlspecialchars($media->filepath); ?>" target="_blank" class="text-blue-600 hover:underline">
                            <?php echo htmlspecialchars($media->filename); ?>
                        </a>
                        <span class="text-gray-500">(Size: <?php echo htmlspecialchars($media->file_size); ?> bytes)</span>
                    </div>
                    <span class="text-gray-500">(Uploaded by: <?php echo htmlspecialchars($media->uploader); ?>)</span>
                    <span class="text-gray-500">(Status: <?php echo htmlspecialchars($media->status); ?>)</span>

                    <!-- Comment Section -->
                    <div class="mt-4">
                        <h3 class="font-semibold text-gray-700">Comments:</h3>
                        <ul class="list-disc pl-5">
                            <?php
                            $comments = $feedbackHandler->getComments($media->_id);
                            foreach ($comments as $comment): ?>
                                <li><?php echo htmlspecialchars($comment->username) . ": " . htmlspecialchars($comment->comment); ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <form method="post" class="mt-2">
                            <input type="hidden" name="media_id" value="<?php echo htmlspecialchars($media->_id); ?>">
                            <textarea name="comment" required placeholder="Add your comment..." class="border rounded-md w-full p-2 mt-2 text-gray-600"></textarea>
                            <button type="submit" class="bg-blue-600 text-white rounded-md py-1 px-3 w-full mt-2 hover:bg-blue-500 transition duration-200">Submit Comment</button>
                        </form>
                    </div>

                    <!-- Status Update Form -->
                    <form method="post" action="update_status.php" class="mt-4">
                        <input type="hidden" name="media_id" value="<?php echo htmlspecialchars($media->_id); ?>">
                        <select name="status" class="border rounded-md w-full p-2 text-gray-600">
                            <option value="approved" <?php if ($media->status == 'approved') echo 'selected'; ?>>Approved</option>
                            <option value="needs work" <?php if ($media->status == 'needs work') echo 'selected'; ?>>Needs Work</option>
                            <option value="rejected" <?php if ($media->status == 'rejected') echo 'selected'; ?>>Rejected</option>
                        </select>
                        <button type="submit" class="bg-green-600 text-white rounded-md py-1 px-3 mt-2 hover:bg-green-500 transition duration-200">Update Status</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <a href="logout.php" class="text-red-500 mt-4 inline-block hover:underline">Logout</a>
    </div>

    <script>
        const mediaFileInput = document.getElementById('mediaFileInput');
        const mediaPreview = document.getElementById('mediaPreview');
        const previewContainer = document.getElementById('previewContainer');

        mediaFileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            previewContainer.innerHTML = ''; // Clear previous previews

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'mt-2 rounded-md shadow-lg'; // Improved styling
                        previewContainer.appendChild(img);
                    } else if (file.type.startsWith('video/')) {
                        const video = document.createElement('video');
                        video.src = e.target.result;
                        video.controls = true;
                        video.className = 'mt-2 rounded-md shadow-lg'; // Improved styling
                        previewContainer.appendChild(video);
                    }
                    mediaPreview.style.display = 'block'; // Show the preview
                };
                reader.readAsDataURL(file);
            } else {
                mediaPreview.style.display = 'none'; // Hide preview if no file selected
            }
        });
    </script>
</body>
</html>
