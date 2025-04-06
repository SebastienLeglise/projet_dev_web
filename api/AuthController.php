<?php
session_start();
class AuthController
{
	private string $filePath, $roleFilePath;

	public function __construct(string $filePath, string $roleFilePath)
	{
		$this->filePath = $filePath;
        $this->roleFilePath = $roleFilePath;
	}


    
    public function handleRegister(): void {  
    if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid Content-Type']);
        return;
    }

    // Get raw data from the request
    $json = file_get_contents('php://input');

    // Convert JSON data to a PHP object
    $data = json_decode($json);

    if (!($data->username) || !($data->password)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Invalid username or password',
            'username' => $data->username ?? null,
            'password' => $data->password ?? null
        ]);
        return;
    }

    $username = $data->username;
    $password = $data->password;

    $users = $this->getAllUsers();

    if (isset($users[$username])) {
        http_response_code(409);
        echo json_encode(['error' => 'Username already registered']);
        return;
    }

    // Hash the password and add the new user to the list
    $users[$username] = ['password' => password_hash($password, PASSWORD_DEFAULT)];

    // Save the updated users to the file
    file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT));


    //default role

    $role= [
        'username' => $username,
        'role'=> "cuisinier"
    ];

    $this->saveRole($role);


    // Send a success response
    http_response_code(201);
    echo json_encode(['message' => 'User registered successfully']);
}


	// TODO: Implement the handleLogin method
	public function handleLogin(): void
	{
		if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }
    
        // Get raw data from the request
        $json = file_get_contents('php://input');
    
        // Convert JSON data to a PHP object
        $data = json_decode($json);
    
        if (!($data->username) || !($data->password)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Invalid username or password',
                'username' => $data->username ?? null,
                'password' => $data->password ?? null
            ]);
            return;
        }

        $username = $data->username;
        $password = $data->password;

		$users = $this->getAllUsers();

		if (!isset($users[$username]) || !password_verify($password, $users[$username]['password'])) {
			http_response_code(401);
			echo json_encode(['error' => 'Invalid credentials']);
			return;
		}
        $roles = json_decode(file_get_contents(__DIR__ . '/data/roles.json'), true);

        $userRole = null;
        foreach ($roles as $entry) {
            if ($entry['username'] === $username) {
                $userRole = $entry['role'];
                break;
            }
        }

        if (!$userRole) {
            http_response_code(403);
            echo json_encode(['error' => 'No role assigned to this user']);
            return;
        }

		// Store user session
		$_SESSION['user'] = $username;
        $_SESSION['role'] = $userRole;
        
        
		http_response_code(200);
		echo json_encode(['message' => 'Login successful']);

	}

    public function handleCheckSession(): void
{
    if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid Content-Type']);
        return;
    }
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Initialize response
    $response = ['isLoggedIn' => false, 'username' => '','role' => ''];

    // Check if the session contains a logged-in user
    if (isset($_SESSION['user'])) {
        $response['isLoggedIn'] = true;
        $response['username'] = $_SESSION['user'];
        $response['role'] = $_SESSION['role'];
    }

    // Send JSON response
    echo json_encode($response);
}



	public function handleLogout(): void
	{
		session_destroy(); // Clear session
		http_response_code(200);
		echo json_encode(['message' => 'Logged out successfully']);
	}

	public function validateAuth(): ?string
	{
		return $_SESSION['user'] ?? null;
	}

	public function getAllUsers(): array
	{
		return file_exists($this->filePath) ? json_decode(file_get_contents($this->filePath), true) ?? [] : [];
	}

    private function saveRole(array $role): void
	{
		$roles = $this->getAllRoles();
		$roles[] = $role;

		file_put_contents($this->roleFilePath, json_encode($roles, JSON_PRETTY_PRINT));
	}
    
    
    private function getAllRoles(): array
    {
		return file_exists($this->roleFilePath) ? json_decode(file_get_contents($this->roleFilePath), true) ?? [] : [];

    }


}
