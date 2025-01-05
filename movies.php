<?php
session_start();
require_once 'config/autoload.php';
require_once 'controllers/moviesSorting.php';
include './controllers/lastVisitedPage.php';

$title = "Films";
ob_start(); ?>

<main class="main-movies">
    <?php 
    echo $subHeader;
    echo $form;
    ?>
    <div class="movies">
        <?php 
        // Check if there are movies to display
        if(empty($movies)){
            // Display a message if no movies
            echo "<div class='alert-danger'>Aucun film n'est disponible.</div>";
        }else{
            // Loop through each movie to display them
            foreach($movies as $movie): ?>
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
                        // Display the movie's average rating if available
                        if($movie['rating_avg'] !== null){
                            echo "<div class='movie-rating'><div class='movie-stars'>";
                            // Get the rating average value
                            $movieRating = $movie['rating_avg'];
                            // Get the number of full stars to display
                            $fullStar = floor($movieRating);
                            $decimalStar = 0;

                            // Determine if there's a need for a half-star
                            if(($movieRating - $fullStar) >= 0.25 && ($movieRating - $fullStar) <= 0.75){
                                $decimalStar = 1;
                            }

                            // Adjust the number of full stars based on the rating
                            if(($movieRating - $fullStar) > 0.75){
                                $fullStar++;
                            }

                            // Output full stars
                            for($i = 0; $i < $fullStar; $i++){
                                echo "<i class='fa-solid fa-star' id='star-full'></i>";
                            }

                            // Output half star if necessary
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
    <div class="pagination">
        <?php

            // Prepare parameters for the backward pagination link
            $linkBackwardParam = [
                'page' => $page > 2 ? $page - 1 : null,
                'sort' => $sort ?? null,
                'genre' => $genre ?? null
            ];

             // Filter out null values from the backward link parameters
            $linkBackwardParam = array_filter($linkBackwardParam, fn($values) => $values !== null);
            $linkBackwardParam = array_filter($linkBackwardParam, fn($values) => $values !== "add");

            // Convert the parameters to a query string
            $linkBackwardParamString = http_build_query($linkBackwardParam);

            // Generate the backward pagination URL
            $linkBackward = BASE_URL . "movies.php" . ($linkBackwardParamString ? "?" . $linkBackwardParamString : "");

            // Prepare parameters for the forward pagination link
            $linkForwardParam = [
                'page' => $page < $pagesAmount ? $page + 1 : null,
                'sort' => $sort ?? null,
                'genre' => $genre ?? null
            ];

            // Filter out null values from the forward link parameters
            $linkForwardParam = array_filter($linkForwardParam, fn($values) => $values !== null);
            $linkForwardParam = array_filter($linkForwardParam, fn($values) => $values !== "add");

            // Convert the parameters to a query string
            $linkForwardParamString = http_build_query($linkForwardParam);

            // Generate the forward pagination URL
            $linkForward = BASE_URL . "movies.php" . ($linkForwardParamString ? "?" . $linkForwardParamString : "");

            // Display backward pagination link if not on the first page
            echo $page > 1 ? "<a href='" . $linkBackward . "'><i class='fa-solid fa-chevron-left'></i></a>" : "";

            // Display the current page number if there's more than one page
            echo $pagesAmount > 1 ? "<p>" . $page . "</p>" : "";
            
            // Display forward pagination link if not on the last page
            echo $page < $pagesAmount ? "<a href='" . $linkForward . "'><i class='fa-solid fa-chevron-right'></i></a>" : "";
        ?>
    </div>
</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>