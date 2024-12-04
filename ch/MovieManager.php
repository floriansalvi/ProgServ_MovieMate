<?php

namespace ch;

use Exception;
use \PDO;

class MovieManager extends DbManager implements I_Movie {

    public function __construct(){
        parent::__construct();
    }

    public function isTitleUsed($title):bool {
        $sql = "SELECT COUNT(*) FROM movie WHERE title = :title;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('title', $title, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        return $stmt->fetchColumn() > 0;
    }

    public function saveMovie($title, $description, $realisator, $releaseDate, $duration, $genreId, $coverName):bool {
        $saved = false;

        if(!empty($title) && !empty($description) && !empty($realisator) && !empty($releaseDate) && !empty($duration) && !empty($genreId) && !empty($coverName)){
            $datas = [
                'title' => $title,
                'description' => $description,
                'realisator' => $realisator,
                'release_date' => $releaseDate,
                'duration' => $duration,
                'genre_id' => $genreId,
                'cover_name' => $coverName
            ];
            $sql = "INSERT INTO movie (title, description, realisator, release_date, duration, genre_id, cover_name) VALUES "."(:title, :description, :realisator, :release_date, :duration, :genre_id, :cover_name);";
            $stmt = $this->getDB()->prepare($sql);
            try {
                $stmt->execute($datas);
            } catch (PDOException $e) {
                error_log($e->getMessage());
            }
            $saved = true;
        }
        return $saved;
    }

    public function getMovies(?string $sortColumn, ?string $sortOrder, ?int $genreId, ?int $limit, ?int $offset):array {
        
        $datas = [];

        $sql = "SELECT * FROM movie";

        $validColumns = ['title', 'realisator', 'release_date', 'duration', 'add_date', 'genre_id'];
        $validOrders = ['ASC', 'DESC'];
        $sortColumn = in_array($sortColumn, $validColumns) ? $sortColumn : 'add_date';
        $sortOrder = in_array($sortOrder, $validOrders) ? $sortOrder : 'DESC';

        if(!is_null($genreId)){
            $sql .= " WHERE genre_id = :genre_id";
            $datas['genre_id'] = $genreId;
        }
        
        $sql .= " ORDER BY $sortColumn $sortOrder";

        if(!is_null($limit)){
            $sql .= " LIMIT :limit";
            $datas['limit'] = $limit;
        }

        if(!is_null($offset)){
            $sql .= " OFFSET :offset";
            $datas['offset'] = $offset;
        }
        
        $stmt = $this->getDB()->prepare($sql);

        if (!is_null($limit)) {
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        }

        if (!is_null($offset)) {
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        }

        try {
            $stmt->execute($datas);
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return[];
        }
        return $movies;
    }

    public function getMovieDatas($movieId):array {
        $sql = "SELECT * FROM movie WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam(':id', $movieId, \PDO::PARAM_INT);
        try {
            $stmt->execute();
            $movie = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $movie ? $movie : [];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }  
    }

    public function updateMovieField($movieId, $field, $value):bool {
        $validFields = ['title', 'description', 'realisator', 'release_date', 'duration', 'genre_id', 'cover_name'];
        if(in_array($field, $validFields)){
            $datas = [
                'id' => $movieId,
                $field => $value
            ];
            $sql = "UPDATE movie SET $field = :$field WHERE id = :id;";
            $stmt = $this->getDB()->prepare($sql);
            try {
                $stmt->execute($datas);
                if($stmt->rowCount()>0){
                    return true;
                }else{
                    return false;
                }
            } catch (\PDOException $e){
                error_log($e->getMessage());
                return false;
            }
        }else{
            return false;
        } 
    }

    public function deleteMovie($movieId):bool {
        $sql = "DELETE FROM movie WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $movieId, \PDO::PARAM_INT);
        try {
            $stmt->execute();
            return true; 
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        } 
    }

}
