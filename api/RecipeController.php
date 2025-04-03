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

      // Check if the recipe name already exists
        $recipes = $this->getAllRecipes();
        foreach ($recipes as $recipe) {
            if (strcasecmp($recipe['name'], $data->name) === 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Recipe name already exists']);
                return;
            }
        }

            // sanitize the input data
        // Validate necessary fields
        $missingFields = [];
        if (!$data->name) 
            $missingFields[] = 'name';
        if (!$data->ingredients) 
            $missingFields[] = 'ingredients';
        if (!$data->steps) 
            $missingFields[] = 'steps';
        if (!$data->timers) 
            $missingFields[] = 'timers';
    
        if ($missingFields) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required recipe information: ' . implode(', ', $missingFields)]);
            return;
        }
    

        $recipes = $this->getAllRecipes();

        // Create a new recipe object using the input data
        // La recette peut avoir un ou plusieurs champs avec la valeur null(le titre, la liste des ingrédients, etc ..)
        $newRecipe = [
            'name' => $data->name,
            'nameFR' => $data->nameFR ?? null,
            'Author' => $data->Author ?? null,
            'Without' => $data->Without ?? null,
            'ingredients' => $data->ingredients,
            'steps' => $data->steps,
            'timers' => $data->timers,
            'imageURL' => $data->imageURL ?? null,
            'originalURL' => $data->originalURL ?? null,
        ];
        $recipes[] = $newRecipe;
        file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        http_response_code(201);
        echo json_encode(['message' => 'Recipe proposed successfully']);
    }

    public function handleRecipeDeletion() {

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
                file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                http_response_code(200);
                echo json_encode(['message' => 'Recipe deleted successfully']);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Recipe not found']);
    
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
    
                file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                http_response_code(200);
                echo json_encode(['message' => 'Recipe modified successfully']);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Recipe not found']);
    }
    //---------------------------------------------------------------





    public function handleRecipeApproval($params,$splitedQuery):void {

        // Ensure the correct Content-Type header
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }


        // Validate and sanitize form data
        $recipeName = isset($splitedQuery['name']) ? filter_var($splitedQuery['name'], FILTER_SANITIZE_STRING) : null;
        $status = isset($splitedQuery['status']) ? filter_var($splitedQuery['status'], FILTER_SANITIZE_STRING) : null;  
        

        if (!$recipeName || !$status) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields:'. $recipeName . $status]);
            return;
        }
        $validStatuses = ['terminée', 'publiée'];
        if (!in_array($status, $validStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status. Allowed values are: terminée, publiée']);
            return;
        }


        $recipes = $this->getAllRecipes();
         // Chercher la recette à approuver     -> l'administrateur déclare une recette terminée ou publiée en fonction de ce qui manque
         foreach ($recipes as &$recipe) {
            if ($recipe['name'] === $recipeName) {
                echo json_encode($recipe);
                // Vérifier si le champ 'status' existe, sinon le créer
                if (!isset($recipe['status'])) {
                    $recipe['status'] = null;
                }

                $recipe['status'] = $status;

                $this->updateRecipes($recipe);

        
                http_response_code(200);
                echo json_encode(['message' => 'Recipe status updated successfully', 'status' => $status]);
                return;

            }
        }

        // Si la recette n'est pas trouvée
        http_response_code(404);
        echo json_encode(['error' => 'Recipe not found']);
    }




    public function handleRecipeSearch($params, $splitedQuery) {
        // Ensure the correct Content-Type header
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }

        $validSearchFields = ['name',
                              'nameFR', 
                              'author', 
                              'without', 
                              'ingredient',
                              'likes', 
                              //'lang'   une foie traduction sera faite on pourra chercher par langue
        ];



        $validSearchVlaues = [];
        
        // Validate and sanitize the search query
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
            header('Content-Type: application/json');
            echo json_encode("error => No recipe found with the specified query");
            return;
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($filteredRecipes);
    }



    public function handleRecipeConsultingAll() {
        $recipes = $this->getAllRecipes();
        if($recipes == null) {
            http_response_code(404);
            echo json_encode("error => The recipes databases is empty, you can help by expanding it ");
            return;
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($recipes);
    }



    public function handleRecipeConsulting($params) {//nom
         // Ensure the correct Content-Type header
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }
        $recipeName = urldecode($params[0]);

        // Validate and sanitize form data
        $recipeName = filter_var($recipeName, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $recipe = $this->findRecipeByName($recipeName);
        if ($recipe !== null) {
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($recipe);
        }else {
            http_response_code(404);
            echo json_encode(['error' => 'Recipe not found']);
        }
    }





    public function handleRecipeTraduction($params, $splitedQuery) {
        // Ensure the correct Content-Type header
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }

        $recipe = $this->findRecipeByName(urldecode($params[0]));
        if ($recipe === null)
            return;

        /*   // Vérifier si l'utilisateur est autorisé (rôle de traducteur)
        if (!AuthMiddleware::authCheck('translator')) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied. Only translators can modify translations.']);
            return;
        }*/

                    // Validate and sanitize

        $language = $splitedQuery['lang'] == null ? strtolower($splitedQuery['lang']) : 'en';//default language is english

        if($language != 'fr' && $language != 'en') {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid language']);
            return;
        }

        $json = file_get_contents('php://input');
        $body = json_decode($json, true);

        if ($body === null || !is_array($body)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing request body']);
            return;
        }
        if (!$body['translationData'] || !is_array($body['translationData'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing translation data']);
            return;
        }

        $translationData = $body['translationData'];
        $sanitizedData = [];


        echo $body['translationData'];

            //v1 traduction vers le français
         

        // Valider le champ nameFR
        if (isset($translationData['nameFR'])) 
            $sanitizedData['nameFR'] = filter_var($translationData['nameFR'], FILTER_SANITIZE_STRING);
        // Valider le champ ingredientsFR
        if (isset($translationData['ingredientsFR'])) {
            if (is_array($translationData['ingredientsFR'])) {
                $sanitizedData['ingredientsFR'] = array_map(function ($ingredient) {
                    if (!is_array($ingredient) || !isset($ingredient['quantity'], $ingredient['name'], $ingredient['type'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid ingredientsFR format. Each ingredient must be an object with quantity, name, and type.']);
                        exit();
                    }
                    return [
                        'quantity' => filter_var($ingredient['quantity'],  FILTER_SANITIZE_STRING),
                        'name' => filter_var($ingredient['name'],  FILTER_SANITIZE_STRING),
                        'type' => filter_var($ingredient['type'],  FILTER_SANITIZE_STRING),
                    ];
                }, $translationData['ingredientsFR']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid ingredientsFR format. It must be an array of objects.']);
                return;
            }
        }
        // Valider le champ stepsFR
        if (isset($translationData['stepsFR'])) {
            if (is_array($translationData['stepsFR'])) {
                $sanitizedData['stepsFR'] = 
                    array_map(function ($step) {
                        return filter_var($step, FILTER_SANITIZE_STRING);
                    }, $translationData['stepsFR']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid stepsFR format. It must be an array.']);
                return;
            }
        }

     
        // modifier la recette avec les données de traduction
        if (isset($sanitizedData['nameFR'])) 
            $recipe['nameFR'] = $sanitizedData['nameFR'];
        if (isset($sanitizedData['ingredientsFR'])) 
            $recipe['ingredientsFR'] = $sanitizedData['ingredientsFR'];
        if (isset($sanitizedData['stepsFR'])) 
            $recipe['stepsFR'] = $sanitizedData['stepsFR'];


        $this->updateRecipes($recipe);
        http_response_code(200);
        echo json_encode(['message' => 'Recipe translation updated successfully']);
    }




    public function handleRecipeFotoPublication($params, $splitedQuery) {
        // Ensure the correct Content-Type header
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type. Expected application/x-www-form-urlencoded']);
            return;
        }

        // Validate and sanitize the image URL from $splitedQuery
        $imageURL = isset($splitedQuery['img']) ? filter_var($splitedQuery['img'], FILTER_SANITIZE_URL) : null;

        if (!$imageURL || !filter_var($imageURL, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing image URL']);
            return;
        }

        $recipe = $this->findRecipeByName(urldecode($params[0]));
        if($recipe == null) 
            return;

        if (!$recipe['imageURL']|| $recipe['imageURL'] === null) {
            // Case 1: existe pas ou vide
            $recipe['imageURL'] = [$imageURL];
        } elseif (is_array($recipe['imageURL'])) {
            //case 2: tableau existe et donc au moins une image dejà publiée
            if (in_array($imageURL, $recipe['imageURL'], true)) {
                http_response_code(400);
                echo json_encode(['error' => 'This photo is already published']);
                return;
            }
            $recipe['imageURL'][] = $imageURL;
        }
        else {
            //case 3: une seule image existé mais pas dans un tableau
            if ($recipe['imageURL'] == $imageURL) {
                http_response_code(400);
                echo json_encode(['error' => 'This photo is already published']);
                return;
            }
            $recipe['imageURL'] = [$recipe['imageURL'], $imageURL];
        }


        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Photo added successfully', 'imageURL' => $recipe['imageURL']]);

    }


    public function handleRecipeLike($params) {
        // Ensure the correct Content-Type header
        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }
        // Validate and sanitize the recipe name
        $recipeName = urldecode($params[0]);
        $recipeName = filter_var($recipeName, FILTER_SANITIZE_STRING);
        if (!$recipeName) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing recipe name']);
            return;
        }
        $recipe = $this->findRecipeByName($recipeName);
        if ($recipe === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Recipe not found']);
            return;
        }

        if($recipe['likes'] == null) {
            $recipe['likes'] = 0;
        }
        $recipe['likes']++;
        $this->updateRecipes($recipe);

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($this->getAllRecipes());
        
    }

    
    //-----------------------------------------------------------------------------------------
    private function findRecipeByName(string $recipeName): ?array{
    
        if(!$recipeName) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing recipe name']);
            return null;
        }
    
        $recipes = $this->getAllRecipes();
        if ($recipes === null) {
            http_response_code(404);
            echo json_encode(['error' => 'The recipes database is empty.']);
            return null;
        }

        foreach ($recipes as $recipe) {
            if (strcasecmp($recipe['name'], $recipeName) === 0) { 
                return $recipe;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => "Recipe not found: $recipeName"]);
        return null;
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
        file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
               

        
    }




}

?>
