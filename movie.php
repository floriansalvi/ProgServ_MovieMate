<?php
use ch\MovieManager;
use ch\RatingManager;
use ch\UserManager;
require_once 'config/autoload.php';
require_once 'ch/MovieManager.php';
require_once 'ch/UserManager.php';
require_once 'ch/RatingManager.php';
require_once 'controllers/protect.php';
require_once 'controllers/ratingValidation.php';
require_once './config/base_url.php';

$dbMovie = new MovieManager();
if(isset($_GET['id'])){
    $movieId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
}else{
    throw new Exception("Cette page n'existe pas.");
}
$movie = $dbMovie->getMovieDatas($movieId);
if(empty($movie)){
    throw new Exception("Cette page n'existe pas.");
}

$minutes = $movie['duration'] % 60;
$hours = ($movie['duration'] - $minutes) / 60;
$duration = $hours . "h" . $minutes . "m";

$dbRating = new RatingManager();
$ratingsAmount = $dbRating->getRatingsCount("movie", $movieId);

$ratingsPerLoad = 5;

if($ratingsAmount > 0){
    $maxLoads = ceil($ratingsAmount / $ratingsPerLoad);
}else{
    $maxLoads = 1;
}


$ratingsLoad = $_GET['ra'] ?? 1;

if(!filter_var($ratingsLoad, FILTER_VALIDATE_INT)){
    throw new Exception('Cette page n\'existe pas.');
};

if($ratingsLoad === '1'){
    header("Location: " . BASE_URL . "movie.php" . "?id=" . $movieId);
    exit();
}

$ratingsLoad = (int)$ratingsLoad;
if($ratingsLoad <= 0 || $ratingsLoad > $maxLoads){
    throw new Exception('Cette page n\'existe pas.');
}

$ratingsShown = $ratingsPerLoad * $ratingsLoad;

$title = $movie['title'];
ob_start(); ?>

<main class="main-movie">
        <a href='<?= $_SESSION['lastVisitedPage'] ?>' class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
        <div>
            <section class="movie-card">
                <div class="movie-cover-container">
                    <img src="assets/img/movie_cover/<?= $movie['cover_name']?>" alt="<?= $movie['title'] ?> cover" class="movie-cover">
                </div>
                <div class="movie-info">
                    <h1> <?= $movie['title'] ?></h1>
                    <p class="realisator"><?= $movie['realisator'] ?></p>
                    <?php if(!empty($movie['rating_avg'])): ?>
                        <p class="rate"><?= htmlspecialchars(round($movie['rating_avg'], 1))?>
                        <span><?php
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
                        ?></span>
                    </p>
                    <?php endif;?>
                    <p class="duration"><?= $duration?></p>
                    <p class="description"><?= $movie['description'] ?></p>
                </div>
            </section>
            <section class="movie-ratings">
                <?php
                    $ratings = $dbRating->getRatings("movie", $movieId, $ratingsShown);
                    
                    if($dbRating->isMovieRatedByUser($movie['id'], $_SESSION['user']['id']) === false): ?>
                        <form action="" method="post" class="form" id="rate">
                            <h3>Add a rating</h3>
                            <div class="new-rating-rate">
                                <input type="radio" name="stars" value="5" id="id-5">
                                <label for="id-5"></label>
                                <input type="radio" name="stars" value="4" id="id-4">
                                <label for="id-4"></label>
                                <input type="radio" name="stars" value="3" id="id-3" checked>
                                <label for="id-3"></label>
                                <input type="radio" name="stars" value="2" id="id-2">
                                <label for="id-2"></label>
                                <input type="radio" name="stars" value="1" id="id-1">
                                <label for="id-1"></label>
                                <?php echo $rateErr; ?>
                            </div>
                            <div class="new-rating-comment">
                                <label for="comment">Comment</label>
                                <textarea placeholder="The movie was…" maxlength="500" name="comment" class="comment"></textarea>
                                <?php echo $commentErr;?>
                            </div>
                            <button type="submit" name="rate" class="button">Send</button>
                            <?php echo $errorMessage; ?>
                        </form>
                    <?php endif;
                    if(!empty($ratings)){
                        $dbUser = new UserManager();
                        foreach($ratings as $rating):
                            $user = $dbUser->getUserById($rating['user_id']);
                            ?>
                            <article class="rating">
                                <div class="user-info">
                                    <div  class="user-cover-container">
                                        <img src="<?= BASE_URL . "assets/img/user_cover/user_cover_" . $user['cover'] . ".jpg"?>" alt="user cover">
                                    </div>
                                    <h4><?= htmlspecialchars($user['username'])?></h4>
                                </div>
                                <div class="rating-info">
                                    <p class="rating-stars">
                                        <?php
                                            for($i = 0; $i < $rating['rate']; $i++):?>
                                                <i class='fa-solid fa-star' id='star-full'></i>
                                            <?php endfor; ?>
                                    </p>
                                    <?php
                                        if(!empty($rating['comment'])){
                                            echo "<p class='rating-comment'>" . $rating['comment'] . "</p>";
                                        }
                                        ?>
                                    <?php $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $rating['created_at']); ?>
                                    <p class="rating-date"><?= $dateObj->format('j F Y') ?></p>
                                </div>
                            </article>
                        <?php endforeach;
                    }
                    if($ratingsShown < $ratingsAmount): ?>
                        <a class="show-more-btn" href="<?= BASE_URL . "movie.php?id=" . $movieId . "&ra=" . $ratingsLoad+1 ?>">Show more ratings</a>
                    <?php endif; ?>
            </section>
        </div>
</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>