<?php
require '../src/db.php'; // Ensure this path is correct
// Include the database connection file

// Connect to MongoDB
$db = getDbConnection();
$collection = $db->users;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert user into MongoDB
    $result = $collection->insertOne([
        'username' => $username,
        'password' => $password
    ]);

    if ($result->getInsertedCount() == 1) {
        echo "New record created successfully";
        header("Location: index.php");
        exit();
    } else {
        echo "Error: Could not create user";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Management - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-md rounded-lg p-6 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Register</h1>
        <form method="post">
            <div class="mb-4">
                <label class="block mb-1">Username:</label>
                <input type="text" name="username" class="border rounded-md w-full p-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Password:</label>
                <input type="password" name="password" class="border rounded-md w-full p-2" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white rounded-md py-2 px-4 w-full">Register</button>
        </form>
        <p class="text-center mt-4">
            <a href="index.php" class="text-blue-500">Already have an account? Login</a>
        </p>
    </div>
</body>
</html>