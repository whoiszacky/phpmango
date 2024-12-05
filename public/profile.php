<?php
// Start the session only if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files using require_once to avoid redeclaration errors
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/media.php';
require_once '../src/feedback.php';

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
$feedbackHandler = new Feedback($db);

// Handle media upload
$uploadMessage = '';
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

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Retrieve uploaded media
$allMedia = $mediaHandler->getAllMedia();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Vault - Manage Your Files</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f6f8f9 0%, #e5ebee 100%);
        }
        .media-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .media-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <header class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-cloud-upload-alt text-3xl mr-4"></i>
                <h1 class="text-3xl font-bold">Media Vault</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-lg">Welcome, <?php echo htmlspecialchars($user->username); ?></span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition flex items-center">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 flex-grow">
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Upload Section -->
            <div class="md:col-span-1 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-upload mr-3 text-blue-600"></i>Upload Media
                </h2>
                <form method="post" enctype="multipart/form-data" class="space-y-4">
                    <div class="border-2 border-dashed border-blue-200 rounded-lg p-6 text-center">
                        <input type="file" name="media_file" id="mediaFileInput" class="hidden" accept="image/*,video/*,audio/*,.pdf,.doc,.docx" required>
                        <label for="mediaFileInput" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-4xl text-blue-500 mb-4 block"></i>
                            <p class="text-gray-600">Drag and drop or click to upload</p>
                            <p class="text-xs text-gray-500">Supports: Images, Videos, Audio, PDF, Docs</p>
                        </label>
                    </div>
                    <div id="mediaPreview" class="mt-4 hidden">
                        <h3 class="text-gray-700 font-semibold mb-2">Preview:</h3>
                        <div id="previewContainer" class="flex items-center justify-center bg-gray-100 rounded-lg p-4"></div>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-3 rounded-lg hover:opacity-90 transition">
                        Upload File
                    </button>
                </form>
                <?php if ($uploadMessage): ?>
                    <div class="mt-4 text-center <?php echo strpos($uploadMessage, 'success') !== false ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $uploadMessage; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Media Gallery -->
            <div class="md:col-span-2">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-images mr-3 text-blue-600"></i>Your Media Library
                </h2>
                <?php if (empty($allMedia)): ?>
                    <div class="bg-white rounded-lg shadow-md p-8 text-center">
                        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600">Your media library is empty. Start uploading!</p>
                    </div>
                <?php else: ?>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($allMedia as $media): ?>
                            <div class="bg-white rounded-lg shadow-md media-card overflow-hidden">
                                <!-- Media Thumbnail -->
                                <div class="relative">
                                <img src="<?php echo htmlspecialchars($media->filepath ?? ''); ?>" alt="<?php echo htmlspecialchars($media->filename ?? 'Unknown file'); ?>" class="w-full h-auto object-cover rounded-md">
                                    
                                    <span class="absolute top-2 right-2 bg-<?php 
                                        echo $media->status === 'approved' ? 'green' : 
                                             ($media->status === 'needs work' ? 'yellow' : 'red'); 
                                    ?>-500 text-white text-xs px-2 py-1 rounded">
                                        <?php echo htmlspecialchars(ucfirst($media->status)); ?>
                                    </span>
                                </div>

                                <!-- Media Details -->
                                <div class="p-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <a href="<?php echo htmlspecialchars($media->filepath); ?>" target="_blank" class="text-blue-600 hover:underline font-medium truncate">
                                            <?php echo htmlspecialchars($media->filename); ?>
                                        </a>
                                        <span class="text-gray-500 text-xs"><?php echo htmlspecialchars($media->file_size); ?> bytes</span>
                                    </div>

                                    <!-- Comments Section -->
                                    <div class="mt-4 border-t pt-3">
                                        <h4 class="font-semibold text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-comments mr-2 text-blue-500"></i>Comments
                                        </h4>
                                        <div class="space-y-2 max-h-32 overflow-y-auto">
                                            <?php if (!empty($media->comments)): ?>
                                                <?php foreach ($media->comments as $comment): ?>
                                                    <div class="bg-gray-100 p-2 rounded text-sm">
                                                        <div class="flex justify-between">
                                                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($comment['username']); ?></span>
                                                            <span class="text-xs text-gray-500">
                                                                <?php 
                                                                    $commentDate = $comment['comment_date']->toDateTime();
                                                                    $now = new DateTime();
                                                                    $diff = $now->diff($commentDate);
                                                                    
                                                                    if ($diff->y > 0) echo $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
                                                                    elseif ($diff->m > 0) echo $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
                                                                    elseif ($diff->d > 0) echo $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
                                                                    elseif ($diff->h > 0) echo $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                                                                    elseif ($diff->i > 0) echo $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                                                                    else echo 'Just now';
                                                                ?>
                                                            </span>
                                                        </div>
                                                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p class="text-gray-500 text-center">No comments</p>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Add Comment Form -->
                                        <form method="post" class="mt-3">
                                            <input type="hidden" name="media_id" value="<?php echo htmlspecialchars($media->_id); ?>">
                                            <div class="flex">
                                                <input type="text" name="comment" required placeholder="Add a comment..." 
                                                       class="w-full p-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <button type="submit" class="bg-blue-600 text-white px-4 rounded-r-lg hover:bg-blue-700">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <!-- status update  -->
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
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Media Vault. All Rights Reserved.</p>
        </div>
    </footer>

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
                    element.className = 'rounded-md max-h-64 object-contain';
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