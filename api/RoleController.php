<?php

class RoleController{

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
        $status = ($requestedRole === 'none') ? 'denied' : 'pending';
        $requests = $this->getAllRequests();
        $requests[] = [
            'username' => $username,
            'requestedRole' => $requestedRole,
            'status' => $status,
            'role' => 'cuisinier'
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
                $request['role']= $request['requestedRole'];
                file_put_contents($this->filePath, json_encode($requests, JSON_PRETTY_PRINT));
                http_response_code(200);
                echo json_encode(['message' => 'Role request ' . $status]);
                return;
            }
            
        }
        http_response_code(404);
        echo json_encode(['error' => 'Role request not found']);
    }


    public function handleRoleDeny(): void 
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
                $request['role']= 'cuisinier';
                file_put_contents($this->filePath, json_encode($requests, JSON_PRETTY_PRINT));
                http_response_code(200);
                echo json_encode(['message' => 'Role request ' . $status]);
                return;
            }
            
        }
        http_response_code(404);
        echo json_encode(['error' => 'Role request not found']);
    }



    public function handleRoleConsultingAll() {
        $roles = $this->getAllRequests();
    
        if ($roles == null || empty($roles)) {
            http_response_code(404);
            echo json_encode(["error" => "The roles database is empty, you can help by expanding it."]);
            return;
        }
    
        // Filter roles with status "pending"
        $pendingRoles = array_filter($roles, function($role) {
            return isset($role['status']) && $role['status'] === 'pending';
        });
    
        // Optional: Re-index array (so it returns a clean [0,1,2,...] array in JSON)
        $pendingRoles = array_values($pendingRoles);
    
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($pendingRoles);
    }
    
    
        

}

?>
