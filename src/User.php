<?php
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get user role
    public function getUserRole($username) {
        $user = $this->db->users->findOne(['username' => $username]);
        return $user ? $user->role : null;
    }

    // Set user role
    public function setUserRole($username, $role) {
        $this->db->users->updateOne(
            ['username' => $username],
            ['$set' => ['role' => $role]]
        );
        return true;
    }
}
?>
