<?php

use ch\MovieManager;

require_once 'ch/MovieManager.php';
require_once './config/base_url.php';

$validSortingEl = [
    'tit_asc' => [
        'title' => 'Titre par ordre croissant',
        'column' => 'title', 
        'order' => 'ASC'
    ],
    'tit_des' => [
        'title' => 'Titre par ordre décroissant',
        'column' => 'title', 
        'order' => 'DESC'
    ],
    'rea_asc' => [
        'title' => 'Réalisateur par ordre croissant',
        'column' => 'realisator', 
        'order' => 'ASC'
    ],
    'rea_des' => [
        'title' => 'Réalisateur par ordre décroissant',
        'column' => 'realisator', 
        'order' => 'DESC'
    ],
    'rel_asc' => [
        'title' => 'Sortie par ordre croissant',
        'column' => 'release_date', 
        'order' => 'ASC'
    ],
    'rel_des' => [
        'title' => 'Sortie par ordre décroissant',
        'column' => 'release_date', 
        'order' => 'DESC'
    ],
    'dur_asc' => [
        'title' => 'Durée par ordre croissant',
        'column' => 'duration', 
        'order' => 'ASC'
    ],
    'dur_des' => [
        'title' => 'Durée par ordre décroissant',
        'column' => 'duration', 
        'order' => 'DESC'
    ]
];

if(isset($_GET['sort']) && array_key_exists($_GET['sort'], $validSortingEl)){
    $sort = filter_input(INPUT_GET, 'sort', FILTER_UNSAFE_RAW);
}else{
    $sort = null;
}

if(is_null($sort) && isset($_GET['sort'])){
    header("Location: " . BASE_URL . "movies.php");
    exit();
}

$form = 
    '<form method="get">
    <select name="sort" id="sort" onchange="this.form.submit()">
    <option value=""' . ($sort === null ? ' selected="selected"' : '') . '>Trier les films</option>';

foreach($validSortingEl as $key => $sortingEl){
    $form .= '<option value="' . $key . '"' . ($key === $sort ? ' selected="selected"' : '') . '>' . $sortingEl['title'] . '</option>';
}

$form .=
    '</select>
    </form>';

$db = new MovieManager();

$moviesPerPage = 2;
$moviesAmount = $db->getMoviesCount(null);
$pagesAmount = ceil($moviesAmount / $moviesPerPage);


//

if(is_null($currentPage) && isset($_GET['page'])){
    header("Location: " . BASE_URL . "movies.php");
    exit();
}


$offset = ($currentPage-1) * $moviesPerPage;
$sortColumn = ($sort) ? $validSortingEl[$sort]['column'] : 'add_date';
$sortOrder = ($sort) ? $validSortingEl[$sort]['order'] : 'DESC';
$movies = $db->getMovies($sortColumn, $sortOrder, null, $moviesPerPage, $offset);


