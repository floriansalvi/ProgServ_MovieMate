<?php

namespace ch;

use Exception;
use \PDO;

class GenreManager extends DbManager {

    public function __construct(){
        parent::__construct();
    }

    public function getAllGenres():array {
        $sql = "SELECT id, title, description FROM genre;";
        $stmt = $this->getDB()->prepare($sql);
        try {
            $stmt->execute();
            $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $genres;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getGenreDatas($id):array {
        $sql = "SELECT id, title, description FROM genre WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
            $genres = $stmt->fetch(PDO::FETCH_ASSOC);
            return $genres;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}