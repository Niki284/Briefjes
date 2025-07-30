<?php

namespace Services;

use Firebase\JWT\JWT;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

class JWTService
{
    public static function generateJWTToken(
        int $userId,
        string $userEmail,
        string $name,
        ?array $rolesArray,
        string $secretKey,
        int $seconds = 3600, // default: 1 uur
        ?int $organizations_id = null //  extra parameter

    ): string {
        $issuedAt = time();
        $expirationTime = $issuedAt + $seconds;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $userId,
            'email' => $userEmail,
            'name' => $name,
            'organizations_id' => $organizations_id // voeg toe aan payload
        ];
        // var_dump($payload);

        if ($rolesArray !== null) {
            $payload['roles'] = $rolesArray;
        }


        return JWT::encode($payload, $secretKey, 'HS256');
    }

    public static function validateJWTToken(string $jwtToken, string $secretKey): \stdClass
    {
        try {
            return JWT::decode($jwtToken, new Key($secretKey, 'HS256'));
        } catch (ExpiredException $e) {
            throw new \Exception('Token expired');
        } catch (SignatureInvalidException $e) {
            throw new \Exception('Invalid token signature: ' . $e->getMessage());
        } catch (BeforeValidException $e) {
            throw new \Exception('Token not valid yet');
        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }

    public static function getOrganizationIdFromToken(): ?int
{
    $token = self::getBearerToken();
    $decoded = self::validateJWTToken($token, $_ENV['SECRET_KEY']);
    return isset($decoded->organizations_id) ? (int)$decoded->organizations_id : null;
    
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
