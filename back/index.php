<?php

require_once 'Router.php';
require_once 'AuthController.php';
require_once 'CommentController.php';
require_once 'RecipeController.php';
require_once 'RoleController.php';

session_start(); // Start the session

$router = new Router();


$authController = new AuthController(__DIR__ . '/data/users.json');
$commentController = new CommentController(__DIR__ . '/data/comments.json', $authController);

//------------------------------------------------------------------


$roleController = new RoleController(__DIR__ . '/data/roles.json', $authController);
$recipeController = new RecipeController(__DIR__ . '/data/recipesTest.json');

//------------------------------------------------------------------
//Auth

$router->register('POST', '/register', [$authController, 'handleRegister']);
$router->register('POST', '/login', [$authController, 'handleLogin']);
$router->register('POST', '/logout', [$authController, 'handleLogout']);



//Comments

$router->register('POST', '/comment', [$commentController, 'handlePostCommentRequest']);
$router->register('GET', '/comment', [$commentController, 'handleGetCommentsRequest']);
$router->register('DELETE', '/comment', [$commentController, 'handleDeleteCommentRequest']);


//------------------------------------------------------------------

/*

//Roles
*/
$router->register('POST','/askedRoles', [$roleController, 'handleRoleRequest'] );		//Demander un role
$router->register('POST','/roles',  [$roleController, 'handleRoleApproval'] );		//Accepter la demande d'un role
$router->register('PUT','/role',  [$roleController, 'handleRoleAssignment']  );		//Attribuer un role




//Recipe


$router->register('POST','/recipe',[$recipeController, 'handleRecipePostProposal']);				//Proposer une recette
$router->register('DELETE','/recipe/{params}',[$recipeController, 'handleRecipeDeletion']);				//Éliminer une recette
$router->register('PUT','/recipe',[$recipeController, 'handleRecipeModification']);			//Modifier une recette

$router->register('POST','/recipe/approval/{recipe_name}',[$recipeController, 'handleRecipeApproval']);				//Approuver une recette
$router->register('GET','/recipe/consult/{recipe_name}',[$recipeController, 'handleRecipeConsulting']);				//Consulter une recette
$router->register('GET','/recipe/search',[$recipeController, 'handleRecipeSearch']);				//Rechercher une recette

$router->register('POST','/recipe/traduction/{params}',[$recipeController, 'handleRecipeTraduction']);				//Traduire une recette
$router->register('POST','/recipe/photo/{params}',[$recipeController, 'handleRecipeFotoPublication']); 			//Publier une photo d'une recette
$router->register('POST','/recipe/like/{recipe_name}',[$recipeController, 'handleRecipeLike']);				//Liker une recette



//------------------------------------------------------------------

/*
//test
$router->register('GET', '/post/{post_id}/comment/{comment_id}', function ($post_id, $comment_id) {
    echo "Post ID: " . $post_id . ", Comment ID: " . $comment_id;
});*/



$router->handleRequest();

?>

