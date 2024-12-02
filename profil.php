<?php
require_once './controllers/protect.php';
require_once './controllers/profilValidation.php';
$title = "Profil";
ob_start();
?>

<h1>Profil</h1>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>
