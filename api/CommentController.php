<?php

class CommentController
{
	private string $filePath;
	private AuthController $authController;
	private RecipeController $recipeController;

	public function __construct(string $filePath, AuthController $authController, RecipeController $recipeController)
	{
		$this->filePath = $filePath;
		$this->authController = $authController;
		$this->recipeController = $recipeController;

	}

	// Handles the POST /comment route
	public function handlePostCommentRequest(): void
	{
		// Ensure the correct Content-Type header
		if ($_SERVER['CONTENT_TYPE'] !== 'application/x-www-form-urlencoded') {
			http_response_code(400);
			echo json_encode(['error' => 'Invalid Content-Type header']);
			return;
		}

		// Validate and sanitize form data
		$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $recipeName = filter_input(INPUT_POST, 'recipeName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		
        if (!$username || !$message || !$recipeName) {
			http_response_code(400);
			echo json_encode(['error' => 'Missing required fields. Fields']);
			return;
		}


		//only comment if the recipe exists
		$recipe = $this->recipeController->findRecipeByName($recipeName);
		
        if ($recipe === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Recipe not found']);
            return;
        }

		// Create a new comment
		$newComment = [
			'username' => $username,
            'message' => $message,
            'timestamp' => date('c'),
      	];

		// Save the comment
		$this->saveComment($recipeName, $newComment);

		// Return the updated list of comments
		http_response_code(200);
		header('Content-Type: application/json');
		echo json_encode($this->getAllComments());
	}

	// Saves a new comment to the file
	private function saveComment(string $recipeName,array $comment): void
	{
		$comments = $this->getAllComments();
		
		if (!isset($comments[$recipeName])) {
            $comments[$recipeName] = [];
        }
		$comments[$recipeName][] = $comment;


		file_put_contents($this->filePath, json_encode($comments, JSON_PRETTY_PRINT));
	}

	// Retrieves all comments from the file
	private function getAllComments(): array
	{
		if (!file_exists($this->filePath)) {
			return [];
		}

		$content = file_get_contents($this->filePath);
		if ($content === false) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to read comments file']);
			return [];
		}
		return json_decode($content, true) ?? [];
	}

	public function handleGetCommentsRequest(): void
	{
		$recipeName = filter_input(INPUT_GET, 'recipeName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


		if (!$recipeName) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing recipe name']);
            return;
        }

		$comments = $this->getCommentsByRecipe($recipeName);
		if ($comments === null) {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to retrieve comments']);
			return;
		}

		http_response_code(200);
		header('Content-Type: application/json');
		echo json_encode($comments);
	}

	public function handleDeleteCommentRequest(): void
	{

		//TODO
		/*$auth = $this->authController->isAuthenticated();
		
		if (!$auth) {
			http_response_code(401);
			echo json_encode(['error' => 'Unauthorized']);
			return;
		}*/

		file_put_contents($this->filePath, json_encode([]));

		http_response_code(200);
		echo json_encode(['message' => 'All comments deleted']);
	}

	private function getCommentsByRecipe(string $recipeName): array
	{
		$comments = $this->getAllComments();

		// Log the loaded comments
		error_log("Loaded comments: " . json_encode($comments));

		if (!is_array($comments)) {
			error_log("Comments data is not an array.");
			return [];
		}

		return $comments[$recipeName] ?? [];
	}
	
}



