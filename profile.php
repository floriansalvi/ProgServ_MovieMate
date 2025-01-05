<?php

require_once './controllers/protect.php';
require_once 'controllers/profileValidation.php';

// Create a DateTime object from the user's 'created_at' session value
$dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $_SESSION['user']['created_at']);

// Setup an IntlDateFormatter for formatting the date in French locale
$format = new IntlDateFormatter(
    'fr_FR', // Locale set to French (France)
    IntlDateFormatter::LONG, // Long date format (e.g., '25 décembre 2025')
    IntlDateFormatter::NONE // No time format
);
$format->setPattern('d MMMM yyyy'); // Custom pattern for date format

// Check if the 'update' page is requested via the GET parameter
if(isset($_GET['page']) && $_GET['page'] === "update") {
    // Prepare the form content for updating the profile
    $profileContent = '
    <div class="profile-info">
        <form action="" method="POST" class="form" id="profile-update">
            <label for="username">Nom d\'utilisateur</label>
            <input type="text" value="' . $_SESSION['user']['username'] . '" name="username" class="username">'
            . $newUsernameErr .
            '<label for="oldPassword">Mot de passe actuel</label>
            <input type="password" name="oldPassword" class="oldPassword">'
            . $oldPasswordErr .
            '<label for="newPassword">Nouveau mot de passe</label>
            <input type="password" name="newPassword" class="newPassword">'
            . $newPasswordErr .
            '<label for="newPasswordConf">Confirmez le nouveau mot de passe</label>
            <input type="password" name="newPasswordConf" class="newPasswordConf">'
            . $newPasswordConfErr .
            '<p>Image de profil</p>
            <div class="profile-cover-choice">
                <label>
                    <input type="radio" name="profile_cover" value="0" ' . ($_SESSION['user']['cover'] == 0 ? 'checked' : '') . ' required>
                    <img src="' . BASE_URL .'assets/img/user_cover/user_cover_0.jpg" alt="Cover 0" class="cover-image">
                </label>
                <label>
                    <input type="radio" name="profile_cover" value="1" ' . ($_SESSION['user']['cover'] == 1 ? 'checked' : '') . ' required>
                    <img src="' . BASE_URL .'assets/img/user_cover/user_cover_1.jpg" alt="Cover 1" class="cover-image">
                </label>
                <label>
                    <input type="radio" name="profile_cover" value="2" ' . ($_SESSION['user']['cover'] == 2 ? 'checked' : '') . ' required>
                    <img src="' . BASE_URL .'assets/img/user_cover/user_cover_2.jpg" alt="Cover 2" class="cover-image">
                </label>
                <label>
                    <input type="radio" name="profile_cover" value="3" ' . ($_SESSION['user']['cover'] == 3 ? 'checked' : '') . ' required>
                    <img src="' . BASE_URL .'assets/img/user_cover/user_cover_3.jpg" alt="Cover 3" class="cover-image">
                </label>
            </div>
            <button type="submit" name="profile-update">Confirmer les changements</button>
        </form>
    </div>
';
}else{
    // Prepare the default profile content displaying user information
    $profileContent = '
        <div class="profil-info">
            <h1>' . $_SESSION['user']['username'] . '</h1>
            <p>' . $_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname'] . '</p>
            <p>' . $_SESSION['user']['email'] . '</p>
            <p>Compte créé le ' . $format->format($dateObj) . '</p>
            <div class="update-user-links">
            <a href="' . BASE_URL . 'profile.php?page=update">Mettre à jour votre profil</a>';
            // Check if 'delete' page is requested and show the deletion confirmation form
            if(isset($_GET['page']) && $_GET['page'] = "delete") {
                $profileContent .= '
                    <form action="" method="post" class="form" id="delete-user">
                        <label id="delete-user-yes">
                            <input type="radio" name="confirmation" value="0" onchange="this.form.submit()">
                            <i class="fa-solid fa-xmark"></i>
                        </label>
                        <label id="delete-user-no">
                            <input type="radio" name="confirmation" value="1" onchange="this.form.submit()">
                            <i class="fa-solid fa-check"></i>
                        </label>
                    </form>
            ';
            } else {
                // Link to delete the profile
                $profileContent .= '<a href="' . BASE_URL . 'profile.php?page=delete">Supprimer  votre profil</a>';
            }
    $profileContent .= '  
            <div>
        </div>
    ';
}

$title = "Profil";
ob_start();
?>
<main class="main-profile">
    <div class="profile-cover-container">
        <img src="assets/img/user_cover/user_cover_<?= $_SESSION['user']['cover'] ?>.jpg" alt="Profile cover" class="profile-cover">
    </div>
    <?= $profileContent ?>  <!-- Display the profile content -->
</main>


<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>
