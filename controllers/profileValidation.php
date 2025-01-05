<?php

use ch\RatingManager;
use ch\UserManager;
use ch\MovieManager;

require_once './config/autoload.php';
require_once './config/base_url.php';

// Initialize error variables to empty or use existing values if already set
$newUsernameErr = $newUsernameErr ?? "";
$oldPasswordErr = $oldPasswordErr ?? "";
$newPasswordErr = $newPasswordErr ?? "";
$newPasswordConfErr = $newPasswordConfErr ?? "";
$coverIdErr = $coverIdErr ?? "";
$validationErr = false;

// Check if the 'profile-update' form has been submitted
if(filter_has_var(INPUT_POST, 'profile-update')) {
    // Retrieve and sanitize form input
    $newUsername = trim(filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW));
    $oldPassword = filter_input(INPUT_POST, 'oldPassword', FILTER_UNSAFE_RAW);
    $newPassword = filter_input(INPUT_POST, 'newPassword', FILTER_UNSAFE_RAW);
    $newPasswordConf = filter_input(INPUT_POST, 'newPasswordConf', FILTER_UNSAFE_RAW);
    $coverId = filter_input(INPUT_POST, 'profile_cover', FILTER_VALIDATE_INT);

    // Instantiate UserManager
    $db = new UserManager();

    // Validate new username if provided
    if(!empty($newUsername) || !empty($newPassword) || $coverId != $_SESSION['user']['cover']){
        if(!empty($newUsername) && $newUsername != $_SESSION['user']['username']){
            // Validate username format
            if(!preg_match("/^[a-zA-Zäàâèêéïöôüç0-9]([-_]?[a-zA-Zäàâèêéïöôüç0-9]){5,31}$/", $newUsername)){
                    $newUsernameErr = '<div class="alert alert-danger">Le nom d\'utilisateur doit faire entre 6 et 32 caractères et peut contenir des lettres, chiffres, tirets et underscores.</div>';
                    $validationErr = true;
                    // Check if username is already in use
            } else if($db->isUserNameUsed($newUsername)){
                $newUsernameErr = '<div class="alert alert-danger">Ce nom d\'utilisateur n\'est pas disponible.</div>';
                $validationErr = true;
            }
        }

        // Validate passwords if provided
        if(!empty($oldPassword) && !empty($newPassword)){
            $datas = $db->getUserDatas($_SESSION['user']['username'], $oldPassword);
            $username_ok = $datas['username_ok'];
            $password_ok = $datas['password_ok'];
            // Check old password authenticity
            if(!$username_ok || !$password_ok){
                $oldPasswordErr = '<div class="alert alert-danger">Données d\'authentification incorrectes.</div>';
                $validationErr = true;
            }
        }

        // Validate new password if provided
        if(!empty($newPassword)){
            // Check if old password is provided
            if(empty($oldPassword)){
                $oldPasswordErr = '<div class="alert alert-danger">Votre mot de passe actuel est requis.</div>';
                $validationErr = true;
                // Validate new password format
            }else if(!preg_match("/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,32}$/", $newPassword)){
                $newPasswordErr = '<div class="alert alert-danger">Le mot de passe doit faire entre 8 et 32 caractères et doit contenir un chiffre, une minuscule, une majuscule et un caractère spécial.</div>';
                $validationErr = true;
                // Check if new password confirmation is provided
            }else if(empty($newPasswordConf)){
                $newPasswordConfErr = '<div class="alert alert-danger">Veuillez confirmer votre nouveau mot de passe.</div>';
                $validationErr = true;
                // Check if new password matches confirmation
            }else if($newPassword !== $newPasswordConf){
                $newPasswordConfErr = '<div class="alert alert-danger">Les mots de passe ne correspondent pas.</div>';
                $validationErr = true;
            }
        }

        // Validate profile cover ID if changed
        if($coverId != $_SESSION['user']['cover']){
            // Check if cover ID is valid
            if($coverId < 0 || $coverId > 4){
                $coverIdErr = '<div class="alert alert-danger">Aucune image n\'a pas être trouvée.</div>';
                $validationErr = true;
            }
        }

        // Update user details if no validation errors
        if(!$validationErr){
            if(!empty($newUsername)){
                $db->updateUserField($_SESSION['user']['id'], "username", $newUsername);
                // Update session with new username
                $_SESSION['user']['username'] = $newUsername;
            }

            if(!empty($newPassword)){
                // Hash new password
                $newPasswordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
                $db->updateUserField($_SESSION['user']['id'], "password", $newPasswordHash);
            }

            if($coverId != $_SESSION['user']['cover']){
                $db->updateUserField($_SESSION['user']['id'], "cover", $coverId);
                // Update session with new cover ID
                $_SESSION['user']['cover'] = $coverId;
            }
            // Redirect to profile page
            header("Location: " . BASE_URL . "profile.php");
            exit();
        }
    }
}

// Handle account deletion confirmation
if(filter_has_var(INPUT_POST, 'confirmation')) {    
    $confirmation = (int)filter_input(INPUT_POST, 'confirmation', FILTER_VALIDATE_INT);
    
    $userId = $_SESSION['user']['id'];

    if($confirmation !== 1){
        // Redirect if confirmation not given
        header("Location: " . BASE_URL . "profile.php");
        exit();
    } else {
        // Proceed with account deletion
        $ratingDb = new RatingManager();
        $userDb = new UserManager();
        $movieDb = new MovieManager();

        // Delete user ratings
        $ratingDb->deleteUserRatings($_SESSION['user']['id']);

        // Delete user account
        $userDb->deleteUser($_SESSION['user']['id']);

        // End session
        session_destroy();

        // Redirect to base URL
        header("Location: " . BASE_URL);
        exit();
    }
}