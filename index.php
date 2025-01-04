<?php
session_start();

require_once './config/autoload.php';
include './controllers/lastVisitedPage.php';
use ch\MovieManager;

$db = new MovieManager();
$moviesFavourite = $db->getMovies('rating_avg', 'DESC', null, 3, null);
$moviesLastAdded = $db->getMovies('add_date', 'DESC', null, 3, null);

$title ="Accueil";
ob_start(); ?>

<main class="main-index">
    <section class="subheader">
        <h1>MovieMate</h1>
        <p>MovieMate est une plateforme adressée aux cinophiles souhaitant découvrir de nouvelles oeuvres à visionner et voulant partager leur avis et recommendations à toute une communauté.</p>
    </section>
    <section>
        <h2>Les mieux notés</h2>
        <div class="movies" class="favourite">
            <?php
                if(empty($moviesFavourite)){
                    echo "<div>Aucun film n'est disponible.</div>";
                }else{
                    foreach($moviesFavourite as $movie): ?>
                        <article class="movie" onclick="window.location='<?= BASE_URL . 'movie.php?id=' . $movie['id'] ?>'">
                            <div class="img-container">
                                <img src="assets/img/movie_cover/<?= $movie['cover_name']?>" alt="<?= $movie['title'] ?> cover" loading="lazy">
                            </div>
                            <div class="txt-container">
                                <h2><?= htmlspecialchars($movie['title'])?></h2>
                                <p class="realisator"><?= htmlspecialchars($movie['realisator']) . " | " . substr($movie['release_date'], 0, 4)?></p>
                                <?php
                                    $minutes = $movie['duration'] % 60;
                                    $hours = ($movie['duration'] - $minutes)/60;
                                ?>
                                <p class="duration"><?= $hours ."h" . $minutes . "m"?></p>
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
                                    
                                        echo "</div><p>" . htmlspecialchars(round($movie['rating_avg'], 1)) . "</p></div>";
                                        
                                }?>
                            </div>
                        </article>
                    <?php endforeach;
                }; ?>
        </div>
    </section>
    <section>
        <h2>Récemment ajoutés</h2>
        <div class="movies" class="last-added">
            <?php
                if(empty($moviesLastAdded)){
                    echo "<div>Aucun film n'est disponible.</div>";
                }else{
                    foreach($moviesLastAdded as $movie): ?>
                        <article class="movie" onclick="window.location='<?= BASE_URL . 'movie.php?id=' . $movie['id'] ?>'">
                            <div class="img-container">
                                <img src="assets/img/movie_cover/<?= $movie['cover_name']?>" alt="<?= $movie['title'] ?> cover" loading="lazy">
                            </div>
                            <div class="txt-container">
                                <h2><?= htmlspecialchars($movie['title'])?></h2>
                                <p class="realisator"><?= htmlspecialchars($movie['realisator']) . " | " . substr($movie['release_date'], 0, 4)?></p>
                                <?php
                                    $minutes = $movie['duration'] % 60;
                                    $hours = ($movie['duration'] - $minutes)/60;
                                ?>
                                <p class="duration"><?= $hours ."h" . $minutes . "m"?></p>
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
                                    
                                        echo "</div><p>" . htmlspecialchars(round($movie['rating_avg'], 1)) . "</p></div>";
                                        
                                }?>
                            </div>
                        </article>
                    <?php endforeach;
                }; ?>
        </div>
    </section>
</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>