<?php

class AuthMiddleware{

    public static function authCheck(){
        if (isset($_SESSION['user'])){
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
    }
}