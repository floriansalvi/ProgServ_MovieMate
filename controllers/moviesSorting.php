<?php

use ch\GenreManager;
use ch\MovieManager;

require_once 'ch/MovieManager.php';
require_once 'ch/GenreManager.php';
require_once './config/base_url.php';

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

$sort = $_GET['sort'] ?? "add";
if(isset($_GET['sort']) && empty($_GET['sort'])){
    header("Location: " . BASE_URL . "movies.php");
    exit();
} else if(isset($_GET['sort']) && $sort === "add"){
    header("Location: " . BASE_URL . "movies.php");
    exit();
} else if(!array_key_exists($sort, $validSortingEl)){
    throw new Exception('Cette page n\'existe pas.');
};

//

$genre = $_GET['genre'] ?? null;

$dbGenre = new GenreManager();
$allGenres = $dbGenre->getAllGenres();
$allGenreIds = array_column($allGenres, 'id');

if($genre !== null){
    if(!filter_var($genre, FILTER_VALIDATE_INT) || !in_array((int)$genre, $allGenreIds)){
        throw new Exception('Cette page n\'existe pas.');
    }else{
        $genre = (int)$genre;
    }
}

//

$page = $_GET['page'] ?? 1;
if(!filter_var($page, FILTER_VALIDATE_INT)){
    throw new Exception('Cette page n\'existe pas.');
} else if($page === '1'){
    header("Location: " . BASE_URL . "movies.php" . "?sort=" . $sort);
    exit();
} else if((int)$page <= 0){
    throw new Exception('Cette page n\'existe pas.');
};

$dbMovie = new MovieManager();
$moviesPerPage = 15;
$moviesAmount = $dbMovie->getMoviesCount($genre);
$pagesAmount = ceil($moviesAmount / $moviesPerPage);

if($page > $pagesAmount){
    throw new Exception('Cette page n\'existe pas.');
}

$offset = ($page-1) * $moviesPerPage;
$sortColumn = $validSortingEl[$sort]['column'] ?? "add_date";
$sortOrder = $validSortingEl[$sort]['order'] ?? "DESC";
$movies = $dbMovie->getMovies($sortColumn, $sortOrder, $genre, $moviesPerPage, $offset);

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


$pageTitle = $pageDescription = "";

if($genre !== null){
    $genreDatas = $dbGenre->getGenreDatas($genre);
    $pageTitle = $genreDatas['title'];
    $pageDescription = $genreDatas['description'];
}else{
    $pageTitle = "Les films de MovieMate";
    $pageDescription = "Parcourez le catalogue de films proposés par MovieMate !";
}

$subHeader = '
<div class="movies-subheader">
    <h1>' . $pageTitle . '</h1>
    <p>' . $pageDescription . '</p>
</div>
';