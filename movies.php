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
    <div class="movies">
        <?php 
        if(empty($movies)){
            echo "<div>Aucun film n'est disponible.</div>";
        }else{
            foreach($movies as $movie): ?>
                <section class="movie" onclick="window.location='<?= BASE_URL . 'movie?id=' . $movie['id'] ?>'">
                    <div class="img-container">
                        <img src="assets/img/movie_cover/<?= $movie['cover_name']?>" alt="<?= $movie['title'] ?>">
                    </div>
                    <div class="txt-container">
                        <h2><?= htmlspecialchars($movie['title'])?></h2>
                        <p class="realisator"><?= htmlspecialchars($movie['realisator']) . " | " . substr($movie['release_date'], 0, 4)?></p>
                        <p class="duration">Durée : <?= htmlspecialchars($movie['duration']) . " min"?></p>
                    <?php 
                        if($movie['rating_avg'] !== null){
                            $movieRating = $movie['rating_avg'];
                            $fullStar = floor($movieRating);
                            $decimalStar = ($movieRating - $fullStar) > 0 ? 1 : 0;
                            $emptyStar = 5 - $decimalStar - $fullStar;
                            echo "<div class=movie-rating'>";
                            for($i = 0; $i < $fullStar; $i++){
                                echo "<i class='fa-solid fa-star' id='star-full'></i>";
                            }

                            if($decimalStar > 0){
                                echo "<i class='fa-solid fa-star-half' id='star-decimal'></i>";
                            }

                            for($i = 0; $i < $emptyStar; $i++){
                                echo "<i class='fa-solid fa-star' id='star-empty'></i>";
                            }
                            echo "</div>";  
                        }?>
                    </div>
                </section>
            <?php endforeach;
        }; ?>
    </div>
    <div class="pagination">
        <?php
        $link = BASE_URL . "movies.php";
        $page > 2 ? $link .= "?page=" . ($page -1) . "&" : "";
        echo $page > 1 ? "<a href='" . $link . "?sort=" . $sort . "'><i class='fa-solid fa-chevron-left'></i></a>" : "";
        echo $pagesAmount > 1 ? "<p>" . $page . " sur " . $pagesAmount ."</p>" : "";
        echo $page < $pagesAmount ? "<a href='" . BASE_URL . "movies.php?page=" . $page+1 . "&sort=" . $sort . "'><i class='fa-solid fa-chevron-right'></i></a>" : ""; ?>
    </div>

</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>