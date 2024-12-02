<?php
require_once './config/base_url.php';
?>

<header>
    <div id="logo">
    </div>
    <nav>
        <ul>
            <?php
                echo "
                    <li>Films</li>
                    <li><a href='" . BASE_URL . "categories.php'>Catégories</a></li>
                ";
            ?>
        </ul>
    </nav>
    <div id="log_section">
        <ul>
            <li><a href="<?php echo BASE_URL ?>">Accueil</a></li>
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
    </div>
</header>