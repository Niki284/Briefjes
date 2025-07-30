<?php

namespace Controllers;

use Services\JWTService;
use Services\Mailer;

class BookController extends ApiBaseController
{
    private $drives;

    public function overview()
    {
        $stmt = $this->conn->prepare("SELECT * FROM drives WHERE status = 'reserved'");
        $result = $stmt->executeQuery();
        $this->drives = $result->fetchAllAssociative();
        echo json_encode(['bookings' => array_values($this->drives)]);
    }

    public function search($id)
    {
        if (is_numeric($id) && (int)$id == $id) {
            $stmt = $this->conn->prepare("SELECT * FROM drives WHERE id = :id");
            $stmt->bindValue(':id', $id);
            $result = $stmt->executeQuery();

            $this->drives = $result->fetchAllAssociative();
            if ($this->drives) {
                echo json_encode(['bookings' => array_values($this->drives)]);
            } else {
                $this->message(404, "no booking found");
            }
        } else {
            $this->message(400, "invalid ID requested");
        }
    }

    public function create()
    {
        $this->httpBody = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->message(400, 'Invalid JSON format');
            return;
        }

        if (!isset($this->httpBody['first-name']) || !isset($this->httpBody['last-name']) || !isset($this->httpBody['email']) ||
            !isset($this->httpBody['pickup-location']) || !isset($this->httpBody['dropoff-location']) || !isset($this->httpBody['ride-date']) || !isset($this->httpBody['ride-time'])) {
            $this->message(400, 'Missing required fields in request body');
            return;
        }

        try {
            $datetime = $this->httpBody['ride-date'] . ' ' . $this->httpBody['ride-time'];
            $stmt = $this->conn->prepare("INSERT INTO drives (pickup, dropoff, price, datetime, status, payed, name, surname, email) VALUES (:pickup, :dropoff, :price, :datetime, :status, :payed, :name, :surname, :email)");
            $stmt->bindValue(':pickup', $this->httpBody['pickup-location']);
            $stmt->bindValue(':dropoff', $this->httpBody['dropoff-location']);
            $stmt->bindValue(':price', 25);
            $stmt->bindValue(':datetime', $datetime);
            $stmt->bindValue(':status', "reserved");
            $stmt->bindValue(':payed', 0);
            $stmt->bindValue(':name', $this->httpBody['first-name']);
            $stmt->bindValue(':surname', $this->httpBody['last-name']);
            $stmt->bindValue(':email', $this->httpBody['email']);
            $stmt->executeQuery();

            if ($this->httpBody['userId']) {
                $stmt = $this->conn->prepare('UPDATE drives SET user_id = :userid WHERE id = LAST_INSERT_ID()');
                $stmt->bindValue(':userid', $this->httpBody['userId']);
                $stmt->executeQuery();
            }

            try {
                $text = "Beste,\n\nWe hebben je reservatie goed ontvangen! We laten je weten wanneer je boeking bevestigd is door een chauffeur.\n\nMet vriendelijke groeten,\nTeam Downtown Cab Co.";
                Mailer::sendMail($this->httpBody['email'], "Reservatie ontvangen.", "", "<p>$text</p>");
            } catch (\Exception $e) {
                $this->message(500, 'Error sending mail: ' . $e->getMessage());
            }
            $this->message(201, 'Booking created successfully');

        } catch (\Exception $e) {
            $this->message(500, 'Database error: ' . $e->getMessage());
        }
    }

    public
    function update($id)
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
                    $stmt = $this->conn->prepare("UPDATE drives SET status = 'planned', driver_id = :driver WHERE id = :id AND status = 'reserved'");
                    $stmt->bindValue(':id', $id);
                    $stmt->bindValue(':driver', $driver);
                    $stmt->executeQuery();

                    try {
                        $stmt = $this->conn->prepare("SELECT email, dropoff, datetime FROM drives WHERE id= :id");
                        $stmt->bindValue(':id', $id);
                        $result = $stmt->executeQuery();
                        $booking = $result->fetchAssociative();
                        $stmt = $this->conn->prepare("SELECT name, email FROM users2 WHERE id= :id");
                        $stmt->bindValue(':id', $driver);
                        $result = $stmt->executeQuery();
                        $drivername = $result->fetchAssociative();
                        $text = "Beste,\n\nJe taxirit naar " . $booking['dropoff'] . " is zonet bevestigd door " . $drivername["name"] . "!\n\nmet vriendelijke groeten,\nTeam Downtown Cab Co.";
                        Mailer::sendMail($booking['email'], "Reservatie (" . $id . ") bevestigd", "", "<p>$text</p>");
                        $text = "Beste,\n\nU heeft zonet een reservatie bevestigd naar " . $booking["dropoff"] . " om " . $booking["datetime"] . ".\n\nMet vriendelijke groeten,\nTeam Down townCab Co.";
                        Mailer::sendMail($drivername["email"], "Reservatie (" . $id . ") bevestigd", "", "<p>$text</p>");

                    } catch (\Exception $e) {
                        $this->message(500, 'Error sending mails: ' . $e->getMessage());
                    }
                    $this->message(200, "Booking succesfully claimed");
                } catch (\Exception $e) {
                    $this->message(500, "Database error: " . $e->getMessage());
                }
            } else {
                $this->message(401, "Invalid Credentials.");
            }
        } catch
        (\Exception $e) {
            $this->message(401, "error validating token: " . $e->getMessage());
        }
    }
}