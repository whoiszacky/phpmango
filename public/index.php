<?php
session_start();
require '../src/db.php'; // Ensure the path is correct
require '../src/auth.php';

// Get the database connection
$db = getDbConnection(); // Initialize the database connection

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

$auth = new Auth($db); // Pass the $db connection to the Auth class

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($auth->login($username, $password)) {
        $_SESSION['user_id'] = $username; // Assuming the username is unique
        header('Location: profile.php');
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Management - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-md rounded-lg p-6 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Login</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-600"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post">
            <div class="mb-4">
                <label class="block mb-1">Username:</label>
                <input type="text" name="username" class="border rounded-md w-full p-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Password:</label>
                <input type="password" name="password" class="border rounded-md w-full p-2" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white rounded-md py-2 px-4 w-full">Login</button>
        </form>
        <p class="text-center mt-4">
            <a href="register.php" class="text-blue-500">Create an account</a>
        </p>
    </div>
</body>
</html>
