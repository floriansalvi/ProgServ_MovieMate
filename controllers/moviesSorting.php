<?php

use ch\GenreManager;
use ch\MovieManager;

require_once 'ch/MovieManager.php';
require_once 'ch/GenreManager.php';
require_once './config/base_url.php';

// Define valid sorting elements with corresponding titles, database columns, and order directions.
$validSortingEl = [
    "add" => [
        'title' => 'Durée par ordre décroissant',
        'column' => 'duration', 
        'order' => 'DESC'  
    ],
    "ta" => [
        'title' => 'Titre par ordre croissant',
        'column' => 'title', 
        'order' => 'ASC'
    ],
    "td" => [
        'title' => 'Titre par ordre décroissant',
        'column' => 'title', 
        'order' => 'DESC'
    ],
    "ra" => [
        'title' => 'Réalisateur par ordre croissant',
        'column' => 'realisator', 
        'order' => 'ASC'
    ],
    "rd" => [
        'title' => 'Réalisateur par ordre décroissant',
        'column' => 'realisator', 
        'order' => 'DESC'
    ],
    "rda" => [
        'title' => 'Sortie par ordre croissant',
        'column' => 'release_date', 
        'order' => 'ASC'
    ],
    "rdd" => [
        'title' => 'Sortie par ordre décroissant',
        'column' => 'release_date', 
        'order' => 'DESC'
    ],
    "da" => [
        'title' => 'Durée par ordre croissant',
        'column' => 'duration', 
        'order' => 'ASC'
    ],
    "dd" => [
        'title' => 'Durée par ordre décroissant',
        'column' => 'duration', 
        'order' => 'DESC'
    ]
];

// Get the sort parameter from the query string, defaulting to "add".
$sort = $_GET['sort'] ?? "add";

// Redirect to the movies default page if the sort parameter is empty or equals "add".
if(isset($_GET['sort']) && empty($_GET['sort'])){
    header("Location: " . BASE_URL . "movies.php");
    exit();
} else if(isset($_GET['sort']) && $sort === "add"){
    header("Location: " . BASE_URL . "movies.php");
    exit();
} else if(!array_key_exists($sort, $validSortingEl)){
    // Throw an exception if the sort parameter is invalid.
    throw new Exception('Cette page n\'existe pas.');
};

// Get the genre parameter from the query string, defaulting to null.
$genre = $_GET['genre'] ?? null;

// Create an instance of GenreManager and fetch all genres.
$dbGenre = new GenreManager();
$allGenres = $dbGenre->getAllGenres();
$allGenreIds = array_column($allGenres, 'id');

// Validate the genre parameter, ensuring it is an integer and exists in the list of genre IDs.
if($genre !== null){
    if(!filter_var($genre, FILTER_VALIDATE_INT) || !in_array((int)$genre, $allGenreIds)){
        throw new Exception('Cette page n\'existe pas.');
    }else{
        $genre = (int)$genre;
    }
}

// Get the page parameter from the query string, defaulting to 1.
$page = $_GET['page'] ?? 1;

// Validate the page parameter to ensure it is a positive integer.
if(!filter_var($page, FILTER_VALIDATE_INT)){
    throw new Exception('Cette page n\'existe pas.');
} else if($page === '1'){
    // Redirect to the movies default page if the page is 1.
    header("Location: " . BASE_URL . "movies.php" . "?sort=" . $sort);
    exit();
} else if((int)$page <= 0){
    throw new Exception('Cette page n\'existe pas.');
};

// Create an instance of MovieManager and calculate pagination variables.
$dbMovie = new MovieManager();
$moviesPerPage = 15;
$moviesAmount = $dbMovie->getMoviesCount($genre);
$pagesAmount = ceil($moviesAmount / $moviesPerPage);

// Throw an exception if the requested page exceeds the total number of pages.
if($page > $pagesAmount){
    throw new Exception('Cette page n\'existe pas.');
}

// Calculate the offset for the SQL query based on the current page.
$offset = ($page-1) * $moviesPerPage;

// Determine the sorting column and order based on the selected sort parameter.
$sortColumn = $validSortingEl[$sort]['column'] ?? "add_date";
$sortOrder = $validSortingEl[$sort]['order'] ?? "DESC";

// Fetch the movies with the specified sorting, genre, and pagination.
$movies = $dbMovie->getMovies($sortColumn, $sortOrder, $genre, $moviesPerPage, $offset);

// Build the sorting form with the selected sorting option.
$form = '<form method="get" class="form-sort">';
$form .= $genre !== null ? '<input type="hidden" name="genre" value="' . htmlspecialchars($genre) . '">' : '';
$form .= '<select name="sort" class="form-sort-select" onchange="this.form.submit()">
<option value=""' . ($sort === "add" ? ' selected="selected"' : '') . '>Trier les films</option>';

foreach($validSortingEl as $key => $sortingEl){
    if($key !== "add"){
        $form .= '<option value="' . $key . '"' . ($key === $sort ? ' selected="selected"' : '') . '>' . $sortingEl['title'] . '</option>';
    }
}

$form .= '</select> </form>';

// Set the page title and description based on the selected genre or default values.
$pageTitle = $pageDescription = "";
if($genre !== null){
    $genreDatas = $dbGenre->getGenreDatas($genre);
    $pageTitle = $genreDatas['title'];
    $pageDescription = $genreDatas['description'];
}else{
    $pageTitle = "Les films de MovieMate";
    $pageDescription = "Parcourez le catalogue de films proposés par MovieMate !";
}

// Generate the subheader for the movies page with the title and description.
$subHeader = '
<div class="movies-subheader">
    <h1>' . $pageTitle . '</h1>
    <p>' . $pageDescription . '</p>
</div>
';