<?php
session_start();

require_once './config/autoload.php';
include './controllers/lastVisitedPage.php';
use ch\MovieManager;

$db = new MovieManager();

// Get the top 3 highest-rated movies
$moviesFavourite = $db->getMovies('rating_avg', 'DESC', null, 3, null);

// Get the 3 most recently added movies
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
                // Check if there are no favourite movies available
                if(empty($moviesFavourite)){
                    echo "<div>Aucun film n'est disponible.</div>";
                }else{
                    // Loop through the favourite movies and display each one
                    foreach($moviesFavourite as $movie): ?>
                        <article class="movie" onclick="window.location='<?= BASE_URL . 'movie.php?id=' . $movie['id'] ?>'">
                            <div class="img-container">
                                <img src="assets/img/movie_cover/<?= $movie['cover_name']?>" alt="<?= $movie['title'] ?> cover" loading="lazy">
                            </div>
                            <div class="txt-container">
                                <h2><?= htmlspecialchars($movie['title'])?></h2>
                                <p class="realisator"><?= htmlspecialchars($movie['realisator']) . " | " . substr($movie['release_date'], 0, 4)?></p>
                                <?php
                                    // Calculate the movie's duration in hours and minutes
                                    $minutes = $movie['duration'] % 60;
                                    $hours = ($movie['duration'] - $minutes)/60;
                                ?>
                                <p class="duration"><?= $hours ."h" . $minutes . "m"?></p>
                                <?php 
                                    // Check if the movie has an average rating and display it
                                    if($movie['rating_avg'] !== null){
                                        echo "<div class='movie-rating'><div class='movie-stars'>";
                                        $movieRating = $movie['rating_avg'];
                                        $fullStar = floor($movieRating);
                                        $decimalStar = 0;

                                        // Determine how many full and half stars to display
                                        if(($movieRating - $fullStar) >= 0.25 && ($movieRating - $fullStar) <= 0.75){
                                            $decimalStar = 1;
                                        }
                                        if(($movieRating - $fullStar) > 0.75){
                                            $fullStar++;
                                        }

                                        // Display the full stars
                                        for($i = 0; $i < $fullStar; $i++){
                                            echo "<i class='fa-solid fa-star' id='star-full'></i>";
                                        }

                                        // Display the half star if needed
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
                // Check if there are no recently added movies available
                if(empty($moviesLastAdded)){
                    echo "<div>Aucun film n'est disponible.</div>";
                }else{
                    // Loop through the recently added movies and display each one
                    foreach($moviesLastAdded as $movie): ?>
                        <article class="movie" onclick="window.location='<?= BASE_URL . 'movie.php?id=' . $movie['id'] ?>'">
                            <div class="img-container">
                                <img src="assets/img/movie_cover/<?= $movie['cover_name']?>" alt="<?= $movie['title'] ?> cover" loading="lazy">
                            </div>
                            <div class="txt-container">
                                <h2><?= htmlspecialchars($movie['title'])?></h2>
                                <p class="realisator"><?= htmlspecialchars($movie['realisator']) . " | " . substr($movie['release_date'], 0, 4)?></p>
                                <?php
                                    // Calculate the movie's duration in hours and minutes
                                    $minutes = $movie['duration'] % 60;
                                    $hours = ($movie['duration'] - $minutes)/60;
                                ?>
                                <p class="duration"><?= $hours ."h" . $minutes . "m"?></p>
                                <?php 
                                    // Check if the movie has an average rating and display it
                                    if($movie['rating_avg'] !== null){
                                        echo "<div class='movie-rating'><div class='movie-stars'>";
                                        $movieRating = $movie['rating_avg'];
                                        $fullStar = floor($movieRating);
                                        $decimalStar = 0;
                                        
                                        // Determine how many full and half stars to display
                                        if(($movieRating - $fullStar) >= 0.25 && ($movieRating - $fullStar) <= 0.75){
                                            $decimalStar = 1;
                                        }
                                        if(($movieRating - $fullStar) > 0.75){
                                            $fullStar++;
                                        }

                                        // Display the full stars
                                        for($i = 0; $i < $fullStar; $i++){
                                            echo "<i class='fa-solid fa-star' id='star-full'></i>";
                                        }

                                        // Display the half star if needed
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