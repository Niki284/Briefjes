<?php

namespace Controllers;

use Services\JWTService;
use Services\Mailer;


class BriefjesController extends ApiBaseController {
    private $channels;
   // GET /api/posts
    public function getAllPosts() {
        $stmt = $this->conn->prepare("SELECT * FROM channels");
        $result = $stmt->executeQuery();
        $this->channels = $result->fetchAllAssociative();
        echo json_encode(['channels' => array_values($this->channels)]);
    }         

    // GET /api/posts/{id}
    public function getPostById($id) {}  
    
    // POST /api/posts
    public function createPost() {
        
    }           
}