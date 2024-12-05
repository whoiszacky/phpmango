<?php
class Admin {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createTask($taskDetails) {
        return $this->db->tasks->insertOne($taskDetails);
    }

    public function assignTask($taskId, $userId) {
        return $this->db->tasks->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($taskId)],
            ['$set' => ['assignedTo' => $userId]]
        );
    }

    public function getTasks() {
        return $this->db->tasks->find()->toArray();
    }
}
?>

