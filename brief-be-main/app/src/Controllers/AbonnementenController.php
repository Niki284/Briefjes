<?php

namespace Controllers;

use Services\JWTService;
use Services\Mailer;

class AbonnementenController extends ApiBaseController
{
    // GET /api/subscriptions
    public function getAllSubscriptions(): void
    {
        $stmt = $this->conn->prepare("SELECT * FROM subscriptions");
        $result = $stmt->executeQuery();
        $subscriptions = $result->fetchAllAssociative();

        echo json_encode(['subscriptions' => array_values($subscriptions)]);
    }

    // GET /api/subscriptions/me
    public function getMySubscriptions(): void
    {
        $userId = JWTService::getUserIdFromToken();

        $stmt = $this->conn->prepare("SELECT * FROM subscriptions WHERE users_id = ?");
        $result = $stmt->executeQuery([$userId]);
        $subscriptions = $result->fetchAllAssociative();

        echo json_encode(['subscriptions' => array_values($subscriptions)]);
    }

    // POST /api/subscriptions
    public function createSubscription(): void
    {
        $this->httpBody = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON format']);
            return;
        }

        // Strip spaces around keys (bugfix voor 'users_id ' met spatie)
        $data = array_map('trim', $this->httpBody);

        // Vereiste velden
        $required = ['users_id', 'users_rol', 'channels_id', 'approved', 'created_at'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: $field"]);
                return;
            }
        }

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO subscriptions (users_id, users_rol, channels_id, approved, created_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->executeQuery([
                $data['users_id'],
                $data['users_rol'],
                $data['channels_id'],
                $data['approved'],
                $data['created_at']
            ]);

            http_response_code(201);
            echo json_encode(['message' => 'Subscription created successfully']);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    // PATCH /api/subscriptions/{id}/approve
    public function approveSubscription($id): void
    {
        try {
            $stmt = $this->conn->prepare("UPDATE subscriptions SET approved = 1 WHERE id = ?");
            $stmt->executeQuery([$id]);

            echo json_encode(['message' => "Subscription $id approved."]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    // DELETE /api/subscriptions/{id}
    public function deleteSubscription($id): void
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM subscriptions WHERE id = ?");
            $stmt->executeQuery([$id]);

            echo json_encode(['message' => "Subscription $id deleted."]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
