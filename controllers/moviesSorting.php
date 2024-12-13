<?php

use ch\MovieManager;

require_once 'ch/MovieManager.php';
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

if(isset($_GET['sort']) && empty($_GET['sort'])){
    header("Location: " . BASE_URL . "movies.php");
    exit();
}

$sort = $_GET['sort'] ?? 'add';

if(!array_key_exists($sort, $validSortingEl)){
    throw new Exception('Cette page n\'existe pas.');
}

$page = $_GET['page'] ?? 1;

if(!filter_var($page, FILTER_VALIDATE_INT)){
    throw new Exception('Cette page n\'existe pas.');
};

if($page === '1'){
    header("Location: " . BASE_URL . "movies.php" . "?sort=" . $sort);
    exit();
}

$page = (int)$page;
if($page <= 0){
    throw new Exception('Cette page n\'existe pas.');
}

$db = new MovieManager();
$moviesPerPage = 12;
$moviesAmount = $db->getMoviesCount(null);
$pagesAmount = ceil($moviesAmount / $moviesPerPage);

if($page > $pagesAmount){
    throw new Exception('Cette page n\'existe pas.');
}

$offset = ($page-1) * $moviesPerPage;
$sortColumn = $validSortingEl[$sort]['column'] ?? "add_date";
$sortOrder = $validSortingEl[$sort]['order'] ?? "DESC";
$movies = $db->getMovies($sortColumn, $sortOrder, null, $moviesPerPage, $offset);

$form = 
'<form method="get" class="form-sort">
<select name="sort" class="form-sort-select" onchange="this.form.submit()">
<option value=""' . ($sort === "add" ? ' selected="selected"' : '') . '>Trier les films</option>';

foreach($validSortingEl as $key => $sortingEl){
    if($key !== "add"){
        $form .= '<option value="' . $key . '"' . ($key === $sort ? ' selected="selected"' : '') . '>' . $sortingEl['title'] . '</option>';
    }
}

$form .= '</select> </form>';