<?php

class RolepeController{

    private string $filePath;

	public function __construct(string $filePath)
	{
		$this->filePath = $filePath;
	}
    public function getAllRoleRequests(): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->getAllRequests());
    }

    private function getAllRequests(): array
    {
        return file_exists($this->filePath) ? json_decode(file_get_contents($this->filePath), true) ?? [] : [];
    }
    private function getAllUsers(): array
    {
        $usersFile = 'users.json'; 
        return file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) ?? [] : [];
    }


    public function handleRoleRequest(): void{
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }
        $json = file_get_contents('php://input');
        $data = json_decode($json);

        if (!($data->username) || !($data->requestedRole)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid username or requested role']);
            return;
        }

        $username = $data->username;
        $requestedRole = $data->requestedRole;
        
        $requests = $this->getAllRequests();
        $requests[] = [
            'username' => $username,
            'requestedRole' => $requestedRole,
            'status' => 'pending'
        ];

        file_put_contents($this->filePath, json_encode($requests, JSON_PRETTY_PRINT));
        http_response_code(201);
        echo json_encode(['message' => 'Role request submitted']);

    }


    public function handleRoleApproval(): void 
    {
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json);
        if (!($data->username) || !($data->status)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid username or status']);
            return;
        }

        $username = $data->username;
        $status = $data->status;
        $requests = $this->getAllRequests();

        foreach ($requests as &$request) {
            if ($request['username'] === $username) {
                $request['status'] = $status;
                file_put_contents($this->filePath, json_encode($requests, JSON_PRETTY_PRINT));
                http_response_code(200);
                echo json_encode(['message' => 'Role request ' . $status]);
                return;
            }
            
        }
        http_response_code(404);
        echo json_encode(['error' => 'Role request not found']);
    }
    public function handleRoleAssignment(): void
    {
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json);

        if (!($data->username) || !($data->role)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid username or role']);
            return;
        }

        $username = $data->username;
        $role = $data->role;
        $users = $this->getAllUsers();

        if (!isset($users[$username])) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }

        $users[$username]['role'] = $role;
        file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT));
        http_response_code(200);
        echo json_encode(['message' => 'Role assigned to user']);
    }
    
        

}

?>