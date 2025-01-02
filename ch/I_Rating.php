<?php

namespace ch;

interface I_Rating {
    public function isMovieRatedByUser($movieId, $userId):bool;
    public function saveRating($movieId, $userId, $rate, ?string $comment):bool;
    public function updateRatingAvg($movieId):bool;
    public function getRatings(?string $idType, ?int $id, ?int $limit):array;
    public function getRatingDatasById($ratingId):array;
    public function getRatingsCount(?string $idType, ?int $id):int;
    public function deleteRating($ratingId):bool;
    public function deleteUserRatings($userId):bool;
    public function deleteMovieRatings($movieId):bool;
}