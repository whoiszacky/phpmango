
<?php
class Annotations {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addAnnotation($mediaId, $userId, $annotationText, $coordinates) {
        return $this->db->annotations->insertOne([
            'mediaId' => $mediaId,
            'userId' => $userId,
            'annotationText' => $annotationText,
            'coordinates' => $coordinates,
            'timestamp' => time()
        ]);
    }

    public function getAnnotations($mediaId) {
        return $this->db->annotations->find(['mediaId' => $mediaId])->toArray();
    }
}
