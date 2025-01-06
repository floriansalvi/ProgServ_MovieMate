<?php

use ch\UserManager;
use ch\MovieManager;
use ch\RatingManager;
use ch\GenreManager;

require_once './config/autoload.php';
require_once './config/base_url.php';
require_once 'movieUploadValidation.php';

// Unset session variables related to movie, user, and rating deletion.
unset($_SESSION['movie_to_delete']);
unset($_SESSION['user_to_delete']);
unset($_SESSION['rating_to_delete']);

//Initialize the different database managers.
$dbMovie = new MovieManager();
$dbUser = new UserManager();
$dbRating = new RatingManager();
$dbGenre = new GenreManager();

// Retrieve category and movies limit from GET request, default to 0 and 10 if null.
$category = $_GET['cat'] ?? 0;
$moviesLimit = $_GET['movies'] ?? 10;

// Validate the 'cat' GET parameter.
if(isset($_GET['cat'])){
    if(empty($_GET['cat']) || $category === 0){
         // Redirect to admin page if 'cat' is invalid or zero.
        header("Location: " . BASE_URL . "admin.php");
        exit();
    }else if(!filter_var($_GET['cat'], FILTER_VALIDATE_INT) || $_GET['cat'] < 0 || $_GET['cat'] > 2){
        // Throw an exception if 'cat' is not a valid integer or out of range.
        throw new Exception('Cette page n\'existe pas.');
    }else{
        $category = (int)$_GET['cat'];
    }
}

$content = ""; // Initialize content variable for output.
$itemsPerLoad = 10; //Set number of items per load.

// Switch statement to handle the different admin categories.
switch ($category) {
    case 0 :
        // Get total number of movies if category is 0.
        $dbMovie = new MovieManager();
        $itemsAmount = $dbMovie->getMoviesCount(null);
        break;
    case 1 :
        // Get total number of users if category is 1.
        $dbUser = new UserManager();
        $itemsAmount = $dbUser->getUsersCount();
        break;
    case 2 :
        // Get total number of ratings if category is 2.
        $dbRating = new RatingManager();
        $itemsAmount = $dbRating->getRatingsCount(null, null);
        break;
};

// Calculate the maximum number of loads required based on items amount and items per load.
if($itemsAmount > 0){
    $maxLoads = ceil($itemsAmount / $itemsPerLoad);
}else{
    $maxLoads = 1;
}

// Retrieve the 'ia' parameter from GET request, default to 1. ia = items affichés
$itemsLoad = $_GET['ia'] ?? 1;

// Validate the 'ia' parameter.
if(!filter_var($itemsLoad, FILTER_VALIDATE_INT)){
    throw new Exception('Cette page n\'existe pas.');
};

// Redirect to admin page with the defined category if 'ia' equals '1'.
if($itemsLoad === '1'){
    header("Location: " . BASE_URL . "admin.php" . "?cat=" . $category);
    exit();
};

$itemsLoad = (int)$itemsLoad;
if($itemsLoad <= 0 || $itemsLoad > $maxLoads){
    // Throw an exception if 'ia' is out of range.
    throw new Exception('Cette page n\'existe pas.');
};

// Calculate the total number of items shown.
$itemsShown = $itemsPerLoad * $itemsLoad;

// Handle movie deletion based on POST request.
if(filter_has_var(INPUT_POST, var_name: 'delete-movie')) {
    $movieId = (int)filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);

    $movie = $dbMovie->getMovieDatas($movieId);
    
    if(empty($movie)){
        // Throw an exception if movie not found.
        throw new Exception("No corresponding movie could be found.");
    }else if($_SESSION['user']['role'] === 'admin'){
        // Set session variable for movie deletion if user is admin.
        $_SESSION['movie_to_delete'] = $movieId;
    }else{
        throw new Exception("No corresponding movie could be found.");
    }
}

// Handle movie confirmation for deletion based on POST request.
if(filter_has_var(INPUT_POST, 'movie-confirmation')) {
    $movieId = (int)filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);

    $confirmation = (int)filter_input(INPUT_POST, 'movie-confirmation', FILTER_VALIDATE_INT);
    
    if($confirmation !== 1){
        // Unset session variable if confirmation is different from 1.
        unset($_SESSION['movie_to_delete']);
    } else {
        $movie = $dbMovie->getMovieDatas($movieId);
        if(empty($movie)){
            unset($_SESSION['movie_to_delete']);
            throw new Exception("No corresponding movie could be found.");
        }else if($_SESSION['user']['role'] === 'admin'){
            // Delete movie and related ratings if user is admin and movie exists.
            $dbMovie->deleteMovie($movie['id']);
            $dbRating->deleteMovieRatings($movieId);
            unset($_SESSION['movie_to_delete']);
        }else{
            unset($_SESSION['movie_to_delete']);
            throw new Exception("No corresponding rating could be found.");
        }
    }
}

// Handle user deletion based on POST request.
if(filter_has_var(INPUT_POST, var_name: 'delete-user')) {
    $userId = (int)filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    $user = $dbUser->getUserById($userId);
    
    if(empty($user)){
        throw new Exception("No corresponding user could be found.");
    }else if($_SESSION['user']['role'] === 'admin'){
        // Set session variable for user deletion if user is admin.
        $_SESSION['user_to_delete'] = $userId;
    }else{
        throw new Exception("No corresponding user could be found.");
    }
}

// Handle user deletation confirmation based on POST request.
if(filter_has_var(INPUT_POST, 'user-confirmation')) {
    $userId = (int)filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    $confirmation = (int)filter_input(INPUT_POST, 'user-confirmation', FILTER_VALIDATE_INT);
    
    if($confirmation !== 1){
        unset($_SESSION['user_to_delete']);
    } else {
        $user = $dbUser->getUserById($userId);
        if(empty($user)){
            unset($_SESSION['user_to_delete']);
            throw new Exception("No corresponding user could be found.");
        }else if($_SESSION['user']['role'] === 'admin'){
            // Delete user if user is admin and user exists.
            $dbUser->deleteUser(userId: (int)$user['id']);
            $dbRating->deleteUserRatings(userId: (int)$user['id']);
            unset($_SESSION['user_to_delete']);
            header("Location: " . BASE_URL . "admin.php?cat=1");
        }else{
            unset($_SESSION['user_to_delete']);
            throw new Exception("No corresponding user could be found.");
        }
    }
}

// Handle rating deletion based on POST request.
if(filter_has_var(INPUT_POST, var_name: 'delete-rating')) {
    $ratingId = (int)filter_input(INPUT_POST, 'rating_id', FILTER_VALIDATE_INT);

    $rating = $dbRating->getRatingDatasById($ratingId);
    
    if(empty($rating)){
        throw new Exception("No corresponding rating could be found.");
    }else if($_SESSION['user']['role'] === 'admin'){
        // Set session variable for rating deletion if user is admin.
        $_SESSION['rating_to_delete'] = $ratingId;
    }else{
        throw new Exception("No corresponding rating could be found.");
    }
}

// Handle rating deletation confirmation based on POST request.
if(filter_has_var(INPUT_POST, 'rating-confirmation')) {
    $ratingId = (int)filter_input(INPUT_POST, 'rating_id', FILTER_VALIDATE_INT);

    $confirmation = (int)filter_input(INPUT_POST, 'rating-confirmation', FILTER_VALIDATE_INT);
    
    if($confirmation !== 1){
        unset($_SESSION['rating_to_delete']);
    } else {
        $rating = $dbRating->getRatingDatasById($ratingId);
        if(empty($rating)){
            unset($_SESSION['rating_to_delete']);
            throw new Exception("No corresponding rating could be found.");
        }else if($_SESSION['user']['role'] === 'admin'){
            // Delete rating if user is admin and rating exists.
            $dbRating->deleteRating($rating['id']);
            unset($_SESSION['rating_to_delete']);
        }else{
            unset($_SESSION['rating_to_delete']);
            throw new Exception("No corresponding rating could be found.");
        }
    }
}

// Handle user role update based on POST request.
if(filter_has_var(INPUT_POST, var_name: 'user-role')) {
    $role = $_POST['role'] ?? "";
    $userId = $_POST['user_id'];

    $user = $dbUser->getUserById($userId);

    // Define valid roles.
    $validRoles = [
        'admin',
        ''
    ];

    if (!in_array($role, $validRoles, true)) {
        // Throw an exception if role is invalid.
        throw new Exception("Ce rôle n'existe pas.");
    } else if (!$user){
        throw new Exception("Cet utilisateur n'existe pas.");
    } else {
        // Update user role in the database.
        $dbUser->updateUserField($user['id'], "role", $role);
    }
}

// Generate content based on admin category.
switch ($category) {
    case 0 :
        // Retrieve and display movies with pagination.
        $movies = $dbMovie->getMovies('title', 'ASC', null, $itemsShown, null);
        $itemsAmount = $dbMovie->getMoviesCount(null);

        $genres = $dbGenre->getAllGenres(); // Get all genres.

        // Generate genre selection options for the add movie form.
        $genresSelect = '
            <select name="genre" id="genre" required>
                <option value="" disabled selected>Sélectionner un genre</option>
        ';
        foreach($genres as $genre) {
            $genresSelect .= '<option value="' . htmlspecialchars($genre['id']) . '">' . htmlspecialchars($genre['title']) . '</option>';
        }
        $genresSelect .= '
            </select>
        ';

        // Generate form for adding a movie and display published movies.
        $content = '
            <h2>Films publiés : ' . $dbMovie->getMoviesCount(null) . '</h2>
            <form action ="" method="post" class="form-add-movie" enctype="multipart/form-data">
                <h3>Ajouter un film</h3>
                <div>
                    <label for="title">Titre</label>
                    <input type="text" name="title" class="title">
                    ' . $titleErr . '
                </div>
                <div>
                    <label for="title">Description</label>
                    <textarea maxlength="1000" rows="8" name="description" class="description"></textarea>
                    ' . $descriptionErr . '
                </div>
                <div>
                    <label for="realisator">Réalisateur.ice</label>
                    <input type="text" name="realisator" class="realisator">
                    ' . $realisatorErr . '
                </div>
                <div>
                    <label for="release_date">Sortie</label>
                    <input type="date" name="release_date" class="release_date">
                    ' . $releaseDateErr . '
                </div>
                <div>
                    <label for="duration">Durée (en minutes)</label>
                    <input type="number" name="duration" class="duration">
                    ' . $durationErr . '
                </div>
                <div>
                    <label for="genre">Genre</label>
                    ' . $genresSelect . $genreErr . '
                </div>
                <div>
                    <label for="cover">Image de couverture</label>
                    <input type="file" name="cover" class="cover">
                    ' . $coverErr . '
                </div>
                <button type="submit" name="movie" class="button">Publier le film</button>
                ' . $successMessage . '
            </form>
        ';
        foreach($movies as $movie) {
            // Display each movie's details and deletion form.
            $content .= '
                <article>
                    <a href="' . BASE_URL . "movie.php?id=" . $movie['id'] . '"><h2>' . htmlspecialchars($movie['title']) . '</h2></a>
                    <p class="realisator">' . htmlspecialchars($movie['realisator']) . ' | ' . substr($movie['release_date'], 0, 4) . '</p>
            ';

            if(isset($_SESSION['movie_to_delete']) && $_SESSION['movie_to_delete'] == $movie['id']) {
                $content .= '
                    <form action="" method="post" class="form-delete">
                        <input type="hidden" name="movie_id" value="' . $movie['id'] . '">
                        <label id="delete-confirmation-yes">
                            <input type="radio" name="movie-confirmation" value="0" onchange="this.form.submit()">
                            <i class="fa-solid fa-xmark"></i>
                        </label>
                        <label id="delete-confirmation-no">
                            <input type="radio" name="movie-confirmation" value="1" onchange="this.form.submit()">
                            <i class="fa-solid fa-check"></i>
                        </label>
                    </form>
                ';
            } else {
                $content .= '
                    <form action="" method="post" class="form-delete">
                        <input type="hidden" name="movie_id" value="' . $movie['id'] . '">
                        <button type="submit" name="delete-movie"><i class="fa-solid fa-xmark"></i></button>
                    </form>
                    ';
            };
            $content .= '</article>';
        };
            break;
    case 1 :
        // Retrieve and display users with pagination.
        $users = $dbUser->getUsers('username', 'ASC', null, $itemsShown, null);
        $itemsAmount = $dbUser->getUsersCount();
        $content = '
            <h2>Comptes actifs : ' . $dbUser->getUsersCount() . '</h2>
        ';
        foreach($users as $user): 
            // Display each user's details and forms for role update and deletion.
            $signupDate = DateTime::createFromFormat('Y-m-d H:i:s', $user['created_at']);
            $format = new IntlDateFormatter(
                'fr_FR', 
                IntlDateFormatter::LONG, 
                IntlDateFormatter::NONE
            );
            $format->setPattern('d MMMM yyyy');
            $content .= '
                <article>
                    <h2>' . $user['username'] . '</h2>
                    <p>Compte crée le <strong class=bold">' . $format->format($signupDate) . '</strong></p>
                    <p>Avis publiés : <strong class=bold">' . $dbRating->getRatingsCount("user", $user['id']) . '</strong></p>
            ';
            if($_SESSION['user']['id'] !== $user['id']){
                $content .= '
                    <form method="post" class="user-role">
                        <input type="hidden" name="user_id" value="' . $user['id'] . '">
                        <select name="role" class="user-role-select">
                            <option value="" ' . ($user['role'] == "" ? ' selected="selected"' : '') . '>Utilisateur.ice</option>
                            <option value="admin" ' . ($user['role'] == "admin" ? ' selected="selected"' : '') . '>Administrateur.ice</option>
                        </select>
                        <button type="submit" name="user-role" class="button">Valider</button>
                    </form>
                ';
            } else {
               $content .= '
                <p>Role : ' . ($user['role'] === "admin" ? "Administrateur.ice" : "Utilisateur.ice") . '</p>
               ';
            }
                    
            if(isset($_SESSION['user_to_delete']) && $_SESSION['user_to_delete'] == $user['id']) {
                $content .= '
                    <form action="" method="post" class="form-delete">
                        <input type="hidden" name="user_id" value="' . $user['id'] . '">
                        <label id="delete-confirmation-yes">
                            <input type="radio" name="user-confirmation" value="0" onchange="this.form.submit()">
                            <i class="fa-solid fa-xmark"></i>
                        </label>
                        <label id="delete-confirmation-no">
                            <input type="radio" name="user-confirmation" value="1" onchange="this.form.submit()">
                            <i class="fa-solid fa-check"></i>
                        </label>
                    </form>
                ';
            } else {
                $content .= '
                    <form action="" method="post" class="form-delete">
                        <input type="hidden" name="user_id" value="' . $user['id'] . '">
                        <button type="submit" name="delete-user"><i class="fa-solid fa-xmark"></i></button>
                    </form>
                    ';
            };
            $content .= '</article>';
        endforeach;
        break;
    case 2 :
        // Retrieve and display ratings with pagination.
        $ratings = $dbRating->getRatings(null, null, $itemsShown);
        $itemsAmount = $dbRating->getRatingsCount(null, null);
        $content = '
            <h2>Avis publiés : ' . $dbRating->getRatingsCount(null, null) . '</h2>
        ';
        foreach($ratings as $rating):
            // Display each rating's details and deletion form.
            $movie = $dbMovie->getMovieDatas($rating['movie_id']);
            $user = $dbUser->getUserById($rating['user_id']);
            $content .= '
                <article>
                    <h2><strong class="regular">Avis de </strong>' . $user['username'] . ' <strong class="regular">sur </strong><a href="' . BASE_URL . 'movie.php?id=' . $movie['id']  . '">' . $movie['title'] . '</a></h2>
                    <p>Note : <strong>' . $rating['rate'] . '</strong class="bold"></p>
            ';
            if($rating['comment']){
                $content .= '
                    <p>Commentaire : ' . $rating['comment'] . '</p>
                ';
            }
            if(isset($_SESSION['rating_to_delete']) && $_SESSION['rating_to_delete'] == $rating['id']) {
                $content .= '
                    <form action="" method="post" class="form-delete">
                        <input type="hidden" name="rating_id" value="' . $rating['id'] . '">
                        <label id="delete-confirmation-yes">
                            <input type="radio" name="rating-confirmation" value="0" onchange="this.form.submit()">
                            <i class="fa-solid fa-xmark"></i>
                        </label>
                        <label id="delete-confirmation-no">
                            <input type="radio" name="rating-confirmation" value="1" onchange="this.form.submit()">
                            <i class="fa-solid fa-check"></i>
                        </label>
                    </form>
                ';
            } else {
                $content .= '
                    <form action="" method="post" class="form-delete">
                        <input type="hidden" name="rating_id" value="' . $rating['id'] . '">
                        <button type="submit" name="delete-rating"><i class="fa-solid fa-xmark"></i></button>
                    </form>
                    ';
            };
            $content .= '</article>';
        endforeach;
        break;
};

