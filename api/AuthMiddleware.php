<?php
$response = ['isLoggedIn' => false, 'username' => ''];
class AuthMiddleware{

    public static function authCheck(){
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }
        

        if (!isset($_SESSION['user'])){
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
        $response['isLoggedIn'] = true;
        $response['username'] = $_SESSION['user'];

        echo json_encode($response);
    }
}