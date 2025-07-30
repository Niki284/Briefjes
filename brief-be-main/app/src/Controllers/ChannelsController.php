<?php

namespace Controllers;

use Services\JWTService;
use Services\Mailer;


class ChannelsController extends ApiBaseController {
    private $channels;

   public function getAllChannels() {
    $userId = JWTService::getUserIdFromToken();
    $roles = JWTService::getUserRolesFromToken();
    $organizations_id = JWTService::getOrganizationIdFromToken();

    if (!$userId) {
        http_response_code(401);
        echo json_encode(['error' => 'Niet geauthenticeerd']);
        return;
    }
    // if (!$roles) {
    //     http_response_code(403);
    //     echo json_encode(['error' => 'Geen toegangsrechten']);
    //     return;
    // }  
    // if (!$organizations_id) {
    //     http_response_code(401);
    //     echo json_encode(['error' => 'Niet organisatie gevonden']);
    //     return;
    // }
    

    $isBeheerder = in_array("beheerder", $roles);

    if ($isBeheerder) {
        // Beheerder → alles ophalen, GEEN bindValue nodig
        $stmt = $this->conn->prepare("SELECT * FROM channels");
    } else {
        // Abonnee → alleen eigen organisatie
        $stmt = $this->conn->prepare("SELECT * FROM channels WHERE organizations_id = :id");
        $stmt->bindValue(':id', $organizations_id);
    }
    // var_dump($stmt);

    $result = $stmt->executeQuery();
    $channels = $result->fetchAllAssociative();

    echo json_encode(['channels' => $channels]);
}


   // GET /api/organisations
    // public function getAllChannels() {
    //      $userId = JWTService::getUserIdFromToken();
    // $roles = JWTService::getUserRolesFromToken();
    // $organizationId = JWTService::getOrganizationIdFromToken();
    //     $stmt = $this->conn->prepare("SELECT * FROM channels");
    //     $result = $stmt->executeQuery();
    //     $this->channels = $result->fetchAllAssociative();
    //     echo json_encode(['channels' => array_values($this->channels)]);
    // }   
    
    // POST /api/channels
    public function createChannel() {
        $this->httpBody = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON format']);
            return;
        }

        // Validate required fields for channels
        if (!isset($this->httpBody['name']) ||
            !isset($this->httpBody['organizations_id'])) 
            
            {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        try {
             // For example, you might insert the channel into the database
        $stmt = $this->conn->prepare("INSERT INTO channels (name, organizations_id) VALUES (:name, :organizations_id)");
        $stmt->bindValue(':name', $this->httpBody['name']);
        $stmt->bindValue(':organizations_id', $this->httpBody['organizations_id']);
        $stmt->executeQuery();

        http_response_code(201);
        // echo json_encode(['message' => 'Channel created successfully']);

         echo json_encode([
            'message' => 'Channel created successfully',
            'channel' => [
                'name' => $this->httpBody['name'],
                'organizations_id' => $this->httpBody['organizations_id']
            ]
        ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }  
      
    public function getChannelPosts($id) {
        $stmt = $this->conn->prepare("
            SELECT 
                c.id AS channels_id, 
                c.name AS channel_name, 
                c.organizations_id, 
                p.id AS post_id, 
                p.title, 
                p.content, 
                p.created_at, 
                p.users_id, 
                p.users_rol, 
                p.channels_id 
            FROM channels c 
            LEFT JOIN posts p ON c.id = p.channels_id 
            WHERE c.id = :id
        ");
        $stmt->bindValue(':id', $id);
        $result = $stmt->executeQuery();
        $data = $result->fetchAllAssociative();

        if (!$data) {
            http_response_code(404);
            echo json_encode(['error' => 'Channel not found']);
            return;
        }

        $channel = [
            'id' => $data[0]['channels_id'],
            'name' => $data[0]['channel_name'],
            'organizations_id' => $data[0]['organizations_id']
        ];

        $posts = [];

        foreach ($data as $row) {
            if ($row['post_id'] !== null) {
                $posts[] = [
                    'id' => $row['post_id'],
                    'title' => $row['title'],
                    'content' => $row['content'],
                    'created_at' => $row['created_at'],
                    'users_id' => $row['users_id'],
                    'users_rol' => $row['users_rol'],
                    'channels_id' => $row['channels_id']
                ];
            }
        }

        echo json_encode([
            'channel' => $channel,
            'posts' => $posts
        ]);
    }

    // GET /api/channels/{channelId}/posts/{postId}
    public function getChannelPostById($channelId, $postId) {
        $userId = JWTService::getUserIdFromToken();
        $roles = JWTService::getUserRolesFromToken();

        // Als de gebruiker GEEN beheerder is, controleren of hij geabonneerd is
        if (!in_array('beheerder', $roles)) {
                $stmt = $this->conn->prepare("
                SELECT 1 FROM subscriptions 
                WHERE user_id = :userId 
                AND channel_id = :channelId 
                AND approved = 1
            ");
                $stmt->bindValue(':userId', $userId);
                $stmt->bindValue(':channelId', $channelId);
                $subscription = $stmt->executeQuery()->fetchOne();

            if (!$subscription) {
                http_response_code(403);
                echo json_encode(['error' => 'Geen toegang: je bent niet geabonneerd op dit kanaal']);
                return;
            }
        }

        // Post ophalen
        $stmt = $this->conn->prepare("
            SELECT * FROM posts 
            WHERE id = :postId AND channels_id = :channelId
        ");
        $stmt->bindValue(':postId', $postId);
        $stmt->bindValue(':channelId', $channelId);
        $result = $stmt->executeQuery();
        $post = $result->fetchAssociative();

        if ($post) {
            echo json_encode(['post' => $post]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Post not found in this channel']);
        }
    }

    // DELETE /api/channels/{id}isations/{id}/channels
    public function deleteChannel($id) {
        $stmt = $this->conn->prepare("DELETE FROM channels WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $result = $stmt->executeQuery();

        if ($result->rowCount() > 0) {
            http_response_code(204); // No Content
            echo json_encode(['message' => 'Channel deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Channel not found']);
        }
    }

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