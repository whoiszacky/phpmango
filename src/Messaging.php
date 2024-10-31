// src/Messaging.php
class Messaging {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function sendMessage($fromUserId, $toUserId, $message) {
        return $this->db->messages->insertOne([
            'from' => $fromUserId,
            'to' => $toUserId,
            'message' => $message,
            'timestamp' => time()
        ]);
    }

    public function getMessages($userId) {
        return $this->db->messages->find([
            '$or' => [
                ['from' => $userId],
                ['to' => $userId]
            ]
        ])->toArray();
    }
}
