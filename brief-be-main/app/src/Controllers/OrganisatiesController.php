<?php

namespace Controllers;

use Services\JWTService;
use Services\Mailer;
use Doctrine\DBAL\ParameterType;



class OrganisatiesController extends ApiBaseController {
    private $organizations;

  public function getAllorganizations()
{
    $userId = JWTService::getUserIdFromToken();
    $roles = JWTService::getUserRolesFromToken();
    $organizations_id = JWTService::getOrganizationIdFromToken();

    if (in_array('beheerder', $roles)) {
        $stmt = $this->conn->prepare("SELECT * FROM organizations");
        $result = $stmt->executeQuery();
    } else {
        $stmt = $this->conn->prepare("
            SELECT o.* 
            FROM organizations o 
            JOIN users u ON u.organizations_id = o.id 
            WHERE u.id = :id
        ");
        $stmt->bindValue('id', $userId, \Doctrine\DBAL\ParameterType::INTEGER);
        $result = $stmt->executeQuery();
    }

    $organizations = $result->fetchAllAssociative();
    echo json_encode(['organizations' => $organizations]);
}




//     // testen morgen
//     public function getAllorganizations()
// {
//     $userId = JWTService::getUserIdFromToken();
//     $userRoles = JWTService::getUserRolesFromToken(); // Je moet deze helper eventueel toevoegen

//     // beheerder? Geef alles terug
//     if (in_array('beheerder', $userRoles)) {
//         $stmt = $this->conn->prepare("SELECT * FROM organizations");
//         $result = $stmt->executeQuery();
//     } else {
//         // Geen beheerder? Alleen eigen organisatie
// $stmt = $this->conn->prepare("
//     SELECT o.* 
//     FROM organizations o 
//     JOIN users u ON u.organization_id = o.id 
//     WHERE u.id = ?
// ");
// $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
// $result = $stmt->executeQuery();
//     }

//     $organizations = $result->fetchAllAssociative();
//     echo json_encode(['organizations' => $organizations]);
// }

    //  // GET /api/organizations
    // public function getAllorganizations() {
    //     $stmt = $this->conn->prepare("SELECT * FROM organizations");
    //     $result = $stmt->executeQuery();
    //     $this->organizations = $result->fetchAllAssociative();

    //     echo json_encode(['organizations' => array_values($this->organizations)]);
    // }        


    // public function getOrganizationById($id) {
    //     $stmt = $this->conn->prepare("SELECT * FROM organizations WHERE id = :id");
    //     $stmt->bindValue(':id', $id);
    //     $result = $stmt->executeQuery();
    //     $organization = $result->fetchAssociative();

    //     if ($organization) {
    //         echo json_encode(['organization' => $organization]);
    //     } else {
    //         http_response_code(404);
    //         echo json_encode(['error' => 'Organization not found']);
    //     }
    // }   


      // GET /api/organizationss/{id}
   public function getOrganizationChannels($id) {
    $stmt = $this->conn->prepare("SELECT * FROM channels WHERE organizations_id = :id");
    $stmt->bindValue(':id', $id);
    $result = $stmt->executeQuery();
    $channels = $result->fetchAllAssociative();

    if ($channels) {
        echo json_encode(['channels' => $channels]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No channels found for this organization']);
    }
}



     // GET /api/organizationss/{id}/channels
   public function createOrganization() {
    $this->httpBody = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON format']);
        return;
    }

    // Vereiste velden controleren
    if (
        empty($this->httpBody['name']) ||
        empty($this->httpBody['postcode']) ||
        empty($this->httpBody['users_id']) ||
        empty($this->httpBody['users_rol'])
    ) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Missing required fields: name, postcode, users_id, users_rol'
        ]);
        return;
    }

    try {
        $stmt = $this->conn->prepare("
            INSERT INTO organizations (name, postcode, users_id, users_rol)
            VALUES (:name, :postcode, :users_id, :users_rol)
        ");
        $stmt->bindValue(':name', $this->httpBody['name']);
        $stmt->bindValue(':postcode', $this->httpBody['postcode']);
        $stmt->bindValue(':users_id', $this->httpBody['users_id']);
        $stmt->bindValue(':users_rol', $this->httpBody['users_rol']);
        $stmt->executeQuery();

        $newId = $this->conn->lastInsertId();

        http_response_code(201);
        echo json_encode([
            'message' => 'Organization created successfully',
            'organization_id' => $newId
        ]);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

}