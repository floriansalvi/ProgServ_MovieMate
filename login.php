<?php
session_start();
require_once './controllers/loginValidation.php';
require_once './config/base_url.php';
if(isset($_SESSION['is_logged'])){
    header("Location: " . BASE_URL);
}
$title = "Connexion";
ob_start(); ?>

<main class="main-login">
    <form action="" method="post" class="form" id="login">
        <h1>Connexion</h1>
        <div>
            <label for="username">Nom d'utilisateur</label>
            <input type="text" placeholder="JohnDoe025" name="username" class="username">
            <?php echo $usernameErr; ?>
        </div>
        <div>
            <label for="password">Mot de passe</label>
            <input type="password" placeholder="Mot de passe" name="password" class="password">
            <?php echo $passwordErr; ?>
        </div>
            <button type="submit" name="login" class="button">Se connecter</button>
            <?php echo $errorMessage; ?>
            <p class="message">Pas encore de compte? <a href="<?php echo BASE_URL . 'signup.php'?>" class="button">S'inscrire</a></p>
    </form>
</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>

