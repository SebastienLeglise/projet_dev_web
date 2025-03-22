<?php

class AuthController
{
	private string $filePath;

	public function __construct(string $filePath)
	{
		$this->filePath = $filePath;
	}

	// TODO: Implement the handleRegister method
	public function handleRegister(): void
	{
		// Hints:
		// 1. Check if the request Content-Type is 'application/x-www-form-urlencoded'

        if($_SERVER['CONTENT_TYPE'] !== 'application/x-www-form-urlencoded' ){
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Content-Type header']);
            return;
        }

		// 2. Get the email and password from the POST data

        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


		// 3. Validate the email and password

        if(!$username || !$password ){
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields ']);
            return;
        }

		// 4. Check if the email is already registered

    
        $f = fopen('data/users.json', 'r+');
        if (!flock($f, LOCK_EX))
            http_response_code(409); // conflict
        $jsonString = fread($f, filesize('dara/'.$users.'.json'));
        $data = json_decode($jsonString, true); 

        foreach($data as $name){
            if($user['username'] == $username){
                http_response_code(401);
                echo json_encode(['error' => 'User already registered']);
                flock($f, LOCK_UN);
                fclose($f);
                return;
            }
        }
        

		// 5. Hash the password using password_hash

        $Hpassword = password_hash($password, PASSWORD_DEFAULT);


		// 6. Save the user data to the file


        $data[] =[
            'username' => $username,
            'password' => $Hpassword,
        ];


        flock($f, LOCK_UN);
        fclose($f);


		// 7. Return a success message with HTTP status code 201

        http_response_code(201);

		// If any error occurs, return an error message with the appropriate HTTP status code
		// Make sure to set the Content-Type header to 'application/json' in the response
		// You can use the json_encode function to encode an array as JSON
		// You can use the http_response_code function to set the HTTP status code


        
	}

	// TODO: Implement the handleLogin method
	public function handleLogin(): void
	{
		// Hints:
		// 1. Check if the request Content-Type is 'application/x-www-form-urlencoded'

        if($_SERVER['CONTENT_TYPE'] !== 'application/x-www-form-urlencoded' ){
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Content-Type header']);
            return;
        }

		// 2. Get the email and password from the POST data

        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


		// 3. Validate the email and password

        if(!$username || !$password ){
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields ']);
            return;
        }
		// 4. Check if the user exists and the password is correct

        $f = fopen('data/users.json', 'r+');
        if (!flock($f, LOCK_EX))
            http_response_code(409); // conflict
        $jsonString = fread($f, filesize('dara/'.$users.'.json'));
        $data = json_decode($jsonString, true); 

        $Hpassword = password_hash($password, PASSWORD_DEFAULT);

        $found = FALSE;
        foreach($data as $name){
            if($user['username'] == $username && $user['password'] == $Hpassword){
                $found = TRUE;
            }
        }

        if($found == FALSE){
            http_response_code(400);
            echo json_encode(['error' => 'User not found ']);
            return;

        }
        
		// 5. Store the user session



		// 6. Return a success message with HTTP status code 200
        http_response_code(200);



		// Additional hints:
		// If any error occurs, return an error message with the appropriate HTTP status code
		// Make sure to set the Content-Type header to 'application/json' in the response
		// You can use the getAllUsers method to get the list of registered users
		// You can use the password_verify function to verify the password
		// You can use the $_SESSION superglobal to store the user session
		// You can use the json_encode function to encode an array as JSON
		// You can use the http_response_code function to set the HTTP status code
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

	private function getAllUsers(): array
	{
		return file_exists($this->filePath) ? json_decode(file_get_contents($this->filePath), true) ?? [] : [];
	}
}
