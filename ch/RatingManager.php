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

    public function saveRating($movieId, $userId, $rate, $comment):bool {
        $saved = false;
        $creationTime = date("Y-m-d H:i:s");
        if(!empty($movieId) && !empty($userId) && !empty($rate)){
            $datas = [
                'user_id' => $userId,
                'movie_id' => $movieId,
                'rate' => $rate,
                'comment' => $comment,
                'created_at' => $creationTime
            ];
            $sql = "INSERT INTO rating (user_id, movie_id, rate, comment, created_at) VALUES "."(:user_id, :movie_id, :rate, :comment, :created_at);";
            $stmt = $this->getDB()->prepare($sql);
            try {
                $stmt->execute($datas);
                $saved = $this->updateRatingAvg($movieId);
            } catch (PDOException $e) {
                error_log($e->getMessage());
            }
        }
        return $saved;
    }

    public function updateRatingAvg($movieId):bool {
        $sql = "UPDATE movie SET rating_avg = (SELECT AVG(rate) FROM rating WHERE movie_id = movie.id) WHERE id = :id;";
        
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $movieId, PDO::PARAM_INT);
        try {
            $stmt->execute();
            return true; 
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getRatings(?string $idType, ?int $id, ?int $limit):array {
        $datas = [];
        $validIdTypes = ['user', 'movie'];

        $columnName = null;

        if($idType !== null && in_array($idType, $validIdTypes)){
                $columnName = $idType . "_id";
        }

        $sql = "SELECT * FROM rating";

        if($columnName !== null && $id !== null){
            $sql .= " WHERE " . $columnName . " = :id ORDER BY created_at DESC";
            $datas['id'] = $id;
        }

        if($limit !== null){
            $sql .= " LIMIT :limit";
            $datas['limit'] = $limit;
        }

        $stmt = $this->getDB()->prepare($sql);
        
        try {
            $stmt->execute($datas);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return[];
        }
    }

    public function getRatingDatasById($ratingId):array {
        $sql = "SELECT * FROM rating WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $ratingId, PDO::PARAM_INT);
        try {
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC)?: []; 
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $result = [];
        } 
        return $result;
    }

    public function getRatingsCount(?string $idType, ?int $id):int {
        $datas = [];
        $validIdTypes = ['user', 'movie'];

        $columnName = null;

        if($idType !== null && in_array($idType, $validIdTypes)){
            $columnName = $idType . "_id";
        }

        $sql = "SELECT COUNT(*) as total FROM rating";

        if($columnName !== null && $id !== null){
            $sql .= " WHERE " . $columnName . " = :id";
            $datas['id'] = $id;
        }

        $stmt = $this->getDB()->prepare($sql);
        
        try {
            $stmt->execute($datas);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function deleteRating($ratingId):bool {
        $rating = $this->getRatingDatasById(ratingId: $ratingId);
        $sql = "DELETE FROM rating WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $ratingId, \PDO::PARAM_INT);
        try {
            $stmt->execute();
            $this->updateRatingAvg($rating['movie_id']);
            return true; 
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        } 
    }

    public function deleteUserRatings($userId):bool {
        $sqlSelectDistinct = "SELECT DISTINCT movie_id FROM rating WHERE user_id = :user_id;";
        $stmtSelectDistinct = $this->getDB()->prepare($sqlSelectDistinct);
        $stmtSelectDistinct->bindParam('user_id', $userId, PDO::PARAM_INT);
        try {
            $stmtSelectDistinct->execute();
            $movies = $stmtSelectDistinct->fetchAll(PDO::FETCH_ASSOC);
            $movieIds = array_map(function($movie) {
                return $movie['movie_id'];
            }, $movies);
        } catch (\PDOException $e){
            error_log($e->getMessage());
        }
        
        $sqlDelete = "DELETE FROM rating WHERE user_id = :user_id;";
        $stmtDelete = $this->getDB()->prepare($sqlDelete);
        $stmtDelete->bindParam('user_id', $userId, PDO::PARAM_INT);
        try {
            $stmtDelete->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
        } 

        if(!empty($movies)){
            $moviesIdsList = implode(', ', array_fill(0, count($movieIds), '?'));
            $sqlUpdate = "UPDATE movie SET rating_avg = (SELECT AVG(rate) FROM rating WHERE movie_id = movie.id) WHERE id IN ($moviesIdsList);";
            $stmtUpdate = $this->getDB()->prepare($sqlUpdate);
            try {
                $stmtUpdate->execute($movieIds);
                return true;
            } catch (\PDOException $e){
                error_log($e->getMessage());
                return false;
            }
        } else {
            return true;
        }
    }

    public function deleteMovieRatings($movieId):bool {
        $sql = "DELETE FROM rating WHERE movie_id = :movie_id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('movie_id', $movieId, PDO::PARAM_INT);
        try {
            $stmt->execute();
            return true; 
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        } 
    }

}
