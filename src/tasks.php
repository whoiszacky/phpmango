<?php
class TaskManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Create a new task
    public function createTask($title, $description, $assignedTo) {
        $this->db->tasks->insertOne([
            'title' => $title,
            'description' => $description,
            'assigned_to' => $assignedTo,
            'status' => 'pending',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
        ]);
        return true;
    }

    // Get tasks for a user
    public function getTasksForUser($username) {
        return $this->db->tasks->find(['assigned_to' => $username]);
    }

    // Update task status
    public function updateTaskStatus($taskId, $status) {
        $this->db->tasks->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($taskId)],
            ['$set' => ['status' => $status]]
        );
        return true;
    }
}
?>
