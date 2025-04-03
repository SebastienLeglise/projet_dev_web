<?php

require_once 'Router.php';
require_once 'AuthController.php';
require_once 'CommentController.php';
require_once 'RecipeController.php';
require_once 'RoleController.php';
require_once 'AuthMiddleware.php';

session_start(); // Start the session



$router = new Router();


$authController = new AuthController(__DIR__ . '/data/users.json', __DIR__ . '/data/roles.json');
$commentController = new CommentController(__DIR__ . '/data/comments.json', $authController);
$roleController = new RoleController(__DIR__ . '/data/roles.json', $authController);
$recipeController = new RecipeController(__DIR__ . '/data/recipeTest.json');


//------------------------------------------------------------------

//Auth

$router->register('POST', '/api/register', [$authController, 'handleRegister'],false);
$router->register('POST', '/api/login', [$authController, 'handleLogin'],false);
$router->register('POST', '/api/logout', [$authController, 'handleLogout'],true);
$router->register('GET', '/api/check-session', [$authController, 'handleCheckSession'], false);

//Comments

$router->register('POST', '/api/comment', [$commentController, 'handlePostCommentRequest'],false);
$router->register('GET', '/api/comment', [$commentController, 'handleGetCommentsRequest'],false);
$router->register('DELETE', '/api/comment', [$commentController, 'handleDeleteCommentRequest'],false);


//------------------------------------------------------------------

/*

//Roles
*/
$router->register('POST','/api/askedRoles', [$roleController, 'handleRoleRequest'],false );		//Demander un role
$router->register('POST','/api/roles',  [$roleController, 'handleRoleApproval'],false );		//Accepter la demande d'un role
$router->register('PUT','/api/role',  [$roleController, 'handleRoleAssignment'],false  );		//Attribuer un role




//Recipe


$router->register('POST','/api/recipe',[$recipeController, 'handleRecipePostProposal'],false);				//Proposer une recette
$router->register('DELETE','/api/recipe/{params}',[$recipeController, 'handleRecipeDeletion'],false);				//Éliminer une recette
$router->register('PUT','/api/recipe',[$recipeController, 'handleRecipeModification'],false);			//Modifier une recette

$router->register('POST','/api/recipe/approval',[$recipeController, 'handleRecipeApproval'],false);				//Approuver une recette
$router->register('GET','/api/recipe/consult/{recipe_name}',[$recipeController, 'handleRecipeConsulting'],false);				//Consulter une recette
$router->register('GET','/api/recipe/consultAll',[$recipeController, 'handleRecipeConsultingAll'],false);				//Consulter toutes les recettes
$router->register('GET','/api/recipe/search',[$recipeController, 'handleRecipeSearch'],false);				//Rechercher une recette

$router->register('POST','/api/recipe/traduction/{params}',[$recipeController, 'handleRecipeTraduction'],false);				//Traduire une recette
$router->register('POST','/api/recipe/photo/{params}',[$recipeController, 'handleRecipeFotoPublication'],false); 			//Publier une photo d'une recette
$router->register('POST','/api/recipe/like/{recipe_name}',[$recipeController, 'handleRecipeLike'],false);				//Liker une recette



//------------------------------------------------------------------

/*
//test
$router->register('GET', '/post/{post_id}/comment/{comment_id}', function ($post_id, $comment_id) {
    echo "Post ID: " . $post_id . ", Comment ID: " . $comment_id;
});*/



$router->handleRequest();

?>
