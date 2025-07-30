<?php

namespace Controllers;

use Services\DatabaseConnector;

abstract class ApiBaseController
{
    protected $conn;
    protected ?array $httpBody;

    public function __construct()
    {
        // Parse the HTTP request body assuming it contains plain JSON
        $this->httpBody = json_decode(file_get_contents('php://input'), true);

        // set the Content-type header of the HTTP response to JSON
        header('Content-type: application/json; charset=UTF-8');
        // CORS: API response can be shared with javascript code from origin ALLOW_ORIGIN
        header('Access-Control-Allow-Origin: ' . $_ENV['ALLOW_ORIGIN']);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        $dotenv = \Dotenv\Dotenv::createImmutable('..');
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

        $this->conn = DatabaseConnector::getConnection();
    }

    protected function message(int $httpCode, string $message)
    {
        http_response_code($httpCode);
        $answer = ['message' => $message];
        echo json_encode($answer);
    }
}