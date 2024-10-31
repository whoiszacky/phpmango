<?php

namespace App; // Use your desired namespace

class Feedback {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addComment($mediaId, $username, $commentText) {
        $comment = [
            'username' => $username,
            'comment' => $commentText,
            'comment_date' => new MongoDB\BSON\UTCDateTime() // Ensure this line is present
        ];

        $this->db->media->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($mediaId)],
            ['$push' => ['comments' => $comment]]
        );
    }
}
