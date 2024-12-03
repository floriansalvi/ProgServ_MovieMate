<?php

namespace ch;

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
            $e->getMessage();
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
                $e->getMessage();
            }
            $saved = true;
        }
        return $saved;
    }





}