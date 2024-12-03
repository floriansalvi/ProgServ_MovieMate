<?php

namespace ch;

interface I_Movie {
    public function isTitleUsed($title):bool;
    public function saveMovie($title, $description, $realisator, $releaseDate, $duration, $genreId, $coverName):bool;
    public function getAllMovies():array;
    public function getGenreMovies($genreId):array;
    public function getBestRatedMovies():array;
    public function getMovieDatas($movieId):array;
    private function updateField($movieId, $field, $value);
    public function updateTitle($movieId, $newTitle):bool;
    public function updateDescription($movieId, $newDescription):bool;
    public function updateRealisator($movieId, $newRealisator):bool;
    public function updateReleaseDate($movieId, $newReleaseDate):bool;
    public function updateDuration($movieId, $newDuration):bool;
    public function updateGenre($movieId, $newGenreId):bool;
    public function updateCover($movieId, $newMovieCoverName):bool;
    public function deleteMovie($movieId):bool;
}