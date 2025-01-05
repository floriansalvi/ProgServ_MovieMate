<?php

namespace ch;

/**
 * Interface I_Movie provides a contract for movies-related operations.
 */
interface I_Movie {
    
    /**
    * Return a boolean that tell the user if the movie title is already used or not
    * 
    * @param string $title The title of the movie.
    * @return bool Returns true if the title is already used, false if the title is not already used
    */
    public function isTitleUsed($title):bool;

    /**
    * Store a movie and its information in the database.
    * 
    * @param string $title The title of the movie.
    * @param string $description The description of the movie.
    * @param string $realisator The realisator of the movie.
    * @param string $release_date The release date of the movie. Format = YYYY-MM-DD
    * @param int $duration The duration of the movie.
    * @param int $genreId The id of the genre associated with the movie.
    * @param int $coverName The name of the movie's cover image.
    *
    * @return bool Returns true if the movie was stored, false if the movies could not stored.
    */
    public function saveMovie($title, $description, $realisator, $releaseDate, $duration, $genreId, $coverName):bool;

    /**
     * Retrieves a list of movies and their information stored in the database.
     * 
     * @param string|null $sortColumn The column by which to sort the movies. 
     *                                If null, no sorting is applied.
     * @param string|null $sortOrder The order to sort the movies by. Can be 'ASC' or 'DESC'. 
     *                               If null, the default sorting order is applied.
     * @param int|null $genreId The ID of the genre to filter the movies by. If null, no filtering is applied.
     * @param int|null $limit The number of movies to retrieve. If null, no limit is applied.
     * @param int|null $offset The offset from which to start retrieving movies. If null, no offset is applied.
     *
     * @return array Returns an array of movies that match the specified parameters. If no movies match, returns an empty array.
     */
    public function getMovies(?string $sortColumn, ?string $sortOrder, ?int $genreId, ?int $limit, ?int $offset):array;
    
    /**
     * Retrieves the total count of movies in the database.
     * 
     * @param int|null $genreId The ID of the genre to filter the movie count by. If null, counts all movies.
     * 
     * @return int Returns the total count of movies. If an error occurs, returns 0.
     */
    public function getMoviesCount(?int $genreId):int;
   
    /**
     * Retrieves detailed information about a specific movie from the database.
     * 
     * @param int $movieId The ID of the movie to retrieve.
     * 
     * @return array Returns an associative array containing the movie data.
     *               If the movie is not found or an error occurs, returns an empty array.
     */
    public function getMovieDatas($movieId):array;
    
    /**
     * Updates a specific field of a movie in the database.
     * 
     * @param int $movieId The ID of the movie to update.
     * @param string $field The field to update.
     * @param mixed $value The new value to assign to the field.
     * 
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public function updateMovieField($movieId, $field, $value); 
   
    /**
     * Deletes a movie and its associated data from the database.
     * 
     * @param int $movieId The ID of the movie to delete.
     * 
     * @return bool Returns true if the movie was deleted successfully, false otherwise.
     */
    public function deleteMovie($movieId):bool;
}