<?php

namespace ch;

interface I_Rating {
    public function isMovieRatedByUser($movieId, $userId):bool;
    public function saveRating($movieId, $userId, $rate, $comment):bool;
    public function getRatings(?string $idType, ?int $id, ?int $limit):array;
    public function deleteRating($ratingId):bool;
}