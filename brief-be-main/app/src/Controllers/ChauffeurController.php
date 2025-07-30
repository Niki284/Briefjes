<?php

namespace Controllers;

use mysql_xdevapi\Exception;
use Services\JWTService;
use Services\Mailer;

class ChauffeurController extends ApiBaseController
{

    private $drivers;

    public function getAll(): void
    {
        $stmt = $this->conn->prepare("SELECT name, surname, email FROM users2 WHERE role = 'driver'");
        $result = $stmt->executeQuery();
        $this->drivers = $result->fetchAllAssociative();
        if ($this->drivers) {
            echo json_encode(['drivers' => array_values($this->drivers)]);
        } else {
            $this->message(404, "no drivers found");
        }
    }

    public function addInfo(): void
    {
        try {
            $headers = apache_request_headers();

            if (isset($headers['Authorization'])) {
                $jwtToken = str_ireplace('Bearer ', '', $headers['Authorization']);
            }
        } catch (\Exception $e) {
            $this->message(501, "failed to authorize: " . $e->getMessage());
        }
        try {
            $decodedPayload = JWTService::validateJWTToken($jwtToken, $_ENV['SECRET_KEY']);
            $driver = $decodedPayload->sub;
            $roles = $decodedPayload->roles;
            if (in_array('driver', $roles)) {
                try {
                    $this->httpBody = json_decode(file_get_contents('php://input'), true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->message(400, 'Invalid JSON format');
                        return;
                    }

                    if (!isset($this->httpBody['password']) || !isset($this->httpBody['birthdate'])
                        || !isset($this->httpBody['gender']) || !isset($this->httpBody['brand'])
                        || !isset($this->httpBody['model']) || !isset($this->httpBody['seats'])
                        || !isset($this->httpBody['plate'])) {
                        $this->message(400, 'Missing required fields in request body');
                        return;
                    }
                    try {
                        $hashedPassword = password_hash($this->httpBody['password'], PASSWORD_DEFAULT);
                        $stmt = $this->conn->prepare('UPDATE users2 SET password = :password, birthdate = :birthdate, gender = :gender, brand = :brand, model = :model, seats = :seats, plate = :plate, isconfirmed = 1 WHERE id = :id');
                        $stmt->bindValue(':password', $hashedPassword);
                        $stmt->bindValue(':birthdate', $this->httpBody['birthdate']);
                        $stmt->bindValue(':gender', $this->httpBody['gender']);
                        $stmt->bindValue(':brand', $this->httpBody['brand']);
                        $stmt->bindValue(':model', $this->httpBody['model']);
                        $stmt->bindValue(':seats', $this->httpBody['seats']);
                        $stmt->bindValue(':plate', $this->httpBody['plate']);
                        $stmt->bindValue(':id', $driver);


                        $stmt->executeQuery();

                        $this->message(200, 'Driver updated.');
                    } catch (\Exception $e) {
                        $this->message(500, 'Database error: ' . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->message(500, "Error getting params: " . $e->getMessage());
                }
            } else {
                $this->message(401, "Invalid Credentials.");

            }
        } catch (\Exception $e) {
            $this->message(500, "error validating token: " . $e->getMessage());
        }
    }
}