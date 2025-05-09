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


        $recipes = $this->getAllRecipes();
        foreach ($recipes as $recipe) {
            if (strcasecmp($recipe['name'], $data->name) === 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Recipe name already exists']);
                return;
            }
        }

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
            'status' => 'pending'
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
                $recipe['ingredientsFR'] = $data->ingredientsFR?? $recipe['ingredientsFR'];
                $recipe['steps'] = $data->steps ?? $recipe['steps'];
                $recipe['stepsFR'] = $data->stepsFR?? $recipe['stepsFR'];
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





    public function handleRecipeApproval():void {

        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json);
        if (!($data->name) || !($data->status)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid name or status']);
            return;
        }

        $name = urldecode($data->name);
        $status = $data->status;
        $recipes = $this->getAllRecipes();

        foreach ($recipes as &$recipe) {
            if ($recipe['name'] === $name) {
                $recipe['status'] = $status;
                file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT));
                http_response_code(200);
                echo json_encode(['message' => 'Recette request ' . $status]);
                return;
            }
            
        }
        http_response_code(404);
        echo json_encode(['error' => 'Recipe request not found']);
    }
    public function handleRecipeDeny():void {

        if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid Content-Type']);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json);
        if (!($data->name) || !($data->status)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid name or status']);
            return;
        }

        $name = urldecode($data->name);
        $status = $data->status;
        $recipes = $this->getAllRecipes();

        foreach ($recipes as &$recipe) {
            if ($recipe['name'] === $name) {
                $recipe['status'] = $status;
                file_put_contents($this->filePath, json_encode($recipes, JSON_PRETTY_PRINT));
                http_response_code(200);
                echo json_encode(['message' => 'Recette request ' . $status]);
                return;
            }
            
        }
        http_response_code(404);
        echo json_encode(['error' => 'Recipe request not found']);
    }


    /*
    * 
    * Cette fonction permet de consulter toutes les recettes disponibles.
    * Elle retourne une liste complète des recettes sous forme de réponse JSON.
    * 
    * - Récupère toutes les recettes existantes.
    * - Retourne la liste des recettes sous forme de réponse JSON.
    * 
    */
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
    
    public function handleRecipeConsultingAll2() {
        $recipes = $this->getAllRecipes();
    
        if ($recipes == null || empty($recipes)) {
            http_response_code(404);
            echo json_encode(["error" => "The recipes database is empty, you can help by expanding it."]);
            return;
        }
    
        // Filter roles with status "pending"
        $pendingRecipes = array_filter($recipes, function($recipe) {
            return isset($recipe['status']) && $recipe['status'] === 'pending';
        });
    
        // Optional: Re-index array (so it returns a clean [0,1,2,...] array in JSON)
        $pendingRecipies = array_values($pendingRecipes);
    
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($pendingRecipes);
    }


    /*
    * Cette fonction permet de consulter les détails d'une recette spécifique.
    * Elle retourne toutes les informations disponibles sur la recette.
    * 
    * - Vérifie si la recette existe.
    * - Retourne les données complètes de la recette sous forme de réponse JSON.
    * 
    */
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




    /*
    * Cette fonction gère la traduction d'une recette. Elle récupère les données
    * d'une recette spécifique (en anglais et en français) et les retourne pour
    * permettre leur affichage ou modification.
    * 
    * 
    * - Vérifie si la recette existe.
    * - Récupère les champs nécessaires pour la traduction.
    * - Retourne les données de la recette sous forme de réponse JSON.
    
    * */
    public function handleRecipeTraduction($params) {
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

        $response = [];
        //include if only one langauge has information
        if (!(empty($recipe['name']) && empty($recipe['nameFR'])) &&    //if it's not fully empty
            !(!empty($recipe['name']) && !empty($recipe['nameFR']))){   //if it's not fully filled
            $response['name'] = $recipe['name'] ?? null;
            $response['nameFR'] = $recipe['nameFR'] ?? null;
        }


        if (!empty($recipe['ingredients']) && !empty($recipe['ingredientsFR'])) {
            
            $iter = count($recipe['ingredients']) > count($recipe['ingredientsFR']) ? $recipe['ingredients'] : $recipe['ingredientsFR'];
        
            foreach ($iter as $index => $ingredient) {


                if($recipe['ingredients'][$index] !== null && $recipe['ingredientsFR'][$index] !== null) {
                    //si les deux champs sont complétements remplis on ne l'envoie pas

                    if($recipe['ingredients'][$index]['quantity'] !== null && $recipe['ingredients'][$index]['name'] !== null && $recipe['ingredients'][$index]['type'] !== null
                     && $recipe['ingredientsFR'][$index]['quantity'] !== null && $recipe['ingredientsFR'][$index]['name'] !== null && $recipe['ingredientsFR'][$index]['type'] !== null) {

                        continue;
                    }


                    $ingredientEN = $recipe['ingredients'][$index] ?? ['quantity' => null, 'name' => null, 'type' => null];
                    $ingredientFR = $recipe['ingredientsFR'][$index] ?? ['quantity' => null, 'name' => null, 'type' => null];

                   
                    
                    //si n'importe quel champ est vide on envoi l'ingrédient
                    if ($ingredientEN['quantity'] === null || $ingredientEN['name'] === null || $ingredientEN['type'] === null
                    || $ingredientFR['quantity'] === null || $ingredientFR['name'] === null || $ingredientFR['type'] === null) {
                        $response['ingredients'][] = $ingredientEN;
                        $response['ingredientsFR'][] = $ingredientFR;


                    }

                }
             
            
                //source de l'erreur debugger ici 

                //cas simple, un des deuxingrédients est complétement vide
                else if($recipe['ingredients'][$index] !== null && $recipe['ingredientsFR'][$index] === null
                || $recipe['ingredients'][$index] === null && $recipe['ingredientsFR'][$index] !== null) {




                     //cas un ingrédient existe mais tout ces valeurs sont null et ça contrepart n'existe pas
                    if(($recipe['ingredients'][$index] === null &&
                            $recipe['ingredientsFR'][$index]['quantity'] === null && $recipe['ingredientsFR'][$index]['name'] === null  &&
                            $recipe['ingredientsFR'][$index]['type'] === null)
                
                            || ($recipe['ingredientsFR'][$index] === null &&
                            $recipe['ingredients'][$index]['quantity'] === null && $recipe['ingredients'][$index]['name'] === null  &&
                            $recipe['ingredients'][$index]['type'] === null)) {
                        continue;
                    }
                  

                    $response['ingredients'][] = $recipe['ingredients'][$index] ?? ['quantity' => null, 'name' => null, 'type' => null];
                    $response['ingredientsFR'][] = $recipe['ingredientsFR'][$index] ?? ['quantity' => null, 'name' => null, 'type' => null];
                }


                
            }


        }


        //cas simple un des deux champs d'ingrédients n'existe pas
        if( (empty($recipe['ingredients']) && !empty($recipe['ingredientsFR']))
        || (!empty($recipe['ingredients']) && empty($recipe['ingredientsFR']))){
            $response['ingredients'] = $recipe['ingredients'] ?? null;
            $response['ingredientsFR'] = $recipe['ingredientsFR'] ?? null;
        }


        if (!(empty($recipe['steps']) && empty($recipe['stepsFR']))){
            if ((empty($recipe['steps']) && !empty($recipe['stepsFR'])) ||
                    (!empty($recipe['steps']) && empty($recipe['stepsFR']))){
                $response['steps'] = $recipe['steps'] ?? null;
                $response['stepsFR'] = $recipe['stepsFR'] ?? null;
            }
            else if(!empty($recipe['steps']) && !empty($recipe['stepsFR'])){

                foreach($recipe['steps'] as $key => $step) {
                    
                    if (($recipe['stepsFR'][$key] !== null && $recipe['steps'][$key] !== null) ||
                        ($recipe['stepsFR'][$key] === null && $recipe['steps'][$key] === null)) {
                    } else {
                        $response['steps'] = $recipe['steps'];
                        $response['stepsFR']= $recipe['stepsFR'];
                        break;
                    }
                }
                
            }
              
        }
    
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($response);
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

    /*
    *
    * Cette fonction gère l'ajout d'un "like" à une recette spécifique.
    * Elle reçoit le nom de la recette en paramètre, recherche cette recette
    * dans les données existantes, incrémente le compteur de "likes" 
    *
    * - Vérifie si la recette existe.
    * - Si elle existe, incrémente le nombre de "likes".
    * - Met à jour les données persistantes avec le nouveau nombre de "likes".
    * - Retourne une réponse JSON contenant les données mises à jour ou un message d'erreur.
    *
    */
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
    public function findRecipeByName(string $recipeName): ?array{
    
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
