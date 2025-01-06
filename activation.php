<?php
session_start();
require_once './controllers/activationValidation.php';
require_once 'config/base_url.php';

if(isset($_SESSION['is_logged'])){
    header("Location: " . BASE_URL);
}
$title = "Activation";

ob_start(); ?>

<main class="main-activation">
    <div>
        <h1>Activation de votre compte</h1>
        <?php echo $message ?>
    </div>
</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>