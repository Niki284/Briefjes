<?php

namespace Services;

use Firebase\JWT\JWT;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
class JWTService
{
    static function generateJWTToken(int $userId, string $userName, string $name, ?array $rolesArray, string $secretKey, int $seconds = 60 * 60) : string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $seconds;
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $userId,
            'email' => $userName,
            'name' => $name
        ];

        if($rolesArray !== null){
            $payload['roles'] = $rolesArray;
        }



        return JWT::encode($payload, $secretKey, 'HS256');
    }
    /**
     * @throws \Exception
     */
    static function validateJWTToken(string $jwtToken, string $secretKey) : \stdClass
    {
        try {
            return JWT::decode($jwtToken,  new Key($secretKey, 'HS256'));
        } catch (ExpiredException $e) {
            throw new \Exception('Token expired');
        } catch (SignatureInvalidException $e) {
            throw new \Exception('Invalid token signature: ' . $e->getMessage());
        } catch (BeforeValidException $e) {
            throw new \Exception('Token not valid yet');
        } catch (\Exception $e) {
            throw new \Exception('Invalid token= ' . $e->getMessage());
        }
    }

    public static function getUserIdFromToken(): int
    {
        $token = self::getBearerToken();
        $decoded = self::validateJWTToken($token, $_ENV['SECRET_KEY']);
        return (int) $decoded->sub;
    }

    public static function getUserRolesFromToken(): array
    {
        $token = self::getBearerToken();
        $decoded = self::validateJWTToken($token, $_ENV['SECRET_KEY']);
        return $decoded->roles ?? [];
    }

    private static function getBearerToken(): string
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            throw new \Exception('Authorization header missing');
        }

        if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            throw new \Exception('Invalid Authorization header format');
        }

        return $matches[1];
    }
}