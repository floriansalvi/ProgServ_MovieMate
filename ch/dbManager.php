<?php

namespace ch;

use \PDO;

/**
 * Class representing the connection to the database
 * 
 * This class handles database connection and ensures that the mandatory tables are created if they do not exists yet.
 */
class DbManager {
    private $db;

    /**
     * Class constructor
     * 
     * Retrieves database connection details from the db.ini and initiates the connection to the database using PDO
     * If the connection is successful, it triggers the tables creation
     */
    public function __construct() {
        $config = parse_ini_file('config' . DIRECTORY_SEPARATOR . 'db.ini', true);
        $dsn = $config['dsn'];
        $username = $config['username'];
        $password = $config['password'];
        $this->db = new PDO($dsn, $username, $password);
        if(!$this->db){
            // Die if the connection could not be established
            die('La connexion à la base de données n\'a pas pu être établie.');
        }else{
            $this->createTables_SQLITE();
        };
    }

    /**
    * Create the different tables if they do not exist.
    * 
    * @return bool Returns true if tables are successfully created or exist, false if there was an error.
    */
    public function createTables_SQLITE():bool {
        $sql = <<<COMMAND_SQL
            CREATE TABLE IF NOT EXISTS user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(32) NOT NULL UNIQUE,
                firstname VARCHAR(32) NOT NULL,
                lastname VARCHAR(32) NOT NULL,
                email VARCHAR(48) NOT NULL UNIQUE,
                password VARCHAR(32) NOT NULL,
                created_at DATETIME NOT NULL,
                activated BOOLEAN,
                cover INTEGER,
                role VARCHAR(16)
            );

            CREATE TABLE IF NOT EXISTS token (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER REFERENCES user(id),
                content VARCHAR(32) NOT NULL UNIQUE
            );

            CREATE TABLE IF NOT EXISTS genre (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(32) NOT NULL UNIQUE,
                description TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS movie (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(64) NOT NULL UNIQUE,
                description TEXT NOT NULL UNIQUE,
                realisator VARCHAR(32) NOT NULL,
                release_date DATE NOT NULL,
                duration INTEGER NOT NULL,
                add_date DATE NOT NULL,
                genre_id INTEGER REFERENCES genre(id),
                cover_name VARCHAR(32) NOT NULL,
                rrating_avg FLOAT 
            );

            CREATE TABLE IF NOT EXISTS rating (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER REFERENCES user(id),
                movie_id INTEGER REFERENCES movie(id),
                rate INTEGER NOT NULL CHECK (rate IN (1 ,2, 3, 4, 5)),
                comment TEXT,
                created_at DATETIME NOT NULL
            )
        COMMAND_SQL;
        try {
            // Attempt to execute the SQL query to create the tables.
            $this->db->exec($sql);
            // If the query is successfuly executed, $create = true.
            $created = true;
        } catch (\PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the query is not executed, $create = false.
            $created = false;
        }
        return $created;
    }

    /**
    * Return the database connection
    * 
    * @return PDO The database connection
    */
    public function getDB():PDO{
        return $this->db;
    }
}