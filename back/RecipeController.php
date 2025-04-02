<?php

class RecipeController{

    private string $filePath;

	public function __construct(string $filePath)
	{
		$this->filePath = $filePath;
	}




    public function handleRecipePostProposal() {
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json);

        // Validate necessary fields
        if (!($data->name) || !($data->ingredients) || !($data->steps)  ||!($data->timers)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required recipe information']);
            return;
        }

        $recipes = $this->getAllRecipes();

        // Create a new recipe object using the input data
        $newRecipe = [
            "name" => $data->name,
            "nameFR" => $data->nameFR ?? "N/A",
            "Author" => $data->Author ?? "Unknown",
            "Without" => $data->Without ?? [],
            "ingredients" => $data->ingredients,
            "steps" => $data->steps,
            "timers" => $data->timers ,
            "imageURL" => $data->imageURL ?? "",
            "originalURL" => $data->originalURL ?? "",
            "status" => 'proposed'
        ];

        $recipes[] = $newRecipe;

        file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT));

        http_response_code(201);
        echo json_encode(['message' => 'Recipe proposed successfully']);
    }

    public function handleRecipeDeletion($params) {
        //TODO
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json);

        if (!($data->name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing recipe name']);
            return;
        }

        $recipes = $this->getAllRecipes();
        foreach ($recipes as $index => $recipe) {
            if ($recipe['name'] === $data->name) {
                array_splice($recipes, $index, 1);
                file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT));
                http_response_code(200);
                echo json_encode(['message' => 'Recipe deleted successfully']);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Recipe not found']);
    
       // $recipe_id = (int) $params[0]; // Extraire l'ID depuis le tableau
    //echo "Recipe with ID $recipe_id deleted.";
    }
    
        public function handleRecipeModification() {
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }
    
        $json = file_get_contents('php://input');
        $data = json_decode($json);
    
        if (!($data->name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing recipe name']);
            return;
        }
    
        $recipes = $this->getAllRecipes();
        foreach ($recipes as &$recipe) {
            if ($recipe['name'] === $data->name) {
                $recipe['nameFR'] = $data->nameFR ?? $recipe['nameFR'];
                $recipe['Author'] = $data->Author ?? $recipe['Author'];
                $recipe['Without'] = $data->Without ?? $recipe['Without'];
                $recipe['ingredients'] = $data->ingredients ?? $recipe['ingredients'];
                $recipe['steps'] = $data->steps ?? $recipe['steps'];
                $recipe['timers'] = $data->timers ?? $recipe['timers'];
                $recipe['imageURL'] = $data->imageURL ?? $recipe['imageURL'];
                $recipe['originalURL'] = $data->originalURL ?? $recipe['originalURL'];
    
                file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT));
                http_response_code(200);
                echo json_encode(['message' => 'Recipe modified successfully']);
                return;
            }
        }




    //---------------------------------------------------------------





    public function handleRecipeApproval() {
        //TODO
        echo "Recipe approved.";
    }


    public function handleRecipeSearch($params, $splitedQuery) {//querys

        $validSearchFields = ['name',
                              'nameFR', 
                              'author', 
                              'without', 
                              'ingredient',
                              'likes', 
                              //'lang'   une foie traduction sera faite on pourra chercher par langue
        ];

        $validSearchVlaues = [];

        foreach($splitedQuery as $field => $queryValue) {
            if(!in_array($field, $validSearchFields)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid query field: ' . $key]);
                return;
            }else{
                $validSearchVlaues[$field] = $queryValue;
            }
        }

        $recipes = $this->getAllRecipes();
        if($recipes == null) {
            http_response_code(404);
            echo json_encode("error => The recipes databases is empty, you can help by expanding it ");
            return;
        }

        $filteredRecipes = [];


        foreach($recipes as $recipe) {
            $valid = true;
            foreach($validSearchVlaues as $field => $queryValue) {
                switch ($field) {

                    case 'name':
                        
                        if (strtoupper($recipe['name']) !=  strtoupper($queryValue)) {
                            $valid = false;
                        }
                        break;
                        
                    case 'nameFR':
                        if (strtoupper($recipe['nameFR']) !=  strtoupper($queryValue)) {
                            $valid = false;
                        }
                        break;
                        
                    case 'author':
                        if (strtoupper($recipe['Author']) !=  strtoupper($queryValue)) {
                            $valid = false;
                        }
                        break;
                        
                    case 'without':



                        $found = false;
                        if ($recipe['Without'] != null && is_array($recipe['Without'])) {
                            foreach ($recipe['Without'] as $item) {
                                if (strtoupper($item) ==  strtoupper($queryValue)) {
                                    $found = true;
                                    break;
                                }
                            }

                        }
                        if (!$found) {
                            $valid = false;
                        }
                        break;

                        
                    case 'ingredient':

                        $found = false;


                        if ( $recipe['ingredients'] != null && is_array($recipe['ingredients'])) {
                            foreach ($recipe['ingredients'] as $ing) {
                                
                                foreach($ing as $key => $value) {
                                    if ($value != null && strtoupper($value) == strtoupper($queryValue)) {
                                        $found = true;
                                        break;
                                    }
                                }
                               
                            }
                        }
                        if (!$found) {
                            $valid = false;
                        }
                        break;

                        
                    case 'likes':
                        if ($recipe['likes'] != $queryValue) {
                            $valid = false;
                        }
                        break;
                        
                    default:
                        $valid = false;
                        break;
                }
                
                // Si un critère ne correspond pas, on arrête la vérification de cette recette
                if (!$valid) {
                    break;
                }
            
            }

            if($valid) {
                $filteredRecipes[] = $recipe;
            }
        }


        if(count($filteredRecipes) == 0) {
            http_response_code(404);
            echo json_encode("error => No recipe found with the specified query");
            return;
        }

        http_response_code(200);
        //TODO header
        echo json_encode($filteredRecipes);
    }



    public function handleRecipeConsulting($params) {//nom
        $recipe_name = $params[0]; // Extraire l'ID depuis le tableau

        $recipes = $this->getAllRecipes();
   

        if($recipes == null) {
            http_response_code(404);
            echo json_encode("error => The recipes databases is empty, you can help by expanding it ");
            return;
        }

        foreach($recipes as $recipe) {
            if($recipe['name'] == $recipe_name) {
                http_response_code(200);

                //TODO header
                //echo json_encode($recipe);
                return $recipe;
            }
        }

        http_response_code(404);
        echo json_encode("error => Recipe not found: $recipe_name");
        return; 
    }





    public function handleRecipeTraduction($params, $splitedQuery) {
        $language = $splitedQuery['lang'] == null ? strtolower($splitedQuery['lang']) : 'en';        //default language is english

        if($language != 'fr' && $language != 'en') {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid language']);
            return;
        }

        $recipe = $this->handleRecipeConsulting($params);
        if($recipe == null) {
            return;
        }


        //TODO


    }




    public function handleRecipeFotoPublication($params, $splitedQuery) {

        if($splitedQuery['img'] == null) {
            http_response_code(412);
            echo json_encode(['error' => 'Missing photo or invalid query']);
            return;
        }
        $imgURL = $splitedQuery['img'];

        //TODO check url validity

        $recipe = $this->handleRecipeConsulting($params);
        if($recipe == null) {
            return;
        }



        if($recipe['imageURL'] == null || !is_array($recipe['imageURL'])) {
            $recipe['imageURL'] = [];
        } elseif(in_array($imgURL, $recipe['imageURL'])) {
            http_response_code(400);
            echo json_encode(['error' => 'This photo is already published']);
            return;
        }

        $recipe['imageURL'][] = $imgURL;

        $this->updateRecipes($recipe);

        http_response_code(200);
        //todo header
        echo json_encode($this->getAllRecipes());
    }


    public function handleRecipeLike($params) {
        $recipe = $this->handleRecipeConsulting($params);
        if($recipe == null) {
            return;
        }

        if($recipe['likes'] == null) {
            $recipe['likes'] = 0;
        }
        $recipe['likes']++;
        $this->updateRecipes($recipe);

        http_response_code(200);
        echo json_encode($this->getAllRecipes());
        
    }


    


    private function getAllRecipes(): array
	{
		return file_exists($this->filePath) ? json_decode(file_get_contents($this->filePath), true) ?? [] : [];
	}

    private function updateRecipes(array $recipe): void
    {
        $recipes = $this->getAllRecipes();

        foreach($recipes as &$rec) {
            if($rec['name'] == $recipe['name']) {
                $rec = $recipe;
                break;
            }
        }
        file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT));
               

        
    }




}

?>
