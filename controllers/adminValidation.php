<?php

use ch\UserManager;
use ch\MovieManager;
use ch\RatingManager;
use ch\GenreManager;

require_once './config/autoload.php';
require_once './config/base_url.php';
require_once 'movieUploadValidation.php';

unset($_SESSION['movie_to_delete']);
unset($_SESSION['user_to_delete']);
unset($_SESSION['rating_to_delete']);

$dbMovie = new MovieManager();
$dbUser = new UserManager();
$dbRating = new RatingManager();
$dbGenre = new GenreManager();


$category = $_GET['cat'] ?? 0;
$moviesLimit = $_GET['movies'] ?? 10;

if(isset($_GET['cat'])){
    if(empty($_GET['cat']) || $category === 0){
        header("Location: " . BASE_URL . "admin.php");
        exit();
    }else if(!filter_var($_GET['cat'], FILTER_VALIDATE_INT) || $_GET['cat'] < 0 || $_GET['cat'] > 2){
        throw new Exception('Cette page n\'existe pas.');
    }else{
        $category = (int)$_GET['cat'];
    }
}

$content = "";

$itemsPerLoad = 10;

switch ($category) {
    case 0 :
        $dbMovie = new MovieManager();
        $itemsAmount = $dbMovie->getMoviesCount(null);
        break;
    case 1 :
        $dbUser = new UserManager();
        $itemsAmount = $dbUser->getUsersCount();
        break;
    case 2 :
        $dbRating = new RatingManager();
        $itemsAmount = $dbRating->getRatingsCount(null, null);
        break;
};

if($itemsAmount > 0){
    $maxLoads = ceil($itemsAmount / $itemsPerLoad);
}else{
    $maxLoads = 1;
}

$itemsLoad = $_GET['ia'] ?? 1;

if(!filter_var($itemsLoad, FILTER_VALIDATE_INT)){
    throw new Exception('Cette page n\'existe pas.');
};

if($itemsLoad === '1'){
    header("Location: " . BASE_URL . "admin.php" . "?cat=" . $category);
    exit();
};

$itemsLoad = (int)$itemsLoad;
if($itemsLoad <= 0 || $itemsLoad > $maxLoads){
    throw new Exception('Cette page n\'existe pas.');
};

$itemsShown = $itemsPerLoad * $itemsLoad;

//

if(filter_has_var(INPUT_POST, var_name: 'delete-movie')) {
    $movieId = (int)filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);

    $movie = $dbMovie->getMovieDatas($movieId);
    
    if(empty($movie)){
        throw new Exception("No corresponding movie could be found.");
    }else if($_SESSION['user']['role'] === 'admin'){
        $_SESSION['movie_to_delete'] = $movieId;
    }else{
        throw new Exception("No corresponding movie could be found.");
    }
}

if(filter_has_var(INPUT_POST, 'movie-confirmation')) {
    $movieId = (int)filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);

    $confirmation = (int)filter_input(INPUT_POST, 'movie-confirmation', FILTER_VALIDATE_INT);
    
    if($confirmation !== 1){
        unset($_SESSION['movie_to_delete']);
    } else {
        $movie = $dbMovie->getMovieDatas($movieId);
        if(empty($movie)){
            unset($_SESSION['movie_to_delete']);
            throw new Exception("No corresponding movie could be found.");
        }else if($_SESSION['user']['role'] === 'admin'){
            $dbMovie->deleteMovie($movie['id']);
            $dbRating->deleteMovieRatings($movieId);
            unset($_SESSION['movie_to_delete']);
        }else{
            unset($_SESSION['movie_to_delete']);
            throw new Exception("No corresponding rating could be found.");
        }
    }
}

//

if(filter_has_var(INPUT_POST, var_name: 'delete-user')) {
    $userId = (int)filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    $user = $dbUser->getUserById($userId);
    
    if(empty($user)){
        throw new Exception("No corresponding user could be found.");
    }else if($_SESSION['user']['role'] === 'admin'){
        $_SESSION['user_to_delete'] = $userId;
    }else{
        throw new Exception("No corresponding user could be found.");
    }
}

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
            $dbUser->deleteUser($user['id']);
            unset($_SESSION['user_to_delete']);
        }else{
            unset($_SESSION['user_to_delete']);
            throw new Exception("No corresponding user could be found.");
        }
    }
}

//

if(filter_has_var(INPUT_POST, var_name: 'delete-rating')) {
    $ratingId = (int)filter_input(INPUT_POST, 'rating_id', FILTER_VALIDATE_INT);

    $rating = $dbRating->getRatingDatasById($ratingId);
    
    if(empty($rating)){
        throw new Exception("No corresponding rating could be found.");
    }else if($_SESSION['user']['role'] === 'admin'){
        $_SESSION['rating_to_delete'] = $ratingId;
    }else{
        throw new Exception("No corresponding rating could be found.");
    }
}

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
            $dbRating->deleteRating($rating['id']);
            unset($_SESSION['rating_to_delete']);
        }else{
            unset($_SESSION['rating_to_delete']);
            throw new Exception("No corresponding rating could be found.");
        }
    }
}

//

    if(filter_has_var(INPUT_POST, var_name: 'user-role')) {
        $role = $_POST['role'] ?? "";
        $userId = $_POST['user_id'];

        $user = $dbUser->getUserById($userId);

        $validRoles = [
            'admin',
            ''
        ];

        if (!in_array($role, $validRoles, true)) {
            throw new Exception("Ce rôle n'existe pas.");
        } else if (!$user){
            throw new Exception("Cet utilisateur n'existe pas.");
        } else {
            $dbUser->updateUserField($user['id'], "role", $role);
        }
    }

switch ($category) {
    case 0 :
        $movies = $dbMovie->getMovies('title', 'ASC', null, $itemsShown, null);
        $itemsAmount = $dbMovie->getMoviesCount(null);

        $genres = $dbGenre->getAllGenres();
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
        $users = $dbUser->getUsers('username', 'ASC', null, $itemsShown, null);
        $itemsAmount = $dbUser->getUsersCount();
        $content = '
            <h2>Comptes actifs : ' . $dbUser->getUsersCount() . '</h2>
        ';
        foreach($users as $user): 
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
        $ratings = $dbRating->getRatings(null, null, $itemsShown);
        $itemsAmount = $dbRating->getRatingsCount(null, null);
        $content = '
            <h2>Avis publiés : ' . $dbRating->getRatingsCount(null, null) . '</h2>
        ';
        foreach($ratings as $rating): 
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

