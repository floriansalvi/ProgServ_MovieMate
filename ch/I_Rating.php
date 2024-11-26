<?php

namespace ch;

interface I_Rating {
    public function isMovieRatedByUser($movieId, $userId):bool;
    public function saveRating($movieId, $userId, $rate, $comment):bool;
    public function getUserRatings($userId):array;
    public function getMovieRatings($movieId):array;
    public function deleteRating($ratingId):bool;
}