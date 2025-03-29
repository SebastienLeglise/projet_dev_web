<?php

class RecipeController{

    private string $filePath;

	public function __construct(string $filePath)
	{
		$this->filePath = $filePath;
	}




    public function handleRecipePostProposal() {
    
        echo "Recipe proposal received.";
    }

    public function handleRecipeDeletion($params) {
        //TODO
        $recipe_id = (int) $params[0]; // Extraire l'ID depuis le tableau
        echo "Recipe with ID $recipe_id deleted.";
    }

    
    public function handleRecipeModification() {
        //TODO
        echo "Recipe modification processed.";
    }



    public function handleRecipeApproval() {
        //TODO
        echo "Recipe approved.";
    }
    public function handleRecipeConsulting($params) {
        //TODO
        $recipe_id = (int) $params[0]; // Extraire l'ID depuis le tableau
        echo "Consulting recipe with ID $recipe_id.";
    }
    public function handleRecipeSearch($params) {
        //TODO
        $recipe_id = (int) $params[0]; // Extraire l'ID depuis le tableau
        echo "Searching for recipe with ID $recipe_id.";
    }
    public function handleRecipeTraduction() {
        //TODO
        echo "Recipe translation processed.";
    }
    public function handleRecipeFotoPublication() {
        //TODO
        echo "Recipe photo published.";
    }
    public function handleRecipeLike() {
        //TODO
        echo "Recipe liked.";
    }





}

?>