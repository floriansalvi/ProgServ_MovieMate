<?php

session_start();
include './controllers/lastVisitedPage.php';

$title ="Accueil";

ob_start(); ?>

<h1>Voici la page d'accueil</h1>
<p>Découvrez le plus grand catalogue de film du monde</p>

<?php echo $_SESSION['lastVisitedPage']?>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>