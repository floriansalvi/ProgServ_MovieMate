<?php

namespace ch;

use Exception;
use \PDO;

class RatingManager extends DbManager implements I_Rating {

    public function __construct(){
        parent::__construct();
    }

    public function isMovieRatedByUser($movieId, $userId):bool {
        $datas = [
            'movie_id' => (int)$movieId,
            'user_id' => (int)$userId
        ];
        $sql = "SELECT COUNT(*) FROM rating WHERE movie_id = :movie_id AND user_id = :user_id;";
        $stmt = $this->getDB()->prepare($sql);
        try {
            $stmt->execute($datas);
            return $stmt->fetchColumn()>0;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
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
        $rating = $this->getRatingDatasById($ratingId);
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
}