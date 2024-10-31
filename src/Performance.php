<?php
class Performance {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Log user activity
    public function logActivity($userId, $action) {
        try {
            return $this->db->activity_logs->insertOne([
                'userId' => $userId,
                'action' => $action,
                'timestamp' => new MongoDB\BSON\UTCDateTime() // Store timestamp in UTC
            ]);
        } catch (Exception $e) {
            // Handle the exception, log it or notify admin
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }

    // Get activity report for a specific user
    public function getUserActivityReport($userId, $limit = 50) {
        try {
            return $this->db->activity_logs->find(
                ['userId' => $userId],
                ['limit' => $limit, 'sort' => ['timestamp' => -1]] // Sort by latest first
            )->toArray();
        } catch (Exception $e) {
            // Handle the exception, log it or notify admin
            error_log("Error retrieving activity report: " . $e->getMessage());
            return [];
        }
    }
}
?>
