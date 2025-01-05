<?php

namespace ch;

use Exception;
use \PDO;

/**
 * MovieManager class handles operations related to movies in the database.
 * It extends the DbManager class to inherit the database connection functionality.
 */
class MovieManager extends DbManager implements I_Movie {

    /**
     * Class constructor
     * 
     * Initializes the MovieManager by calling the parent constructor.
     * The parent constructor establishes the database connection by invoking the DbManager's __construct() method.
     * This allows MovieManager to access the database connection and perform operations related to movies.
     */
    public function __construct(){
        parent::__construct();
    }

    /**
    * Return a boolean that tell the user if the movie title is already used or not
    * 
    * @param string $title The title of the movie.
    * @return bool Returns true if the title is already used, false if the title is not already used
    */
    public function isTitleUsed($title):bool {
        
        // SQL query to insert the movie information into the database
        $sql = "SELECT COUNT(*) FROM movie WHERE title = :title;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('title', $title, PDO::PARAM_STR);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // If the SQL query is successfuly executed, returns an boolean specifying if the title is already used or not.
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns true.
            return true;
        }
    }

    /**
    * Store a movie and its information in the database.
    * 
    * @param string $title The title of the movie.
    * @param string $description The description of the movie.
    * @param string $realisator The realisator of the movie.
    * @param string $release_date The release date of the movie. Format = YYYY-MM-DD
    * @param int $duration The duration of the movie.
    * @param int $genreId The id of the genre associated with the movie.
    * @param int $coverName The name of the movie's cover image.
    *
    * @return bool Returns true if the movie was stored, false if the movies could not stored.
    */
    public function saveMovie($title, $description, $realisator, $releaseDate, $duration, $genreId, $coverName):bool {
        $saved = false;

        // Get the current date for the add_date field
        $addDate = date("Y-m-d");

        // Check if all fields are non-empty
        if(!empty($title) && !empty($description) && !empty($realisator) && !empty($releaseDate) && !empty($duration) && !empty($genreId) && !empty($coverName)){
            // Prepare data for insertion
            $datas = [
                'title' => $title,
                'description' => $description,
                'realisator' => $realisator,
                'release_date' => $releaseDate,
                'duration' => $duration,
                'add_date' => $addDate,
                'genre_id' => $genreId,
                'cover_name' => $coverName
            ];

            // SQL query to insert the movie information into the database
            $sql = "INSERT INTO movie (title, description, realisator, release_date, duration, add_date, genre_id, cover_name) VALUES "."(:title, :description, :realisator, :release_date, :duration, :add_date, :genre_id, :cover_name);";
            $stmt = $this->getDB()->prepare($sql);
            try {
                // Attempt to execute the SQL query.
                $stmt->execute($datas);
                // If the SQL query is successfuly executed, returns true.
                $saved = true;
            } catch (PDOException $e) {
                // If an execption is thrown, log the error message.
                error_log($e->getMessage());
                // If the SQL query could not be executed, returns false.
                $saved = false;
            }
        }
        // Return whether the movie was saved successfully
        return $saved;
    }

    /**
     * Retrieves a list of movies and their information stored in the database.
     * 
     * @param string|null $sortColumn The column by which to sort the movies. 
     *                                If null, no sorting is applied.
     * @param string|null $sortOrder The order to sort the movies by. Can be 'ASC' or 'DESC'. 
     *                               If null, the default sorting order is applied.
     * @param int|null $genreId The ID of the genre to filter the movies by. If null, no filtering is applied.
     * @param int|null $limit The number of movies to retrieve. If null, no limit is applied.
     * @param int|null $offset The offset from which to start retrieving movies. If null, no offset is applied.
     *
     * @return array Returns an array of movies that match the specified parameters. If no movies match, returns an empty array.
     */
    public function getMovies(?string $sortColumn, ?string $sortOrder, ?int $genreId, ?int $limit, ?int $offset):array {
        
        // Initialize an empty array to hold the data for the query parameters.
        $datas = [];

        // Define the initial SQL query to select all columns from the movie table.
        $sql = "SELECT * FROM movie";

        // Define an array of valid columns for sorting.
        $validColumns = ['title', 'realisator', 'release_date', 'duration', 'add_date', 'genre_id', 'rating_avg'];

        // Define an array of valid sorting orders
        $validOrders = ['ASC', 'DESC'];

        // If the provided sort column is valid, use it; otherwise, default to 'add_date'.
        $sortColumn = in_array($sortColumn, $validColumns) ? $sortColumn : 'add_date';

        // If the provided sort order is valid, use it; otherwise, default to 'DESC'.
        $sortOrder = in_array($sortOrder, $validOrders) ? $sortOrder : 'DESC';

        // If a genre ID is provided, filter the movies by genre.
        if(!is_null($genreId)){
            $sql .= " WHERE genre_id = :genre_id";
            $datas['genre_id'] = $genreId;
        }

        // Append the ORDER BY clause to sort the results by the selected column and order.
        $sql .= " ORDER BY $sortColumn $sortOrder";

        // If a limit is provided, restrict the number of movies returned.
        if(!is_null($limit)){
            $sql .= " LIMIT :limit";
            $datas['limit'] = $limit;
        }

        // If an offset is provided, set the starting point for the results.
        if(!is_null($offset)){
            $sql .= " OFFSET :offset";
            $datas['offset'] = $offset;
        }
        
        // Prepare the SQL query for execution.
        $stmt = $this->getDB()->prepare($sql);

        try {
            // Attempt to execute the SQL query.
            $stmt->execute($datas);
            // If the SQL query is successfuly executed, returns the list of movies.
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $movies;
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns an empty array.
            return[];
        }
    }

    /**
     * Retrieves the total count of movies in the database.
     * 
     * @param int|null $genreId The ID of the genre to filter the movie count by. If null, counts all movies.
     * 
     * @return int Returns the total count of movies. If an error occurs, returns 0.
     */
    public function getMoviesCount(?int $genreId): int {
        
        // Initialize an empty array to hold the data for the query parameters.
        $datas = [];

        // Define the initial SQL query to count all movies.
        $sql = "SELECT COUNT(*) as total FROM movie";

        // If a genre ID is provided, filter the count by genre.
        if (!is_null($genreId)) {
            $sql .= " WHERE genre_id = :genre_id;";
            $datas['genre_id'] = $genreId;
        }
        
        // Prepare the SQL query for execution.
        $stmt = $this->getDB()->prepare($sql);

        try {
            // Attempt to execute the SQL query.
            $stmt->execute($datas);
            // Retrieve the count from the query result.
            $moviesAmount = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            // Return the total count of movies.
            return $moviesAmount;
        } catch (PDOException $e) {
            // If an exception occurs, log the error and return 0.
            error_log($e->getMessage());
            return 0;
        }
    }

    /**
     * Retrieves detailed information about a specific movie from the database.
     * 
     * @param int $movieId The ID of the movie to retrieve.
     * 
     * @return array Returns an associative array containing the movie data.
     *               If the movie is not found or an error occurs, returns an empty array.
     */
    public function getMovieDatas($movieId): array {
        
        // Define the SQL query to retrieve a movie by its ID.
        $sql = "SELECT * FROM movie WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam(':id', $movieId, \PDO::PARAM_INT);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // Fetch the movie data as an associative array.
            $movie = $stmt->fetch(PDO::FETCH_ASSOC);
            // Return the movie data if found; otherwise, return an empty array.
            return $movie ? $movie : [];
        } catch (PDOException $e) {
            // If an exception occurs, log the error and return an empty array.
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Updates a specific field of a movie in the database.
     * 
     * @param int $movieId The ID of the movie to update.
     * @param string $field The field to update.
     * @param mixed $value The new value to assign to the field.
     * 
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public function updateMovieField($movieId, $field, $value): bool {
        // Define a list of valid fields that can be updated.
        $validFields = ['title', 'description', 'realisator', 'release_date', 'duration', 'genre_id', 'cover_name'];

        // Check if the field to be updated is valid.
        if (in_array($field, $validFields)) {
            // Prepare data for the query.
            $datas = [
                'id' => $movieId,
                $field => $value
            ];
            // Define the SQL query to update the specified field of the movie.
            $sql = "UPDATE movie SET $field = :$field WHERE id = :id;";
            $stmt = $this->getDB()->prepare($sql);
            try {
                // Attempt to execute the SQL query.
                $stmt->execute($datas);
                // Check if any rows were affected by the update.
                if ($stmt->rowCount() > 0) {
                    return true;
                } else {
                    return false;
                }
            } catch (\PDOException $e) {
                // If an exception occurs, log the error and return false.
                error_log($e->getMessage());
                return false;
            }
        } else {
            // If the field is not valid, return false.
            return false;
        }
    }

    /**
     * Deletes a movie and its associated data from the database.
     * 
     * @param int $movieId The ID of the movie to delete.
     * 
     * @return bool Returns true if the movie was deleted successfully, false otherwise.
     */
    public function deleteMovie($movieId): bool {
        // Retrieve the movie data to access the cover image file name.
        $movie = $this->getMovieDatas($movieId);

        // Define the SQL query to delete the movie by its ID.
        $sql = "DELETE FROM movie WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $movieId, PDO::PARAM_INT);

        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // Build the file path for the cover image.
            $coverPath = $_SERVER['DOCUMENT_ROOT'] . "/ProgServ_MovieMate/assets/img/movie_cover/" . $movie['cover_name'];
            // If the cover image file exists, delete it.
            if (file_exists($coverPath)) {
                unlink($coverPath);
            }
            // Return true if the movie was deleted successfully.
            return true;
        } catch (PDOException $e) {
            // If an exception occurs, log the error and return false.
            error_log($e->getMessage());
            return false;
        }
    }
}
