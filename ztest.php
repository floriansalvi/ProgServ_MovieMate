<?php

use ch\MovieManager;
use ch\RatingManager;
use ch\UserManager;

require_once 'config/autoload.php';
require_once 'config/base_url.php';

// $dbMovie = new MovieManager();

// $movies = $dbMovie->getMovies("title", "DESC", null, 3, 0);

// foreach($movies as $movie){
//     echo
//     "<div>
//         <h1>" . $movie['title'] . "</h1>
//         <br>
//         <p>" .  $movie['description'] . "</p>
//         <br>
//         <strong>Durée : " . $movie['duration']  . " min</strong>
//         <br>
//         <strong>Réalisateur : " . $movie['realisator']  . "</strong>
//         </div>";
// }

// $dbUser = new UserManager();

// $users = $dbUser->getUsers("id", null, null, 3, 2);

// foreach($users as $user){
//     echo
//         "<div>
//             <h1>" . $user['username'] . "</h1>
//             <br>
//             <p>" .  $user['email'] . "</p>
//             <br>
//             <strong>Creation : " . $user['created_at']  . "</strong>
//             <br>
//             <strong>ID : " . $user['id']  . "</strong>
//             </div>";
// }

$dbRating = new RatingManager();

$ratings = $dbRating->getRatings("movie", 1, null);

foreach($ratings as $rating){
    echo
        "<div>
            <h1>" . $rating['comment'] . "</h1>";
}
