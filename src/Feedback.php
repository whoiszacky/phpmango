<?php

namespace App;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Feedback {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addComment($mediaId, $username, $commentText) {
        $comment = [
            'username' => $username,
            'comment' => $commentText,
            'comment_date' => new \MongoDB\BSON\UTCDateTime()
        ];

        $this->db->media->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($mediaId)],
            ['$push' => ['comments' => $comment]]
        );
    }

    public function toggleLike($mediaId, $username) {
        // Find the media item by its ID
        $media = $this->db->media->findOne(['_id' => new \MongoDB\BSON\ObjectId($mediaId)]);
        
        if ($media) {
            // Get current likes or initialize as an empty array
            $likes = $media->likes ?? [];

            // Check if user has already liked the media
            if (in_array($username, $likes)) {
                // If the user has already liked, remove them from the likes array
                $likes = array_diff($likes, [$username]);
            } else {
                // If the user hasn't liked yet, add them to the likes array
                $likes[] = $username;
            }

            // Update the media document with the new likes array
            $this->db->media->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectId($mediaId)],
                ['$set' => ['likes' => $likes]]
            );
        }
    }
}
