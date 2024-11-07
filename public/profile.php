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

    header('Location: ' . $_SERVER['PHP_SELF']);  // Redirect to the same page
    exit();
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
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-3xl">
        <!-- User Greeting -->
        <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-8">Welcome, <?php echo htmlspecialchars($user->username); ?></h1>

        <!-- Upload Media Section -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Upload Media</h2>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <label class="block text-gray-600">
                    <span>Select Media File:</span>
                    <input type="file" name="media_file" class="mt-2 w-full border border-gray-300 rounded-lg p-3 text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required accept="image/*,video/*" id="mediaFileInput">
                </label>
                <div id="mediaPreview" class="mt-4 hidden">
                    <h3 class="text-gray-700 font-semibold">Preview:</h3>
                    <div id="previewContainer" class="flex items-center justify-center mt-2 p-3 border border-gray-200 rounded-lg"></div>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white rounded-lg py-3 font-semibold hover:bg-blue-500 transition duration-200">Upload</button>
            </form>
            <?php if (isset($uploadMessage)): ?>
                <p class="text-center mt-4 text-<?php echo strpos($uploadMessage, 'success') !== false ? 'green' : 'red'; ?>-600"><?php echo $uploadMessage; ?></p>
            <?php endif; ?>
        </section>

        <!-- Uploaded Media Section -->
        <section>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">All Uploaded Media</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($allMedia as $media): ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm transition-shadow hover:shadow-md">
                        <div class="flex justify-between items-center mb-2">
                            <a href="<?php echo htmlspecialchars($media->filepath ?? '#'); ?>" target="_blank" class="text-blue-600 hover:underline font-medium">
                                <?php echo htmlspecialchars($media->filename ?? 'Unknown file'); ?>
                            </a>
                            <span class="text-gray-500 text-sm">(<?php echo htmlspecialchars($media->file_size ?? 'N/A'); ?> bytes)</span>
                        </div>
                        <p class="text-xs text-gray-600 mb-2">Uploaded by: <?php echo htmlspecialchars($media->uploader ?? 'Unknown uploader'); ?></p>
                        <div class="mt-4">
    <span class="inline-block bg-<?php echo $media->status === 'approved' ? 'green' : ($media->status === 'needs work' ? 'yellow' : 'red'); ?>-200 text-<?php echo $media->status === 'approved' ? 'green' : ($media->status === 'needs work' ? 'yellow' : 'red'); ?>-700 text-xs px-2 py-1 rounded-full">
        Status: <?php echo htmlspecialchars($media->status ?? 'unknown'); ?>
    </span>
    <?php if (isset($media->status_changed_at)): ?>
        <p class="text-xs text-gray-500 mt-1">
            Status changed on: <?php echo $media->status_changed_at->toDateTime()->format('Y-m-d H:i:s'); ?>
        </p>
    <?php endif; ?>
    <?php if (isset($media->updated_by)): ?>
        <p class="text-xs text-gray-500 mt-1">Updated by: <?php echo htmlspecialchars($media->updated_by ?? 'Unknown'); ?></p>
    <?php endif; ?>
    <?php if (isset($media->comment) && !empty($media->comment)): ?>
        <p class="text-xs text-gray-500 mt-1">Comment: <?php echo htmlspecialchars($media->comment); ?></p>
    <?php endif; ?>
</div>


                        <!-- Comments Section -->
                        <div class="mt-4">
                            <h3 class="font-semibold text-gray-700 mb-2">Comments</h3>
                            <ul class="space-y-2 border-t border-gray-200 pt-3">
                                <?php if (!empty($media->comments)): ?>
                                    <?php foreach ($media->comments as $comment): ?>
                                        <li class="bg-gray-100 p-3 rounded-md shadow-sm">
                                            <span class="text-gray-800 font-semibold"><?php echo htmlspecialchars($comment['username']); ?></span>
                                            <span class="text-xs text-gray-500 ml-2">
                                                <?php 
                                                    if (isset($comment['comment_date'])) {
                                                        $commentDate = $comment['comment_date']->toDateTime();
                                                        $now = new DateTime();
                                                        $diff = $now->diff($commentDate);
                                                        
                                                        if ($diff->y > 0) {
                                                            echo $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
                                                        } elseif ($diff->m > 0) {
                                                            echo $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
                                                        } elseif ($diff->d > 0) {
                                                            echo $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
                                                        } elseif ($diff->h > 0) {
                                                            echo $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                                                        } elseif ($diff->i > 0) {
                                                            echo $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                                                        } else {
                                                            echo 'Just now';
                                                        }
                                                    } else {
                                                        echo 'Just now';
                                                    }
                                                ?>
                                            </span>
                                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="text-gray-500">No comments yet.</li>
                                <?php endif; ?>
                            </ul>
                            <form method="post" class="mt-3">
                                <input type="hidden" name="media_id" value="<?php echo htmlspecialchars($media->_id); ?>">
                                <textarea name="comment" required placeholder="Add your comment..." class="w-full mt-2 p-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"></textarea>
                                <button type="submit" class="w-full mt-2 bg-blue-600 text-white rounded-lg py-2 font-semibold hover:bg-blue-500 transition">Submit Comment</button>
                            </form>
                        </div>

                        <!-- Admin Status Update (Only for admins) -->
                        <?php if (isset($user->role) && $user->role === 'admin'): ?>
                            <form method="post" action="update_status.php" class="mt-4">
                                <input type="hidden" name="media_id" value="<?php echo htmlspecialchars($media->_id); ?>">
                                <select name="status" class="w-full mt-2 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="approved" <?php echo $media->status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="needs work" <?php echo $media->status === 'needs work' ? 'selected' : ''; ?>>Needs Work</option>
                                    <option value="rejected" <?php echo $media->status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                                <button type="submit" class="w-full mt-2 bg-green-600 text-white rounded-lg py-2 font-semibold hover:bg-green-500 transition">Update Status</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Logout Button -->
        <div class="flex justify-center mt-6">
            <a href="logout.php" class="text-red-500 hover:underline flex items-center font-semibold">
                <i class="fas fa-sign-out-alt mr-1"></i> Logout
            </a>
        </div>
    </div>

    <!-- Media Preview Script -->
    <script>
        const mediaFileInput = document.getElementById('mediaFileInput');
        const mediaPreview = document.getElementById('mediaPreview');
        const previewContainer = document.getElementById('previewContainer');

        mediaFileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            previewContainer.innerHTML = '';

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewElement = file.type.startsWith('image/') ? 'img' : 'video';
                    const element = document.createElement(previewElement);
                    element.src = e.target.result;
                    element.className = 'rounded-md shadow-lg w-full h-auto';
                    if (file.type.startsWith('video/')) element.controls = true;
                    previewContainer.appendChild(element);
                    mediaPreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                mediaPreview.classList.add('hidden');
            }
        });
    </script>
</body>
</html>

