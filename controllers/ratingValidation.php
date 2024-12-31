<?php

use ch\RatingManager;

require_once './config/base_url.php';

unset($_SESSION['movie_to_delete']);


$db = new RatingManager();

$rateErr = $commentErr = $errorMessage = "";
$validationErr = false;

if(filter_has_var(INPUT_POST, 'rate')) {
    $movieRate = filter_input(INPUT_POST, 'stars', FILTER_VALIDATE_INT);
    $movieComment = filter_input(INPUT_POST, 'comment', FILTER_UNSAFE_RAW);

    if(empty($movieRate)){
        $rateErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    } else if ((int)$movieRate < 1 || (int)$movieRate > 5){
        $rateErr = '<div class="alert alert-danger">Un film peut avoir une note de 1 à 5.</div>';
        $validationErr = true;
    }

    if(empty($movieComment)){
        $movieComment = null;
    } else if (strlen($movieComment) > 500){
        $commentErr = '<div class="alert alert-danger">Un avis doit faire entre 0 et 500 caractères..</div>';
        $validationErr = true;
    }

    if($validationErr === false){
        $db->saveRating($_GET['id'], $_SESSION['user']['id'], $movieRate, $movieComment);
        header("Location: " . BASE_URL . "movie.php?id=" . $_GET['id']);
        exit();
    }

}

$delete = false; 

if(filter_has_var(INPUT_POST, var_name: 'delete-rating')) {
    $ratingId = (int)filter_input(INPUT_POST, 'rating_id', FILTER_VALIDATE_INT);
    $movieId = (int)filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);

    
    $rating = $db->getRatingDatasById($ratingId);
    
    $userId = $_SESSION['user']['id'];

    if(empty($rating)){
        throw new Exception("No corresponding rating could be found.");
    }else if($rating['movie_id'] == $movieId && $rating['user_id'] == $userId || $_SESSION['user']['role'] === 'admin'){
        $_SESSION['movie_to_delete'] = $movieId;
    }else{
        throw new Exception("No corresponding rating could be found.");
    }
}

if(filter_has_var(INPUT_POST, 'confirmation')) {
    $ratingId = (int)filter_input(INPUT_POST, 'rating_id', FILTER_VALIDATE_INT);
    $movieId = (int)filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);

    
    $confirmation = (int)filter_input(INPUT_POST, 'confirmation', FILTER_VALIDATE_INT);
    
    $userId = $_SESSION['user']['id'];

    if($confirmation !== 1){
        unset($_SESSION['movie_to_delete']);
    } else {
        $rating = $db->getRatingDatasById($ratingId);
        if(empty($rating)){
            throw new Exception("No corresponding rating could be found.");
        }else if($rating['movie_id'] == $movieId && $rating['user_id'] == $userId || $_SESSION['user']['role'] === 'admin'){
            $db->deleteRating($ratingId);
            unset($_SESSION['movie_to_delete']);
        }else{
            throw new Exception("No corresponding rating could be found.");
        }
    }
}