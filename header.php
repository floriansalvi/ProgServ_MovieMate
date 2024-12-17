<?php
require_once './config/base_url.php';
?>

<header>
    <img src="<?= BASE_URL . "/assets/img/movieMate_logo.png" ?>" alt="website logo" id="logo" onclick="window.location='<?= BASE_URL ?>'">
    <nav class="nav-bar">
        <ul class="nav-menu">
            <?php
                echo "
                    <li><a href='" . BASE_URL . "'>Accueil</a></li>
                    <li><a href='" . BASE_URL . "movies.php'>Films</a></li>
                    <li><a href='" . BASE_URL . "categories.php'>Catégories</a></li>
                ";
            ?>
        </ul>
        <ul class="nav-log">
            <?php
                if(!isset($_SESSION['is_logged'])){
                    echo "
                        <li><a href='" . BASE_URL . "login.php'>Connexion</a></li>
                        <li><a href='" . BASE_URL . "signup.php'>Inscription</a></li>
                    ";
                }else{
                    echo "
                        <li><a href='" . BASE_URL . "profil.php'>Profil</a></li>
                        <li><a href='" . BASE_URL . "controllers/logout.php'>Déconnexion</a></li>
                    ";

                }
            ?>
        </ul>
    </nav>
</header>