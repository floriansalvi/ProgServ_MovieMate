<?php

session_start();

require_once 'config/base_url.php';

if(isset($_SESSION['is_logged'])){
    header("Location: " . BASE_URL);
}

$title = "Confirmation";

ob_start(); ?>

<main class="main-confirmation">
    <div>
        <h2>Confirmation d'inscription</h2>
        <?php echo '<h3>Bienvenue ' . $_GET["username"] . ' !</h3>'?>
        <p>Votre compte a été crée. Consultez votre boîte mail puis, cliquez sur le lien d'activation.</p>
    </div>
</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>