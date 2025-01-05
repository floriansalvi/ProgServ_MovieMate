<?php

namespace ch;

/**
 * Interface I_Rating provides a contract for ratings-related operations.
 */
interface I_Rating {

    /**
    * Return a boolean that specifies if a specific movie has already been rated by a specific user.
    * 
    * @param int $movieId The id of the movie
    * @param int $userId The id of the user
    *
    * @return bool Returns true if the movie is already rated by the user, false if the title is not already used
    */
    public function isMovieRatedByUser($movieId, $userId):bool;

    /**
    * Store a rating and its information in the database.
    * 
    * @param int $movieID The id of the movie associated with the rating. 
    * @param int $userId The id of the user associated with the rating. 
    * @param int $rate The value of the rating
    * @param string $comment The comment of the rating
    *
    * @return bool Returns true if the rating was stored, false if the rating could not stored.
    */
    public function saveRating($movieId, $userId, $rate, ?string $comment):bool;

    /**
    * Update a movie rating average in the database.
    * 
    * @param int $movieID The id of the movie. 
    *
    * @return bool Returns true if the movie rating average was updated, false otherwise.
    */
    public function updateRatingAvg($movieId):bool;

    /**
     * Retrieves a list of ratings and their information stored in the database.
     * 
     * @param string|null $idType The type of id on which in want to base our SQL query on. If null, no filtering is applied.
     * @param int|null $id The id on which in want to base our SQL query on. If null, no filtering is applied.
     * @param int|null $limit The number of ratings to retrieve. If null, no limit is applied.
     *
     * @return array Returns an array of ratings that match the specified parameters. If no rating match, returns an empty array.
     */
    public function getRatings(?string $idType, ?int $id, ?int $limit):array;

    /**
     * Retrieves detailed information about a specific rating from the database.
     * 
     * @param int $ratingId The ID of the rating to retrieve.
     * 
     * @return array Returns an associative array containing the rating data.
     *               If the rating is not found or an error occurs, returns an empty array.
     */
    public function getRatingDatasById($ratingId):array;

    /**
     * Retrieves the total count of ratings in the database.
     * 
     * @param string|null $idType The type of id on which in want to base our SQL query on. If null, no filtering is applied.
     * @param int|null $id The id on which in want to base our SQL query on. If null, no filtering is applied.
     * 
     * @return int Returns the total count of ratings. If an error occurs, returns 0.
     */
    public function getRatingsCount(?string $idType, ?int $id):int;

    /**
     * Deletes a rating and its associated data from the database.
     * 
     * @param int $ratingId The ID of the rating to delete.
     * 
     * @return bool Returns true if the rating was deleted successfully, false otherwise.
     */
    public function deleteRating($ratingId):bool;

    /**
     * Deletes all ratings related to a specific user from the database.
     * 
     * @param int $userId The ID of the user.
     * 
     * @return bool Returns true if the ratings were deleted successfully, false otherwise.
     */
    public function deleteUserRatings($userId):bool;

    /**
     * Deletes all ratings related to a specific movie from the database.
     * 
     * @param int $movieId The ID of the movie.
     * 
     * @return bool Returns true if the ratings were deleted successfully, false otherwise.
     */
    public function deleteMovieRatings($movieId):bool;
}