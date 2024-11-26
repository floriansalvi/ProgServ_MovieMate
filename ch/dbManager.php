<?php

namespace ch;

class DbManager {
    private $db;

    // Constructeur de la base de données.
    public function __construct() {
        $config = parse_ini_file('config' . DIRECTORY_SEPARATOR . 'db.ini', true);
        $dsn = $config['dsn'];
        $username = $config['username'];
        $password = $config['password'];
        $this->db = new \PDO($dsn, $username, $password);
        if(!$this->db){

        }else{
            die('La connexion à la base de données n\'a pas pu être établie.')
        }
            $this->createTables_SQLITE();
    }

    //Creation des différentes tables de la DB
    public function createTables_SQLITE():bool {
        $sql = <<<COMMAND_SQL
            CREATE TABLE IF NOT EXISTS user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                pseudonym VARCHAR(32) NOT NULL UNIQUE,
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
                genre_id INTEGER REFERENCES genre(id),
                cover_path VARCHAR(64) NOT NULL
            );

            CREATE TABLE IF NOT EXISTS rating (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER REFERENCES user(id),
                movie_id INTEGER REFERENCES movie(id),
                rate INTEGER NOT NULL,
                comment TEXT,
                created_at DATETIME NOT NULL
            )
        COMMAND_SQL;
        try {
            $this->db->exec($sql);
            $created = true;
        } catch (\PDOException $e) {
            $e->getMessage();
            $created = false;
        }
        return $created;
    }
}