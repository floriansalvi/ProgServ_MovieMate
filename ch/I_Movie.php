<?php

namespace ch;

interface I_Movie {
    public function isTitleUsed($title):bool; //
    public function saveMovie($title, $description, $realisator, $releaseDate, $duration, $genreId, $coverName):bool; //
    public function getMovies(?string $sortColumn, ?string $sortOrder, ?int $genreId, ?int $limit, ?int $offset):array; //
    public function getMovieDatas($movieId):array;
    public function updateMovieField($movieId, $field, $value); 
    public function deleteMovie($movieId):bool; //
}