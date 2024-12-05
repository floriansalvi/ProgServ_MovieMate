<?php
session_start();
use ch\MovieManager;
require_once 'config/autoload.php';
require_once 'controllers/moviesSorting.php';

if(isset($_SESSION['is_logged'])){
    header("Location: " . BASE_URL);
}
$title = "Films";
ob_start(); ?>

<main class="main-movies">
    <h1>Tous les films</h1>
    <?php echo $form ?>
    <?php 
    if(empty($movies)){
        echo "<div>Aucun film n'est disponible.</div>";
    }else{
        foreach($movies as $movie): ?>
            <div>
                <h2><?= htmlspecialchars($movie['title'])?></h2>
                <p>Sortie : <?= htmlspecialchars($movie['release_date'])?></p>
                <strong>Réalisateur : <?= htmlspecialchars($movie['realisator'])?></strong>
                <br>
                <p>Durée : <?= htmlspecialchars($movie['duration'])?></p>
                <p>Ajouté le : <?= htmlspecialchars($movie['add_date'])?></p>
                <p><?= htmlspecialchars($movie['description'])?></p>
                <img src="assets/img/movie_cover/<?= $movie['cover_name']?>" alt="<? $movie['title'] ?>">
            </div>
        <?php endforeach;
    };

    if ($currentPage > 1): ?>
        <a href="<?= BASE_URL . "movies.php?page=" . ($currentPage - 1) . (isset($_GET['sort']) ? "&sort=" . urlencode($_GET['sort']) : "") ?>">Before</a>
    <?php endif; ?>
    
    <?php if ($currentPage < $pagesAmount): ?>
        <a href="<?= BASE_URL . "movies.php?page=" . ($currentPage + 1) . (isset($_GET['sort']) ? "&sort=" . urlencode($_GET['sort']) : "") ?>">Next</a>
    <?php endif; ?>    
</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>