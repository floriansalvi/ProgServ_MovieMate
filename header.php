<?php


use ch\GenreManager;

require_once './config/autoload.php';
require_once './config/base_url.php';

?>

<header>
    <img src="<?= BASE_URL . "/assets/img/movieMate_logo.png" ?>" alt="website logo" id="logo" onclick="window.location='<?= BASE_URL ?>'">
    <nav class="nav-bar">
        <ul class="nav-menu">
            <li><a href="<?= BASE_URL ?>">Accueil</a></li>
            <li><a href="<?= BASE_URL ?>movies.php">Films</a></li>
            <li class='nav-dropdown'>
                <p>Catégories</p>
                <div>
                    <ul>
                        <?php

                        // Create an instance of GenreManager to fetch all genres from the database
                        $db = new GenreManager();

                        // Retrieve all genres
                        $genres = $db->getAllGenres();

                        // Loop through the genres and display each one as a menu item
                        foreach ($genres as $genre): ?>
                            <li><a href="<?= BASE_URL . "movies.php?genre=" . $genre['id'] ?>"><?= htmlspecialchars($genre['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>
            <?php if(isset($_SESSION['is_logged']) && $_SESSION['user']['role'] === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>admin.php">Admin</a></li>
            <?php endif; ?>
        </ul>
        <ul class="nav-log">
            <?php if(!isset($_SESSION['is_logged'])): ?>
                <li><a href="<?= BASE_URL ?>login.php">Connexion</a></li>
                <li><a href="<?= BASE_URL ?>signup.php">Inscription</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>profile.php">Profil</a></li>
                <li><a href="<?= BASE_URL ?>controllers/logout.php">Déconnexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>