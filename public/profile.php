<?php
// Start the session only if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files using require_once to avoid redeclaration errors
require_once '../src/db.php'; // Ensure this path is correct
require_once '../src/auth.php';
require_once '../src/media.php';
require_once '../src/feedback.php'; // Include the feedback handler

// Get the database connection
$db = getDbConnection();

// Redirect to the index page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['user_id'];
$user = $db->users->findOne(['username' => $username]);
$mediaHandler = new Media($db);
$feedbackHandler = new Feedback($db); // Initialize feedback handler

// Handle media upload
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
$allMedia = $mediaHandler->getAllMedia(); // Assuming this function exists

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Media Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        /* Additional custom styles if necessary */
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-3xl">
        <h1 class="text-3xl font-semibold text-center mb-6 text-gray-800">Welcome, <?php echo htmlspecialchars($user->username); ?></h1>

        <h2 class="text-xl font-semibold mb-4 text-gray-700">Upload Media</h2>
        <form method="post" enctype="multipart/form-data" class="mb-6">
            <div class="mb-4">
                <label class="block text-gray-600 mb-2">Select Media File:</label>
                <input type="file" name="media_file" class="border rounded-md w-full p-2 text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200" required accept="image/*,video/*" id="mediaFileInput">
            </div>
            <div id="mediaPreview" class="mt-4 hidden">
                <h3 class="font-semibold text-gray-700">Preview:</h3>
                <div id="previewContainer"></div>
            </div>
            <button type="submit" class="bg-blue-600 text-white rounded-md py-2 px-4 w-full hover:bg-blue-500 transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Upload media file">Upload</button>
        </form>

        <?php if (isset($uploadMessage)): ?>
            <div class="mt-4 text-center text-<?php echo strpos($uploadMessage, 'success') !== false ? 'green' : 'red'; ?>-600">
                <?php echo $uploadMessage; ?>
            </div>
        <?php endif; ?>

        <h2 class="text-xl font-semibold mt-6 mb-2 text-gray-700">All Uploaded Media</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($allMedia as $media): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="flex justify-between items-center mb-2">
    <a href="<?php echo htmlspecialchars(isset($media->filepath) ? $media->filepath : '#'); ?>" target="_blank" class="text-blue-600 hover:underline">
        <?php echo htmlspecialchars(isset($media->filename) ? $media->filename : 'Unknown file'); ?>
    </a>
    <span class="text-gray-500">
        (Size: <?php echo htmlspecialchars(isset($media->file_size) ? $media->file_size : 'N/A'); ?> bytes)
    </span>
</div>
<span class="inline-block bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">
    Uploaded by: <?php echo htmlspecialchars(isset($media->uploader) ? $media->uploader : 'Unknown uploader'); ?>
</span>

                    <span class="inline-block bg-<?php echo isset($media->status) ? ($media->status === 'approved' ? 'green' : ($media->status === 'needs work' ? 'yellow' : 'red')) : 'gray'; ?>-200 
    text-<?php echo isset($media->status) ? ($media->status === 'approved' ? 'green' : ($media->status === 'needs work' ? 'yellow' : 'red')) : 'gray'; ?>-700 
    text-xs px-2 py-1 rounded-full">
    Status: <?php echo htmlspecialchars(isset($media->status) ? $media->status : 'unknown'); ?>
</span>

                    
                    <!-- Comment Section -->
                    <div class="mt-4">
                        <h3 class="font-semibold text-gray-700">Comments:</h3>
                        <ul class="list-none space-y-3 mt-2">
                            <?php
                            $comments = $media->comments ?? [];
                            if (!empty($comments)) {
                                foreach ($comments as $comment): ?>
                                    <li class="bg-gray-100 p-3 rounded-lg">
                                        <span class="text-gray-800 font-semibold"><?php echo htmlspecialchars($comment['username']); ?></span>
                                        <span class="text-xs text-gray-500 ml-2">
                                        <?php
                                            if (isset($comment['comment_date']) && $comment['comment_date'] instanceof MongoDB\BSON\UTCDateTime) {
                                                // Convert to PHP DateTime object
                                                $dateTime = $comment['comment_date']->toDateTime();
                                                // Format the DateTime object as desired
                                                echo $dateTime->format('Y-m-d H:i:s'); // e.g., 'Y-m-d H:i:s' for full date-time
                                            } else {
                                                echo 'Just now';
                                            }
                                        ?>
                                        </span>

                                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                    </li>
                                <?php endforeach;
                            } else {
                                echo "<li class='text-gray-500'>No comments yet.</li>";
                            }
                            ?>
                        </ul>
                        <form method="post" class="mt-2">
                            <input type="hidden" name="media_id" value="<?php echo htmlspecialchars($media->_id); ?>">
                            <textarea name="comment" required placeholder="Add your comment..." class="border rounded-lg w-full p-3 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"></textarea>
                            <button type="submit" class="bg-blue-600 text-white rounded-md py-1 px-3 w-full mt-2 hover:bg-blue-500 transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">Submit Comment</button>
                        </form>
                    </div>

                    <!-- Status Update Form --><!-- Status Update Form (only for admins) -->
                    <?php if (isset($user->role) && $user->role === 'admin'): ?>
                        <form method="post" action="update_status.php" class="mt-4">
                            <input type="hidden" name="media_id" value="<?php echo htmlspecialchars($media->_id); ?>">
                            <input type="hidden" name="admin" value="<?php echo htmlspecialchars($user->username); ?>">
                            <select name="status" class="border rounded-md w-full p-2 text-gray-600">
                                <option value="approved" <?php if ($media->status == 'approved') echo 'selected'; ?>>Approved</option>
                                <option value="needs work" <?php if ($media->status == 'needs work') echo 'selected'; ?>>Needs Work</option>
                                <option value="rejected" <?php if ($media->status == 'rejected') echo 'selected'; ?>>Rejected</option>
                            </select>
                            <button type="submit" class="bg-green-600 text-white rounded-md py-1 px-3 mt-2">Update Status</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="logout.php" class="flex items-center text-red-500 mt-4 hover:underline">
            <span class="mr-1"><i class="fas fa-sign-out-alt"></i></span> Logout
        </a>
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
                    mediaPreview.classList.remove('hidden'); // Show the preview
                };
                reader.readAsDataURL(file);
            } else {
                mediaPreview.classList.add('hidden'); // Hide preview if no file selected
            }
        });
    </script>
</body>
</html>
