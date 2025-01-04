<?php

namespace ch;

use Exception;
use \PDO;

/**
 * GenreManager class handles operations related to genres in the database.
 * It extends the DbManager class to inherit the database connection functionality.
 */
class GenreManager extends DbManager {

    /**
     * Class constructor
     * 
     * Initializes the GenreManager by calling the parent constructor.
     * The parent constructor establishes the database connection by invoking the DbManager's __construct() method.
     * This allows GenreManager to access the database connection and perform operations related to genres.
     */
    public function __construct(){
        parent::__construct();
    }

    /**
    * Retrieves information about all genres stored in the database and returns them.
    * 
    * @return array An array of genre retrieved from the database or an empty array if an error occurs.
    */
    public function getAllGenres():array {
        $sql = "SELECT id, title, description FROM genre;";
        $stmt = $this->getDB()->prepare($sql);
        try {
            // Attempt to execute the SQL query to retrieves the genre datas.
            $stmt->execute();
            // If the SQL query is successfuly executed, returns an array containing the genres datas.
            $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $genres;
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns an empty array.
            return [];
        }
    }

    /**
    * Retrieves information about a specific genre stored in the database and return them.
    * 
    * @param $id The id of the genre we want to retrieves information of
    * @return array An array containing the genre's information or an empty array if an error occurs
    */
    public function getGenreDatas($id):array {
        $sql = "SELECT id, title, description FROM genre WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        try {
            // Attempt to execute the SQL query to retrieves the genre datas.
            $stmt->execute();
            // If the SQL query is successfuly executed, returns an array containing the genre datas.
            $genres = $stmt->fetch(PDO::FETCH_ASSOC);
            return $genres;
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns an empty array.
            return [];
        }
    }
}