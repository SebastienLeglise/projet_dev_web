<?php

require_once 'Router.php';
require_once 'AuthController.php';
require_once 'CommentController.php';
require_once 'RecipeController.php';
require_once 'RoleController.php';

session_start(); // Start the session



$router = new Router();


$authController = new AuthController(__DIR__ . '/data/users.json', __DIR__ . '/data/roles.json');
$roleController = new RoleController(__DIR__ . '/data/roles.json', $authController);
$recipeController = new RecipeController(__DIR__ . '/data/recipes.json');
$commentController = new CommentController(__DIR__ . '/data/comments.json', $authController, $recipeController);


//------------------------------------------------------------------

//Auth

$router->register('POST', '/api/register', [$authController, 'handleRegister']);
$router->register('POST', '/api/login', [$authController, 'handleLogin']);
$router->register('POST', '/api/logout', [$authController, 'handleLogout']);
$router->register('GET', '/api/check-session', [$authController, 'handleCheckSession']);

//Comments

$router->register('POST', '/api/comment', [$commentController, 'handlePostCommentRequest']);
$router->register('GET', '/api/comment', [$commentController, 'handleGetCommentsRequest']);
$router->register('DELETE', '/api/comment', [$commentController, 'handleDeleteCommentRequest']);


//------------------------------------------------------------------

/*

//Roles
*/
$router->register('POST','/api/askedRoles', [$roleController, 'handleRoleRequest'] );		//Demander un role
$router->register('POST','/api/roles',  [$roleController, 'handleRoleApproval'] );		//Accepter la demande d'un role		//Attribuer un role
$router->register('GET','/api/roles',[$roleController, 'handleRoleConsultingAll']);
$router->register('POST','/api/roles/deny',[$roleController, 'handleRoleDeny']);



//Recipe


$router->register('POST','/api/recipe',[$recipeController, 'handleRecipePostProposal']);				//Proposer une recette
$router->register('DELETE','/api/recipe/{params}',[$recipeController, 'handleRecipeDeletion']);				//Éliminer une recette
$router->register('PUT','/api/recipe',[$recipeController, 'handleRecipeModification']);			//Modifier une recette

$router->register('POST','/api/recipe/approval',[$recipeController, 'handleRecipeApproval']);				//Approuver une recette
$router->register('POST','/api/recipe/deny',[$recipeController, 'handleRecipeDeny']);				//Approuver une recette

$router->register('GET','/api/recipe/consult/{recipe_name}',[$recipeController, 'handleRecipeConsulting']);				//Consulter une recette
$router->register('GET','/api/recipe/consultAll',[$recipeController, 'handleRecipeConsultingAll']);				//Consulter toutes les recettes

$router->register('GET','/api/recipe/consultAllDos',[$recipeController, 'handleRecipeConsultingAll2']);				//Consulter toutes les recettes2

$router->register('GET','/api/recipe/search',[$recipeController, 'handleRecipeSearch']);				//Rechercher une recette

$router->register('GET','/api/recipe/traduction/{recipe_name}',[$recipeController, 'handleRecipeTraduction']);				//Traduire une recette
$router->register('POST','/api/recipe/photo/{params}',[$recipeController, 'handleRecipeFotoPublication']); 			//Publier une photo d'une recette
$router->register('POST','/api/recipe/like/{recipe_name}',[$recipeController, 'handleRecipeLike']);				//Liker une recette



//------------------------------------------------------------------

/*
//test
$router->register('GET', '/post/{post_id}/comment/{comment_id}', function ($post_id, $comment_id) {
    echo "Post ID: " . $post_id . ", Comment ID: " . $comment_id;
});*/



$router->handleRequest();

?>
