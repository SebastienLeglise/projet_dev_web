<?php

class AuthController
{
	private string $filePath;

	public function __construct(string $filePath)
	{
		$this->filePath = $filePath;
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

		// Store user session
		$_SESSION['user'] = $username;

		http_response_code(200);
		echo json_encode(['message' => 'Login successful']);

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
}
