<?php

use ch\MovieManager;
use ch\GenreManager;

require_once './config/autoload.php';
require_once './config/base_url.php';

// Initialize variables for error messages and validation flag.
$titleErr = $descriptionErr = $realisatorErr = $releaseDateErr = $durationErr = $genreErr = $coverErr = $successMessage = "";
$validationErr = false;

// Check if the form is submitted.
if(filter_has_var(INPUT_POST, 'movie')) {
    // Retrieve and sanitize input fields.
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_UNSAFE_RAW));
    $description = filter_input(INPUT_POST, 'description', FILTER_UNSAFE_RAW);
    $realisator = filter_input(INPUT_POST, 'realisator', FILTER_UNSAFE_RAW);
    $releaseDate = filter_input(INPUT_POST, 'release_date', FILTER_UNSAFE_RAW);
    $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
    $genreId = filter_input(INPUT_POST, 'genre', FILTER_VALIDATE_INT);
    $cover = $_FILES['cover'] ?? null;

    // Create an instance of MovieManager for database interactions.
    $dbMovie = new MovieManager();

    // Validate the title field.
    if(empty($title)){
        $titleErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    }else if (!preg_match('/^[a-zA-Z0-9:()\-\'" ,]{1,64}$/', $title)){
        $titleErr = '<div class="alert alert-danger">Ce champ doit comporter 1 à 64 caratères. Il peut être composé de majuscules, minuscules, chiffres, apostrophes, paranthèses, double-points, virgules, tirets.</div>';
        $validationErr = true; 
    }else if($dbMovie->isTitleUsed($title)){
        $titleErr = '<div class="alert alert-danger">Ce titre n\'est pas disponible.</div>';
        $validationErr = true; 
    };

    // Validate the description field.
    if(empty($description)){
        $descriptionErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    }else if (!preg_match('/^[a-zA-Z0-9:()\-\'" ,]{1,750}$/', $description)){
        $descriptionErr = '<div class="alert alert-danger">Ce champ doit comporter 1 à 750 caratères. Il peut être composé de majuscules, minuscules, chiffres, apostrophes, paranthèses, double-points, virgules, tirets.</div>';
        $validationErr = true; 
    };

    /**
     * Validates and formats the movie realisator input.
     * 
     * @param string $value The input value to validate.
     * @return array Contains 'isValid', 'error', and 'formatedName'.
     */
    function validateAndFormatName($value):array {
        $isValid = true;
        $error = "";
        $formatedName = "";
        
        // Check the length of the name.
        if(strlen($value) > 32){
            $isValid = false;
            $error = $error . "Ce champ peut contenir 32 caractères au maximum.";
        }
    
        // Ensure the name contains only valid characters.
        if(!preg_match("/^([a-zA-Zäàâèêéïöôüç]+([-'\s][a-zA-Zäàâèêéïöôüç]+)*)$/", $value)){
            $isValid = false;
            $error = $error . "Le champ ne contenir que des lettres, espaces, tirets et apostrophes.";
        }
    
         // Format the name if no errors are found.
        if(empty($error)){
            $formatedName = ucwords(strtolower($value), " \t\r\n\f\v-'");
        }
    
        // Return validation status, error message, and formatted name.
        return [
            'isValid' => $isValid,
            'error' => $error,
            'formatedName' => $formatedName
        ];
    }

    // Validate the realisator field using the custom function.
    $realisatorValidation = validateAndFormatName($realisator);
    if(empty($realisator)){
        $realisatorErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    }else if(!$realisatorValidation['isValid']){
        $realisatorErr = '<div class="alert alert-danger">' . $realisatorValidation['error'] . '</div>';
        $validationErr = true;
    }

     // Validate the release date field.
    if(empty($releaseDate)){
        $releaseDateErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    }else if($releaseDate < '1895-12-28' || $releaseDate > date("Y-m-d")){
        $releaseDateErr = '<div class="alert alert-danger">La date selectionnée n\'est pas valide.</div>';
        $validationErr = true;
    }

    // Validate the duration field.
    if(empty($duration)){
        $durationErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    }else if($duration <= '0'){
        $durationErr = '<div class="alert alert-danger">La durée d\'un film ne peut pas être nulle.</div>';
        $validationErr = true;
    }

    // Create an instance of GenreManager to fetch genre data.
    $dbGenre = new GenreManager();
    $genres = $dbGenre->getAllGenres();
    $genresId = array_map(function($genre){
        return $genre['id'];
    }, $genres);

    // Validate the genre field.
    if(empty($genreId)){
        $genreErr = '<div class="alert alert-danger">Un genre doit être sélectionné.</div>';
        $validationErr = true;
    }else if(!in_array($genreId, $genresId)){
        $genreErr = '<div class="alert alert-danger">La valeur entrée n\'est pas valide.</div>';
        $validationErr = true;
    }

    // Validate the cover file upload.
    if(!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK){
        $coverErr = '<div class="alert alert-danger">Aucune image n\'a été inserée.</div>';
        $validationErr = true;
    }else {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $filesInfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($filesInfo, $cover['tmp_name']);
        finfo_close($filesInfo);

        $maxImgSize = 2 * 1024 * 1024; // Maximum image size is 2 MB.

        // Check if the file type is allowed and size is within limit.
        if (!in_array($type, $allowedTypes)) {
            $coverErr = '<div class="alert alert-danger">Seuls les jpg et png sont acceptés.</div>';
            $validationErr = true;
        }else if ($cover['size'] > $maxImgSize){
            $coverErr = '<div class="alert alert-danger">Le poids de l\'image ne peut pas exéder 2 MB.</div>';
            $validationErr = true;
        }
    }

    // If no validation errors, proceed to save the movie.
    if(!$validationErr){
        // Format the realisator's name and prepare the cover file name.
        $realisator = $realisatorValidation['formatedName'];
        $coverName = strtolower($title);
        $coverName = preg_replace('/[^a-z0-9_]/', '_', $coverName);
        $coverName = preg_replace('/_+/', '_', $coverName);
        $coverName = trim($coverName, '_');
        $coverName .= "." . pathinfo($cover['name'], PATHINFO_EXTENSION);
        
        // Define the upload path for the cover image.
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . "/ProgServ_MovieMate/assets/img/movie_cover/" . $coverName;

        // Move the uploaded file to the specified location.
        if(!move_uploaded_file($cover['tmp_name'], $uploadPath)){
            throw new Exception("L'image n'a pas pu être uploadée.");
        }else{
            // Save the movie details in the database.
            $dbMovie->saveMovie($title, $description, $realisator, $releaseDate, $duration, $genreId, $coverName);
            $successMessage = '<div class="alert alert-success">Le film a été publié.</div>';
        }
    }
}