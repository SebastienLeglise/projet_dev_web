<?php

require_once 'Router.php';
//require_once 'AuthController.php';
//require_once 'CommentController.php';

//TODo : require_once etc

session_start(); // Start the session

$router = new Router();

/*
$authController = new AuthController(__DIR__ . '/data/users.json');
$commentController = new CommentController(__DIR__ . '/data/comments.json', $authController);

//------------------------------------------------------------------


$roleController = new RoleController(__DIR__ . '/data/roles.json', //TODO :arguments);
$recipeController = new CommentController(__DIR__ . '/data/recipes.json', //TODO);

//------------------------------------------------------------------






//Auth

$router->register('POST', '/register', [$authController, 'handleRegister']);
$router->register('POST', '/login', [$authController, 'handleLogin']);
$router->register('POST', '/logout', [$authController, 'handleLogout']);



//Comments

$router->register('POST', '/comment', [$commentController, 'handlePostCommentRequest']);
$router->register('GET', '/comment', [$commentController, 'handleGetCommentsRequest']);
$router->register('DELETE', '/comment', [$commentController, 'handleDeleteCommentRequest']);


*/

//------------------------------------------------------------------

/*

//Roles

$router->register('POST','/askedRoles', [$roleController, ''] );		//Demander un role
$router->register('POST','/roles',  [$roleController, ''] );		//Accepter la demande d'un role
$router->register('PUT','/role',  [$roleController, '']  );		//Attribuer un role




//Recipe


$router->register('POST','/recipe',[$recipeController, '']);				//Proposer une recette
$router->register('DELETE','/recipe/{recipe_id}',[$recipeController, '']);				//Éliminer une recette
$router->register('PUT','/recipe',[$recipeController, '']);			//Modifier une recette
$router->register('POST','/recipe',[$recipeController, '']);				//Approuver une recette
$router->register('GET','/recipe/{recipe_id}',[$recipeController, '']);				//Consulter une recette
$router->register('GET','/recipe/{recipe_id}',[$recipeController, '']);				//Rechercher une recette
$router->register('POST','/recipe',[$recipeController, '']);				//Traduire une recette
$router->register('POST','/recipe',[$recipeController, '']); 			//Publier une photo d'une recette
$router->register('POST','/recipe',[$recipeController, '']);				//Liker une recette




//TODO : handlers

*/

//------------------------------------------------------------------


//test
$router->register('GET', '/post/{post_id}/comment/{comment_id}', function ($post_id, $comment_id) {
    echo "Post ID: " . $post_id . ", Comment ID: " . $comment_id;
});



$router->handleRequest();

?>

