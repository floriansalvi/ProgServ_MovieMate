<?php

session_start();
require_once('./controllers/signupValidation.php');

$title = "Inscription";

ob_start(); ?>

<main class="main-signup">
    <form action="" method="post" class="form" id="signUp">
        <h2>Inscription</h2>
        <div>
            <label for="pseudonym">Pseudo</label>
            <input type="text" placeholder="JohnDoe025" name="pseudonym" class="pseudonym">
            <?php echo $pseudonymErr; ?>
        </div>
        <div>
            <label for="firstname">Prénom</label>
            <input type="text" placeholder="John" name="firstname" class="firstname">
            <?php echo $firstnameErr; ?>
        </div>
        <div>
            <label for="lastname">Nom</label>
            <input type="text" placeholder="Doe" name="lastname" class="lastname">
            <?php echo $lastnameErr; ?>
        </div>
        <div>
            <label for="email">Adresse email</label>
            <input type="email" placeholder="john.doe@movie-mate.ch" name="email" class="email">
            <?php echo $emailErr; ?>
        </div>
        <div>
            <label for="password">Mot de passe</label>
            <input type="password" placeholder="Mot de passe" name="password" class="password">
            <?php echo $passwordErr; ?>
        </div>
        <div>
            <label for="passwordConf">Confirmation du mot de passe</label>
            <input type="password" placeholder="Confirmation du mot de passe" name="passwordConf" class="passwordConf">
            <?php echo $passwordConfErr; ?>
        </div>
            <button type="submit" name="signup" class="button">Créer le compte</button>
            <p class="message">Déjà un compte? <a href="login.php" class="button">Se connecter</a></p>
        </form>
    </main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>

