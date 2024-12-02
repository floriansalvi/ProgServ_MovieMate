<?php

session_start();
include './controllers/lastVisitedPage.php';

$title ="Accueil";

ob_start(); ?>

<h1>Voici la page catégories</h1>
<p>Découvrez les films de cette catégorie</p>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>