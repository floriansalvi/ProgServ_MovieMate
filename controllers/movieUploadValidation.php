<?php

use ch\MovieManager;
use ch\GenreManager;

require_once './config/autoload.php';
require_once './config/base_url.php';

$titleErr = $descriptionErr = $realisatorErr = $releaseDateErr = $durationErr = $genreErr = $coverErr = $successMessage = "";
$validationErr = false;

if(filter_has_var(INPUT_POST, 'movie')) {
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_UNSAFE_RAW));
    $description = filter_input(INPUT_POST, 'description', FILTER_UNSAFE_RAW);
    $realisator = filter_input(INPUT_POST, 'realisator', FILTER_UNSAFE_RAW);
    $releaseDate = filter_input(INPUT_POST, 'release_date', FILTER_UNSAFE_RAW);
    $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
    $genreId = filter_input(INPUT_POST, 'genre', FILTER_VALIDATE_INT);
    $cover = $_FILES['cover'] ?? null;

    $dbMovie = new MovieManager();

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

    if(empty($description)){
        $descriptionErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    }else if (!preg_match('/^[a-zA-Z0-9:()\-\'" ,]{1,750}$/', $description)){
        $descriptionErr = '<div class="alert alert-danger">Ce champ doit comporter 1 à 750 caratères. Il peut être composé de majuscules, minuscules, chiffres, apostrophes, paranthèses, double-points, virgules, tirets.</div>';
        $validationErr = true; 
    };

    function validateAndFormatName($value):array {
        $isValid = true;
        $error = "";
        $formatedName = "";
        
        if(strlen($value) > 32){
            $isValid = false;
            $error = $error . "Ce champ peut contenir 32 caractères au maximum.";
        }
    
        if(!preg_match("/^([a-zA-Zäàâèêéïöôüç]+([-'\s][a-zA-Zäàâèêéïöôüç]+)*)$/", $value)){
            $isValid = false;
            $error = $error . "Le champ ne contenir que des lettres, espaces, tirets et apostrophes.";
        }
    
        if(empty($error)){
            $formatedName = ucwords(strtolower($value), " \t\r\n\f\v-'");
        }
    
        return [
            'isValid' => $isValid,
            'error' => $error,
            'formatedName' => $formatedName
        ];
    }

    $realisatorValidation = validateAndFormatName($realisator);
    if(empty($realisator)){
        $realisatorErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    }else if(!$realisatorValidation['isValid']){
        $realisatorErr = '<div class="alert alert-danger">' . $realisatorValidation['error'] . '</div>';
        $validationErr = true;
    }

    if(empty($releaseDate)){
        $releaseDateErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    }else if($releaseDate < '1895-12-28' || $releaseDate > date("Y-m-d")){
        $releaseDateErr = '<div class="alert alert-danger">La date selectionnée n\'est pas valide.</div>';
        $validationErr = true;
    }

    if(empty($duration)){
        $durationErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    }else if($duration <= '0'){
        $durationErr = '<div class="alert alert-danger">La durée d\'un film ne peut pas être nulle.</div>';
        $validationErr = true;
    }

    $dbGenre = new GenreManager();
    $genres = $dbGenre->getAllGenres();
    $genresId = array_map(function($genre){
        return $genre['id'];
    }, $genres);

    if(empty($genreId)){
        $genreErr = '<div class="alert alert-danger">Un genre doit être sélectionné.</div>';
        $validationErr = true;
    }else if(!in_array($genreId, $genresId)){
        $genreErr = '<div class="alert alert-danger">La valeur entrée n\'est pas valide.</div>';
        $validationErr = true;
    }

    if(!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK){
        $coverErr = '<div class="alert alert-danger">Aucune image n\'a été inserée.</div>';
        $validationErr = true;
    }else {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $filesInfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($filesInfo, $cover['tmp_name']);
        finfo_close($filesInfo);

        $maxImgSize = 2 * 1024 * 1024;

        if (!in_array($type, $allowedTypes)) {
            $coverErr = '<div class="alert alert-danger">Seuls les jpg et png sont acceptés.</div>';
            $validationErr = true;
        }else if ($cover['size'] > $maxImgSize){
            $coverErr = '<div class="alert alert-danger">Le poids de l\'image ne peut pas exéder 2 MB.</div>';
            $validationErr = true;
        }
    }

    if(!$validationErr){
        $realisator = $realisatorValidation['formatedName'];
        
        $coverName = strtolower($title);
        $coverName = preg_replace('/[^a-z0-9_]/', '_', $coverName);
        $coverName = preg_replace('/_+/', '_', $coverName);
        $coverName = trim($coverName, '_');
        $coverName .= "." . pathinfo($cover['name'], PATHINFO_EXTENSION);
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . "/ProgServ_MovieMate/assets/img/movie_cover/" . $coverName;

        if(!move_uploaded_file($cover['tmp_name'], $uploadPath)){
            throw new Exception("L'image n'a pas pu être uploadée.");
        }else{
            $dbMovie->saveMovie($title, $description, $realisator, $releaseDate, $duration, $genreId, $coverName);
            $successMessage = '<div class="alert alert-sucess">Le film a été publié.</div>';
        }
    }
}