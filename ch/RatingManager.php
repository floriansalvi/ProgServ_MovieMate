<?php

namespace ch;

use Exception;
use \PDO;

/**
 * RatingManager class handles operations related to movies in the database.
 * It extends the DbManager class to inherit the database connection functionality.
 */
class RatingManager extends DbManager implements I_Rating {

    /**
     * Class constructor
     * 
     * Initializes the RatingManager by calling the parent constructor.
     * The parent constructor establishes the database connection by invoking the DbManager's __construct() method.
     * This allows RatingManager to access the database connection and perform operations related to movies.
     */
    public function __construct(){
        parent::__construct();
    }

    /**
    * Return a boolean that specifies if a specific movie has already been rated by a specific user.
    * 
    * @param int $movieId The id of the movie
    * @param int $userId The id of the user
    *
    * @return bool Returns true if the movie is already rated by the user, false if the title is not already used
    */
    public function isMovieRatedByUser($movieId, $userId):bool {
        
        // Prepare data for insertion
        $datas = [
            'movie_id' => (int)$movieId,
            'user_id' => (int)$userId
        ];

        // 
        $sql = "SELECT COUNT(*) FROM rating WHERE movie_id = :movie_id AND user_id = :user_id;";
        $stmt = $this->getDB()->prepare($sql);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute($datas);
            // If the SQL query is successfuly executed, returns an boolean specifying if the movie is already rated by the user or not.
            return $stmt->fetchColumn()>0;
        } catch (\PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns true.
            return true;
        }
    }

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
    public function saveRating($movieId, $userId, $rate, $comment):bool {
        $saved = false;
        
        // Get the current date for the created_at field
        $creationTime = date("Y-m-d H:i:s");
        
        // Check if all fields are non-empty
        if(!empty($movieId) && !empty($userId) && !empty($rate)){
            // Prepare data for insertion
            $datas = [
                'user_id' => $userId,
                'movie_id' => $movieId,
                'rate' => $rate,
                'comment' => $comment,
                'created_at' => $creationTime
            ];

            // SQL query to insert the rating information into the database
            $sql = "INSERT INTO rating (user_id, movie_id, rate, comment, created_at) VALUES "."(:user_id, :movie_id, :rate, :comment, :created_at);";
            $stmt = $this->getDB()->prepare($sql);
            try {
                // Attempt to execute the SQL query.
                $stmt->execute($datas);
                // If the SQL query is successfuly executed, update the associated movie rating averag and return true.
                $saved = $this->updateRatingAvg($movieId);
            } catch (PDOException $e) {
                // If an execption is thrown, log the error message and return false.
                error_log($e->getMessage());
            }
        }
        return $saved;
    }

    /**
    * Update a movie rating average in the database.
    * 
    * @param int $movieID The id of the movie. 
    *
    * @return bool Returns true if the movie rating average was updated, false otherwise.
    */
    public function updateRatingAvg($movieId):bool {
        
        // SQL query to update the movie average in the database
        $sql = "UPDATE movie SET rating_avg = (SELECT AVG(rate) FROM rating WHERE movie_id = movie.id) WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $movieId, PDO::PARAM_INT);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // If the SQL query is successfuly executed, returns true.
            return true; 
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns false.
            return false;
        }
    }

    /**
     * Retrieves a list of ratings and their information stored in the database.
     * 
     * @param string|null $idType The type of id on which in want to base our SQL query on. If null, no filtering is applied.
     * @param int|null $id The id on which in want to base our SQL query on. If null, no filtering is applied.
     * @param int|null $limit The number of ratings to retrieve. If null, no limit is applied.
     *
     * @return array Returns an array of ratings that match the specified parameters. If no rating match, returns an empty array.
     */
    public function getRatings(?string $idType, ?int $id, ?int $limit):array {
        
        // Initialize an empty array to hold the data for the query parameters.
        $datas = [];
        
        // Define an array of valid id type for sorting.
        $validIdTypes = ['user', 'movie'];

        $columnName = null;

        if($idType !== null && in_array($idType, $validIdTypes)){
                $columnName = $idType . "_id";
        }

        // Define the initial SQL query to select all columns from the rating table.
        $sql = "SELECT * FROM rating";

        // If an id type and an id are provided, filter the rating by associated id.
        if($columnName !== null && $id !== null){
            $sql .= " WHERE " . $columnName . " = :id ORDER BY created_at DESC";
            $datas['id'] = $id;
        }

        // If a limit is provided, restrict the number of ratings returned.
        if($limit !== null){
            $sql .= " LIMIT :limit";
            $datas['limit'] = $limit;
        }

        // Prepare the SQL query for execution.
        $stmt = $this->getDB()->prepare($sql);
        
        try {
            // Attempt to execute the SQL query.
            $stmt->execute($datas);
            // If the SQL query is successfuly executed, returns the list of ratings.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns an empty array.
            return[];
        }
    }

    /**
     * Retrieves detailed information about a specific rating from the database.
     * 
     * @param int $ratingId The ID of the rating to retrieve.
     * 
     * @return array Returns an associative array containing the rating data.
     *               If the rating is not found or an error occurs, returns an empty array.
     */
    public function getRatingDatasById($ratingId):array {

        // Define the SQL query to retrieve a rating by its ID.
        $sql = "SELECT * FROM rating WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $ratingId, PDO::PARAM_INT);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // Fetch the rating data as an associative array.
            $result = $stmt->fetch(PDO::FETCH_ASSOC)?: [];
            // Return the rating data if found; otherwise, return an empty array.
            return $result ? $result : [];
        } catch (PDOException $e) {
            // If an exception occurs, log the error and return an empty array.
            error_log($e->getMessage());
            return [];
        } 
    }

    /**
     * Retrieves the total count of ratings in the database.
     * 
     * @param string|null $idType The type of id on which in want to base our SQL query on. If null, no filtering is applied.
     * @param int|null $id The id on which in want to base our SQL query on. If null, no filtering is applied.
     * 
     * @return int Returns the total count of ratings. If an error occurs, returns 0.
     */
    public function getRatingsCount(?string $idType, ?int $id):int {
       
        // Initialize an empty array to hold the data for the query parameters.       
        $datas = [];
        $validIdTypes = ['user', 'movie'];

        $columnName = null;

        // If a valid id type is provided, filter the count by the id type.
        if($idType !== null && in_array($idType, $validIdTypes)){
            $columnName = $idType . "_id";
        }

        // Define the initial SQL query to count all ratings.
        $sql = "SELECT COUNT(*) as total FROM rating";

        // If a valid id type and an id are provided, filter the count.
        if($columnName !== null && $id !== null){
            $sql .= " WHERE " . $columnName . " = :id";
            $datas['id'] = $id;
        }

        // Prepare the SQL query for execution.
        $stmt = $this->getDB()->prepare($sql);
        
        try {
            // Attempt to execute the SQL query.
            $stmt->execute($datas);
            // Return the total count of ratings.
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            // If an exception occurs, log the error and return 0.
            error_log($e->getMessage());
            return 0;
        }
    }

    /**
     * Deletes a rating and its associated data from the database.
     * 
     * @param int $ratingId The ID of the rating to delete.
     * 
     * @return bool Returns true if the rating was deleted successfully, false otherwise.
     */
    public function deleteRating($ratingId):bool {

        // Retrieve the rating data to access the associated movie_id.
        $rating = $this->getRatingDatasById(ratingId: $ratingId);
        
        // Define the SQL query to delete the rating by its ID.
        $sql = "DELETE FROM rating WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $ratingId, \PDO::PARAM_INT);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // If the query is successfuly executed, update the associated movie rating average and returns true.
            $this->updateRatingAvg($rating['movie_id']);
            return true; 
        } catch (PDOException $e) {
            // If an exception occurs, log the error and return false.
            error_log($e->getMessage());
            return false;
        } 
    }

    /**
     * Deletes all ratings related to a specific user from the database.
     * 
     * @param int $userId The ID of the user.
     * 
     * @return bool Returns true if the ratings were deleted successfully, false otherwise.
     */
    public function deleteUserRatings($userId):bool {
        
        // SQL query to retrieve the ratings data to access the associated movie_id.
        $sqlSelectDistinct = "SELECT DISTINCT movie_id FROM rating WHERE user_id = :user_id;";
        $stmtSelectDistinct = $this->getDB()->prepare($sqlSelectDistinct);
        $stmtSelectDistinct->bindParam('user_id', $userId, PDO::PARAM_INT);
        try {
            // Attempt to execute the SQL query.
            $stmtSelectDistinct->execute();
            // Retrieve the movies from the query result.
            $movies = $stmtSelectDistinct->fetchAll(PDO::FETCH_ASSOC);
            // Retrieve the movies id and put them in a new array.
            $movieIds = array_map(function($movie) {
                return $movie['movie_id'];
            }, $movies);
        } catch (\PDOException $e){
            // If an exception occurs, log the error.
            error_log($e->getMessage());
        }
        
        // Define the SQL query to delete the ratings by their user_id column value.
        $sqlDelete = "DELETE FROM rating WHERE user_id = :user_id;";
        $stmtDelete = $this->getDB()->prepare($sqlDelete);
        $stmtDelete->bindParam('user_id', $userId, PDO::PARAM_INT);
        try {
            // Attempt to execute the SQL query.
            $stmtDelete->execute();
        } catch (PDOException $e) {
            // If an exception occurs, log the error.
            error_log($e->getMessage());
        } 

        // If at least movie or more were retrieve, update their rating average.
        if(!empty($movies)){
            $moviesIdsList = implode(', ', array_fill(0, count($movieIds), '?')); // implode the movies id array and turn it into a string.

            // Define the SQL query to update the movies rating average.
            $sqlUpdate = "UPDATE movie SET rating_avg = (SELECT AVG(rate) FROM rating WHERE movie_id = movie.id) WHERE id IN ($moviesIdsList);";
            $stmtUpdate = $this->getDB()->prepare($sqlUpdate);
            try {
                // Attempt to execute the SQL query.
                $stmtUpdate->execute($movieIds);
                // Return true if the movie rating average was deleted updated.
                return true;
            } catch (\PDOException $e){
                // If an exception occurs, log the error and return false.
                error_log($e->getMessage());
                return false;
            }
        } else {
            // If the ratings were deleted and no movies were related to them, return true.
            return true;
        }
    }

    /**
     * Deletes all ratings related to a specific movie from the database.
     * 
     * @param int $movieId The ID of the movie.
     * 
     * @return bool Returns true if the ratings were deleted successfully, false otherwise.
     */
    public function deleteMovieRatings($movieId):bool {
        
        // Define the SQL query to delete the ratings by their movie_id column.
        $sql = "DELETE FROM rating WHERE movie_id = :movie_id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('movie_id', $movieId, PDO::PARAM_INT);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // Return true if the ratings were deleted successfully.
            return true; 
        } catch (PDOException $e) {
            // If an exception occurs, log the error and return false.
            error_log($e->getMessage());
            return false;
        } 
    }

}
