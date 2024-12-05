<?php
class Media {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    private $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'video/mp4',
        'video/webm',
        'video/ogg'
    ];

    public function uploadMedia($file, $username) {
        $targetDirectory = 'uploads/';
        $targetFile = $targetDirectory . basename($file['name']);

        // Check if the file type is allowed
        if (!in_array($file['type'], $this->allowedMimeTypes)) {
            return false;
        }
    
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Prepare metadata with error handling
            $metadata = [
                'installationSite' => isset($_POST['installation_site']) ? $_POST['installation_site'] : '',
                'date' => isset($_POST['date']) ? $_POST['date'] : null, // Default to null if not set
                'responsibleIndividuals' => isset($_POST['responsible_individuals']) ? $_POST['responsible_individuals'] : ''
            ];
    
            // Save media details to the database
            $this->db->media->insertOne([
                'filename' => $file['name'],
                'filepath' => $targetFile,
                'uploader' => $username,
                'upload_date' => new MongoDB\BSON\UTCDateTime(),
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'comments' => [], // Array to hold comments
                'status' => 'needs work', // Default status
                'installationSite' => $metadata['installationSite'],
                'date' => $metadata['date'],
                'responsibleIndividuals' => $metadata['responsibleIndividuals']
            ]);
            return true;
        }
        return false;
    }
    public function isImage($mimeType) {

        return preg_match('/^image\//', $mimeType);

    }



    public function isVideo($mimeType) {

        return preg_match('/^video\//', $mimeType);

    }

    public function getAllMedia() {
        return $this->db->media->find();
    }

    public function getMediaByUser($username) {
        return $this->db->media->find(['uploader' => $username]);
    }
}

class Feedback {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addComment($mediaId, $username, $commentText) {
        $comment = [
            'username' => $username,
            'comment' => $commentText,
            'comment_date' => new MongoDB\BSON\UTCDateTime() // Add timestamp here
        ];

        $this->db->media->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($mediaId)],
            ['$push' => ['comments' => $comment]]
        );
    } 

    
}
?>
