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
                        <img src="assets/img/movie_cover/<?= $movie['cover_name']?>" alt="<?= $movie['title'] ?>" loading="lazy">
                    </div>
                    <div class="txt-container">
                        <h2><?= htmlspecialchars($movie['title'])?></h2>
                        <p class="realisator"><?= htmlspecialchars($movie['realisator']) . " | " . substr($movie['release_date'], 0, 4)?></p>
                        <p class="duration">Durée : <?= htmlspecialchars($movie['duration']) . " min"?></p>
                    <?php 
                        if($movie['rating_avg'] !== null){
                            echo "<div class='movie-rating'><div class='movie-stars'>";
                            $movieRating = $movie['rating_avg'];
                            $fullStar = floor($movieRating);
                            $decimalStar = 0;
                            if(($movieRating - $fullStar) >= 0.25 && ($movieRating - $fullStar) <= 0.75){
                                $decimalStar = 1;
                            }
                            if(($movieRating - $fullStar) > 0.75){
                                $fullStar++;
                            }
                            for($i = 0; $i < $fullStar; $i++){
                                echo "<i class='fa-solid fa-star' id='star-full'></i>";
                            }

                            if($decimalStar > 0){
                                echo "<i class='fa-solid fa-star-half' id='star-decimal'></i>";
                            }
                        
                            echo "</div><p>" . $movie['rating_avg'] . "</p></div>";
                             
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
        echo $pagesAmount > 1 ? "<p>" . $page . "</p>" : "";
        echo $page < $pagesAmount ? "<a href='" . BASE_URL . "movies.php?page=" . $page+1 . "&sort=" . $sort . "'><i class='fa-solid fa-chevron-right'></i></a>" : ""; ?>
    </div>

</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>