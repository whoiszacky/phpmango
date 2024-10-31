<?php


class Feedback {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addComment($mediaId, $username, $comment) {
        return $this->db->media->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($mediaId)],
            ['$push' => ['comments' => ['username' => $username, 'comment' => $comment]]]
        );
    }

 /*    public function getComments($mediaId) {
        $media = $this->db->media->findOne(['_id' => new MongoDB\BSON\ObjectId($mediaId)]);
        return $media ? $media->comments : [];
    } */
    public function getComments($mediaId) {
        $comments = $this->db->comments->find(['media_id' => $mediaId])->toArray();
        return $comments; // Ensure this returns an array
    }
    
}

?>
