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


}