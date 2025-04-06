<?php
class AuthMiddleware{

    public static function authCheck(){
        if (!isset($_SESSION['username'])){
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
        
        $roles = json_decode(file_get_contents('/data/roles.json'));
        $userRole = null;
        
        foreach ($roles as $entry) {
            if ($entry['username'] === $_SESSION['username']) {
                $userRole = $entry['role'];
                break;
            }
        }
        
        if ($userRole !== 'chef') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied: not a chef']);
            exit;
        }
        
    }
}