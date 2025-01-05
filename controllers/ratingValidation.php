<?php

use ch\RatingManager;

require_once './config/base_url.php';

unset($_SESSION['movie_to_delete']);


$db = new RatingManager();

// Initialize error variables and a flag for validation errors.
$rateErr = $commentErr = $errorMessage = "";
$validationErr = false;

// Check if the rate form has been submitted
if(filter_has_var(INPUT_POST, 'rate')) {
    $movieRate = filter_input(INPUT_POST, 'stars', FILTER_VALIDATE_INT);
    $movieComment = filter_input(INPUT_POST, 'comment', FILTER_UNSAFE_RAW);

    // Validate the rate
    if(empty($movieRate)){
        $rateErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    } else if ((int)$movieRate < 1 || (int)$movieRate > 5){
        $rateErr = '<div class="alert alert-danger">Un film peut avoir une note de 1 à 5.</div>';
        $validationErr = true;
    }

    // Validate the comment
    if(empty($movieComment)){
        $movieComment = null;
    } else if (strlen($movieComment) > 500){
        $commentErr = '<div class="alert alert-danger">Un avis doit faire entre 0 et 500 caractères..</div>';
        $validationErr = true;
    }

    // If no validation errors, proceed to save the rating.
    if($validationErr === false){
        $db->saveRating($_GET['id'], $_SESSION['user']['id'], $movieRate, $movieComment);
        header("Location: " . BASE_URL . "movie.php?id=" . $_GET['id']);
        exit();
    }

}

$delete = false; 

// Check if the delete-rating form has been submitted
if(filter_has_var(INPUT_POST, var_name: 'delete-rating')) {
    $ratingId = (int)filter_input(INPUT_POST, 'rating_id', FILTER_VALIDATE_INT);
    $movieId = (int)filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);

    // Check whether the id match a rating
    $rating = $db->getRatingDatasById($ratingId);
    
    // Retrieves the userId stored in the sessions variables
    $userId = $_SESSION['user']['id'];

    // If the rating is empty or does not match the ratingId and movieId, throw an exception. Otherwise, store the rating id in a session variable.
    if(empty($rating)){
        throw new Exception("No corresponding rating could be found.");
    }else if($rating['movie_id'] == $movieId && $rating['user_id'] == $userId || $_SESSION['user']['role'] === 'admin'){
        $_SESSION['rating_to_delete'] = $rating['id'];
    }else{
        throw new Exception("No corresponding rating could be found.");
    }
}

// Check if the (delete-rating) confirmation form has been submitted
if(filter_has_var(INPUT_POST, 'confirmation')) {
    $ratingId = (int)filter_input(INPUT_POST, 'rating_id', FILTER_VALIDATE_INT);
    $movieId = (int)filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
    $confirmation = (int)filter_input(INPUT_POST, 'confirmation', FILTER_VALIDATE_INT);
    
    $userId = $_SESSION['user']['id'];

    // Check if the delete rating confirmation = 0 (no) or 1 (yes)
    if($confirmation !== 1){
        // unset the session variable. If null, the delete-rating form is not shown anymore.
        unset($_SESSION['rating_to_delete']);
    } else {
        // If $confirmation == 0, retrieve the rating data.
        $rating = $db->getRatingDatasById($ratingId);
        // If $rating is empty, throw an exception. Otherwise, delete the rating from the database.
        if(empty($rating)){
            throw new Exception("No corresponding rating could be found.");
            // Check if the rating related ids match with the input ids. If it matches, delete the rating. Otherwise, throw an exception.
        }else if($rating['movie_id'] == $movieId && $rating['user_id'] == $userId || $_SESSION['user']['role'] === 'admin'){
            $db->deleteRating($ratingId);
            unset($_SESSION['rating_to_delete']);
        }else{
            throw new Exception("No corresponding rating could be found.");
        }
    }
}