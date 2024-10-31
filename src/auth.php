<?php
require '../vendor/autoload.php'; // Ensure the path is correct
use Firebase\JWT\JWT;

class Auth {
    private $db;
    private $jwtSecret = 'your_jwt_secret'; // Your JWT secret key

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->db->users->insertOne(['username' => $username, 'password' => $hashedPassword]);
        return true;
    }

    public function login($username, $password) {
        $user = $this->db->users->findOne(['username' => $username]);

        if ($user && password_verify($password, $user->password)) {
            $payload = [
                'iat' => time(),
                'exp' => time() + (60 * 60), // 1 hour expiration
                'sub' => (string)$user->_id,
            ];

            // Specify the algorithm while encoding
            return JWT::encode($payload, $this->jwtSecret, 'HS256'); // Include the algorithm
        }
        return null;
    }

    public function verifyToken($token) {
        try {
            return JWT::decode($token, new \Firebase\JWT\Key($this->jwtSecret, 'HS256')); // Include the algorithm
        } catch (Exception $e) {
            return null;
        }
    }
}
