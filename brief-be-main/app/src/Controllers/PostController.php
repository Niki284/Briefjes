<?php

namespace Controllers;

use Services\JWTService;
use Services\Mailer;


class PostController extends ApiBaseController {
    private $posts;
   // GET /api/posts
    public function getAllPosts() {
        $stmt = $this->conn->prepare("SELECT * FROM posts");
        $result = $stmt->executeQuery();
        $this->posts = $result->fetchAllAssociative();
        echo json_encode(['posts' => array_values($this->posts)]);
    }         

    // POST /api/channels/{channelId}/posts
public function createPostInChannel($channelId) {
    $this->httpBody = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        return;
    }

    // Required field check
    $required = ['title', 'content', 'pdf_path', 'users_id', 'users_rol'];
    foreach ($required as $field) {
        if (empty($this->httpBody[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing or empty field: $field"]);
            return;
        }
    }

    // Validate channelId
    if (!is_numeric($channelId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid channel ID']);
        return;
    }

    // Check if channel exists
    $stmt = $this->conn->prepare("SELECT COUNT(*) FROM channels WHERE id = :channelId");
    $stmt->bindValue(':channelId', $channelId);
    $result = $stmt->executeQuery()->fetchOne();

    if ($result == 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Channel not found']);
        return;
    }

    try {
        $stmt = $this->conn->prepare("
            INSERT INTO posts (title, content, pdf_path, users_id, users_rol, channels_id)
            VALUES (:title, :content, :pdf_path, :users_id, :users_rol, :channels_id)
        ");
        $stmt->bindValue(':title', $this->httpBody['title']);
        $stmt->bindValue(':content', $this->httpBody['content']);
        $stmt->bindValue(':pdf_path', $this->httpBody['pdf_path']);
        $stmt->bindValue(':users_id', $this->httpBody['users_id']);
        $stmt->bindValue(':users_rol', $this->httpBody['users_rol']);
        $stmt->bindValue(':channels_id', $channelId);

        $stmt->executeQuery();

        http_response_code(201);
        echo json_encode([
            'message' => 'Post created successfully',
            'post' => [
                'title' => $this->httpBody['title'],
                'channel_id' => $channelId
            ]
        ]);
    } catch (\Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

}