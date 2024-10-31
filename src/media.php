<?php
class Media {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function uploadMedia($file, $username) {
        $targetDirectory = 'uploads/';
        $targetFile = $targetDirectory . basename($file['name']);
    
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
    
    

    public function getMediaByUser($username) {
        return $this->db->media->find(['uploader' => $username]);
    }
}
?>
