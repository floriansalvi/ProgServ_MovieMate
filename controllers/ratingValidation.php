<?php

use ch\RatingManager;

require_once './config/base_url.php';

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

if(filter_has_var(INPUT_POST, 'delete-rating')) {
    $ratingId = (int)filter_input(INPUT_POST, 'rating_id', FILTER_VALIDATE_INT);
    
    $rating = $db->getRatingDatasById($ratingId);
    
    $movieId = $_GET['id'];
    $userId = $_SESSION['user']['id'];

    if(empty($rating)){
        throw new Exception("No corresponding rating could be found.");
    }else if($rating['movie_id'] == $movieId && $rating['user_id'] == $userId){
        $db->deleteRating($ratingId);
        header("Location: " . BASE_URL . "movie.php?id=" . $movieId);
        exit();
    }else{
        throw new Exception("No corresponding rating could be found.");
    }
}