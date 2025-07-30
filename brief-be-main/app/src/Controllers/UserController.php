<?php

namespace Controllers;

use Doctrine\DBAL\Exception;
use http\Env;
use Services\JWTService;
use Services\Mailer;

class UserController extends ApiBaseController
{
    private $users;

    public function getAll(): void
    {
        $stmt = $this->conn->prepare("SELECT * FROM users");
        $result = $stmt->executeQuery();
        $this->users = $result->fetchAllAssociative();
        echo json_encode(['users' => array_values($this->users)]);
    }

 public function registerUser()
{
    $this->httpBody = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $this->message(400, 'Invalid JSON format');
        return;
    }

    if (!isset($this->httpBody['name'], $this->httpBody['email'], $this->httpBody['password'], $this->httpBody['role'])) {
        $this->message(400, 'Missing required fields in request body');
        return;
    }

    try {
        $hashedPassword = password_hash($this->httpBody['password'], PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare('
            INSERT INTO users (name, email, password, approved, role, organizations_id)
            VALUES (:name, :email, :password, 0, :role, :organizations_id)
        ');

        $stmt->bindValue(':name', $this->httpBody['name']);
        $stmt->bindValue(':email', $this->httpBody['email']);
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindValue(':role', $this->httpBody['role']);
        $stmt->bindValue(':organizations_id', $this->httpBody['organizations_id'] ?? null);

        $stmt->executeQuery();

        // Haal de user ID op
        $stmt = $this->conn->prepare('SELECT LAST_INSERT_ID()');
        $result = $stmt->executeQuery();
        $id = $result->fetchOne();

        $roleArray = [$this->httpBody['role']];

        // Genereer refresh token
        $refreshToken = JWTService::generateJWTToken($id, $this->httpBody['email'], $this->httpBody['name'], $roleArray, $_ENV['SECRET_KEY'], 7200);
        setcookie('refreshToken', $refreshToken, time() + 7200, "", "", false, true);

        // Opslaan in refresh_tokens
        $stmt = $this->conn->prepare('INSERT INTO refresh_tokens (token, user_id) VALUES (:token, :user_id)');
        $stmt->bindValue(':token', $refreshToken);
        $stmt->bindValue(':user_id', $id);
        $stmt->executeQuery();

        // Access token
        $jwtToken = JWTService::generateJWTToken($id, $this->httpBody['email'], $this->httpBody['name'], $roleArray, $_ENV['SECRET_KEY'], 300);
        echo json_encode(['accessToken' => $jwtToken]);

    } catch (\Exception $e) {
        $this->message(500, 'Error: ' . $e->getMessage());
    }
}


    public function login(): void
{
    $email = $this->httpBody['email'] ?? null;
    $password = $this->httpBody['password'] ?? null;

    if (!$email || !$password) {
        $this->message(400, 'Missing email or password');
        return;
    }

    $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $result = $stmt->executeQuery();
    $user = $result->fetchAssociative();

    // Controleer of de gebruiker bestaat
    if (!$user ) {
        $this->message(401, 'Invalid user');
        return;
    }
    // Controleer of het wachtwoord correct is
     if (!password_verify($password, $user['password'])) {
        $this->message(401, 'Invalid paswoord');
        return;
    }

    $roleArray = [$user['role']];

    // Tokens genereren
    $refreshToken = JWTService::generateJWTToken($user['id'], $user['email'], $user['name'], $roleArray, $_ENV['SECRET_KEY'], 7200 , $user['organizations_id']);
    setcookie('refreshToken', $refreshToken, time() + 7200, "", "", false, true);

    $stmt = $this->conn->prepare('INSERT INTO refresh_tokens (token, user_id) VALUES (:token, :user_id)');
    $stmt->bindValue(':token', $refreshToken);
    $stmt->bindValue(':user_id', $user['id']);
    $stmt->executeQuery();

    $accessToken = JWTService::generateJWTToken($user['id'], $user['email'], $user['name'], $roleArray, $_ENV['SECRET_KEY'], 300 , $user['organizations_id']);
    echo json_encode(['accessToken' => $accessToken]);
}


    public function refreshToken()
{
    $refreshToken = $_COOKIE['refreshToken'] ?? null;

    if (!$refreshToken) {
        $this->message(401, 'No refresh token provided');
        return;
    }

    try {
        $decoded = JWTService::validateJWTToken($refreshToken, $_ENV['SECRET_KEY']);
        $userId = $decoded->sub;

        // Verwijder oude refresh token
        $stmt = $this->conn->prepare('DELETE FROM refresh_tokens WHERE token = :token AND user_id = :id');
        $stmt->bindValue(':token', $refreshToken);
        $stmt->bindValue(':id', $userId);
        $stmt->executeStatement();

        // Gebruiker ophalen
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $userId);
        $result = $stmt->executeQuery();
        $user = $result->fetchAssociative();

        if (!$user) {
            $this->message(404, "User not found");
            return;
        }

        $roleArray = [$user['role']];

        // Nieuwe refresh token
        $newRefreshToken = JWTService::generateJWTToken($userId, $user['email'], $user['name'], $roleArray, $_ENV['SECRET_KEY'], 7200 , $user['organizations_id']);
        setcookie('refreshToken', $newRefreshToken, time() + 7200, "", "", false, true);

        // Opslaan in DB
        $stmt = $this->conn->prepare('INSERT INTO refresh_tokens (token, user_id) VALUES (:token, :user_id)');
        $stmt->bindValue(':token', $newRefreshToken);
        $stmt->bindValue(':user_id', $userId);
        $stmt->executeQuery();

        // Access token
        $accessToken = JWTService::generateJWTToken($userId, $user['email'], $user['name'], $roleArray, $_ENV['SECRET_KEY'], 300 , $user['organizations_id']);
        echo json_encode(['accessToken' => $accessToken]);

    } catch (\Exception $e) {
        $this->message(401, 'Invalid refresh token: ' . $e->getMessage());
    }
}



    // public function registerUser()
    // {
    //     $this->httpBody = json_decode(file_get_contents('php://input'), true);

    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         $this->message(400, 'Invalid JSON format');
    //         return;
    //     }

    //     if (!isset($this->httpBody['name']) 
    //         || !isset($this->httpBody['email']) || !isset($this->httpBody['role'])) {
    //         $this->message(400, 'Missing required fields in request body');
    //         return;
    //     }

    //     try {
    //         if (isset($this->httpBody['password'])) {
    //             $hashedPassword = password_hash($this->httpBody['password'], PASSWORD_DEFAULT);
    //         } else {
    //             $hashedPassword = null;
    //         }
           
    //         $stmt = $this->conn->prepare('INSERT INTO users (name, email, password, approved, role) VALUES (:name, :email , :password, 0, :role)');
    //         $stmt->bindValue(':name', $this->httpBody['name']);
    //         $stmt->bindValue(':email', $this->httpBody['email']);
    //         $stmt->bindValue(':password', $hashedPassword);
    //         $stmt->bindValue(':role', $this->httpBody['role']);

    //         $stmt->executeQuery();
    //         try {
    //             $text = "Beste,\n\nBedankt voor het registreren van uw account. uw kan uw account hier bevestigen:\n\nMet vriendelijke groeten,\nTeam Downtown Cab co.";
    //             Mailer::sendMail($this->httpBody['email'], "Bevestig uw Account.", "", "<p>$text</p><a href='https://downtown-cap-co.gunnarvispoel.ikdoeict.be/verify-account/'> Bevestig mijn account</a>");
    //         } catch (\Exception $e) {
    //             $this->message(500, 'Error sending mail: ' . $e->getMessage());
    //         }

    //         $stmt = $this->conn->prepare('SELECT * FROM users WHERE id = LAST_INSERT_ID()');
    //         $result = $stmt->executeQuery();
    //         $id = $result->fetchOne();
    //         $roleArray[] = $this->httpBody['role'];
    //         try {
    //             $refreshToken = JWTService::generateJWTToken($id, $this->httpBody['email'], $this->httpBody['name'], $roleArray, $_ENV['SECRET_KEY'], 7200);
    //             setcookie('refreshToken', $refreshToken, time() + 720, "", "", false, true);

    //             $stmt = $this->conn->prepare('INSERT INTO refresh_tokens (token, user_id) VALUES (:token, :user_id)');
    //             $stmt->bindValue(':token', $refreshToken);
    //             $stmt->bindValue(':user_id', $id);
    //             $stmt->executeQuery();

    //             $jwtToken = JWTService::generateJWTToken($id, $this->httpBody['email'], $this->httpBody['name'], $roleArray, $_ENV['SECRET_KEY'], 300);
    //             echo json_encode(['accessToken' => $jwtToken]);
    //         } catch (\Exception $e) {
    //             $this->message(500, 'Error creating access token: ' . $e->getMessage());
    //         }
    //     } catch (\Exception $e) {
    //         $this->message(500, 'Database error: ' . $e->getMessage());
    //     }
    // }

    // ode code
    // public function login(): void
    // {
    //     $bodyParams = $this->httpBody;
    //     $email = $bodyParams['email'] ?? false;
    //     $password = $bodyParams['password'] ?? false;

    //     if (($email !== false) && ($password !== false)) {
    //         $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
    //         $stmt->bindValue(':email', $email);
    //         $result = $stmt->executeQuery();
    //         $user = $result->fetchAssociative(); // Haalt één record op

    //         $roleArray[] = $user['role'];

    //         // $stmt = $this->conn->prepare("SELECT role FROM users WHERE email = :email");
    //         $stmt->bindValue(':email', $email);
    //         $result = $stmt->executeQuery();
    //         while ($row = $result->fetchAssociative()) {
    //             $roleArray[] = $row['role']; // Voeg de rol toe aan de array
    //         }

    //         if ($user !== false) {
    //             if (password_verify($password, $user['password'])) {
    //                 try {
    //                     $refreshToken = JWTService::generateJWTToken($user['id'], $user['email'], $user['name'], $user['surname'], $roleArray, $_ENV['SECRET_KEY'], 7200);
    //                     setcookie('refreshToken', $refreshToken, time() + 720, "", "", false, true);

    //                     // add refresh token to the database
    //                     $stmt = $this->conn->prepare('INSERT INTO refresh_tokens (token, user_id) VALUES (:token, :user_id)');
    //                     $stmt->bindValue(':token', $refreshToken);
    //                     $stmt->bindValue(':user_id', $user['id']);
    //                     $stmt->executeQuery();

    //                     // generate access token and send in HTTP response body
    //                     $jwtToken = JWTService::generateJWTToken($user['id'], $user['email'], $user['name'], $roleArray, $_ENV['SECRET_KEY'], 300);
    //                     echo json_encode(['accessToken' => $jwtToken]);
    //                 } catch (\Exception $e) {
    //                     $this->message(500, "Database error : " . $e->getMessage());
    //                 }

    //             } else {
    //                 $this->message(401, "Invalid credentials");
    //             }
    //         } else {
    //             $this->message(401, 'Invalid credentials');
    //         }
    //     } else {
    //         $this->message(400, 'Bad Request, Maybe you are missing some params?');
    //     }
    // }
    // oude code
    // public function refreshToken()
    // {
    //     $refreshToken = $_COOKIE['refreshToken'] ?? false;
    //     if ($refreshToken) {
    //         try {
    //             $decodedPayload = JWTService::validateJWTToken($refreshToken, $_ENV['SECRET_KEY']);
    //             $userId = $decodedPayload->sub;
    //             $stmt = $this->conn->prepare('DELETE FROM refresh_tokens WHERE token = :token AND user_id = :id');
    //             $stmt->bindValue(':token', $refreshToken);
    //             $stmt->bindValue(':id', $userId);
    //             $count = $stmt->executeStatement();
    //             if ($count > 0) {

    //                 $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
    //                 $stmt->bindValue(':id', $userId);
    //                 $result = $stmt->executeQuery();
    //                 $user = $result->fetchAssociative(); // Haalt één record op
                    
    //                 $roleArray[] = $user['role'];

    //                 // $stmt = $this->conn->prepare("SELECT role FROM users WHERE id = :id");
    //                 $stmt->bindValue(':id', $userId);
    //                 $result = $stmt->executeQuery();
    //                 while ($row = $result->fetchAssociative()) {
    //                     $roleArray[] = $row['role']; // Voeg de rol toe aan de array
    //                 }

    //                 if ($user !== false) {
    //                     try {
    //                         $refreshToken = JWTService::generateJWTToken($user['id'], $user['email'], $user['name'], $roleArray, $_ENV['SECRET_KEY'], 7200);
    //                         setcookie('refreshToken', $refreshToken, time() + 720, "", "", false, true);

    //                         // add refresh token to the database
    //                         $stmt = $this->conn->prepare('INSERT INTO refresh_tokens (token, user_id) VALUES (:token, :user_id)');
    //                         $stmt->bindValue(':token', $refreshToken);
    //                         $stmt->bindValue(':user_id', $user['id']);
    //                         $stmt->executeQuery();

    //                         // generate access token and send in HTTP response body
    //                         $jwtToken = JWTService::generateJWTToken($user['id'], $user['email'], $user['name'], $roleArray, $_ENV['SECRET_KEY'], 300);
    //                         echo json_encode(['accessToken' => $jwtToken]);
    //                     } catch (\Exception $e) {
    //                         $this->message(500, "Database error: " . $e->getMessage());
    //                     }
    //                 } else {
    //                     $this->message(404, "User not found.");
    //                 }
    //             } else {
    //                 $this->message(404, 'Token not found.');
    //             }
    //         } catch
    //         (\Exception $e) {
    //             $this->message(500, 'Error refreshing token: ' . $e->getMessage());
    //         }
    //     }
    // }

    // public function getAllBookings()
    // {
    //     $headers = apache_request_headers();
    //     if (isset($headers['Authorization'])) {
    //         $jwtToken = str_ireplace('Bearer ', '', $headers['Authorization']);
    //     }
    //     try {
    //         $decodedPayload = JWTService::validateJWTToken($jwtToken, $_ENV['SECRET_KEY']);
    //         $id = $decodedPayload->sub;
    //         $roles = $decodedPayload->roles;
    //         if (in_array('user', $roles)) {
    //             $stmt = $this->conn->prepare("SELECT d.* FROM drives d INNER JOIN users2 u ON d.user_id = u.id WHERE u.id = :id;");
    //             $stmt->bindValue(':id', $id);
    //             $result = $stmt->executeQuery();
    //             $this->bookings = $result->fetchAllAssociative();
    //             if ($this->bookings) {
    //                 echo json_encode(['bookings' => array_values($this->bookings)]);
    //             } else {
    //                 $this->message(404, 'no bookings found');
    //             }
    //             return;
    //         } else {
    //             $this->message(401, "Invalid Credentials.");
    //         }
    //     } catch
    //     (\Exception $e) {
    //         $this->message(401, "error validating token: " . $e->getMessage());
    //     }
    // }


    // public
    // function getBooking($booking_id)
    // {
    //     $refreshToken = $_COOKIE['refreshToken'] ?? false;
    //     if ($refreshToken) {
    //         $decodedPayload = JWTService::validateJWTToken($refreshToken, "Azerty123");
    //         $id = $decodedPayload->sub;
    //         $stmt = $this->conn->prepare("SELECT d.* FROM drives d INNER JOIN users2 u ON d.user_id = u.id WHERE u.id = :id;");
    //         $stmt->bindValue(':id', $id);
    //         $result = $stmt->executeQuery();
    //         $this->bookings = $result->fetchAllAssociative();
    //         if ($this->bookings) {
    //             echo json_encode(['bookings' => array_values($this->bookings)]);
    //         } else {
    //             $this->message(404, 'no bookings found');
    //         }
    //     } else {
    //         $this->message(401, "Invalid Credentials.");
    //     }


    // }
}