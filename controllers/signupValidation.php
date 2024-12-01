<?php

use ch\UserManager;

require_once './config/autoload.php';
require_once 'config/base_url.php';

$usernameErr = $firstnameErr = $lastnameErr = $emailErr = $passwordErr = $passwordConfErr = "";
$validationErr = false;

function validateAndFormatName($value):array {
    $isValid = true;
    $error = "";
    $formatedName = "";
    
    if(strlen($value) > 32){
        $isValid = false;
        $error = $error . "Ce champ peut contenir 32 caractères au maximum.";
    }

    if(!preg_match("/^([a-zA-Zäàâèêéïöôüç]+([-'\s][a-zA-Zäàâèêéïöôüç]+)*)$/", $value)){
        $isValid = false;
        $error = $error . "Le champ ne contenir que des lettres, espaces, tirets et apostrophes.";
    }

    if(empty($error)){
        $formatedName = ucwords(strtolower($value), " \t\r\n\f\v-'");
    }

    return [
        'isValid' => $isValid,
        'error' => $error,
        'formatedName' => $formatedName
    ];
}

if(filter_has_var(INPUT_POST, 'signup')) {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW));
    $firstname = trim(filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW));
    $lastname = trim(filter_input(INPUT_POST, 'lastname', FILTER_UNSAFE_RAW));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $passwordConf = filter_input(INPUT_POST, 'passwordConf', FILTER_UNSAFE_RAW);

    $db = new UserManager();

    if(empty($username)){
        $usernameErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    } else if(!preg_match("/^[a-zA-Zäàâèêéïöôüç0-9]{6,32}([-_]?[a-zA-Zäàâèêéïöôüç0-9]+)*$/", $username)){
        $usernameErr = '<div class="alert alert-danger">Le nom d\'utilisateur doit faire entre 6 et 32 caractères et peut contenir des lettres, chiffres, tirets et underscores.</div>';
        $validationErr = true;
    } else if($db->isUserNameUsed($username)){
        $usernameErr = '<div class="alert alert-danger">Ce nom d\'utilisateur n\'est pas disponible.</div>';
        $validationErr = true;        
    }

    $firstnameValidation = validateAndFormatName($firstname);
    if(empty($firstname)){
        $firstnameErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    } else if(!$firstnameValidation['isValid']){
        $firstnameErr = '<div class="alert alert-danger">' . $firstnameValidation['error'] . '</div>';
        $validationErr = true;
    }

    $lastnameValidation = validateAndFormatName($lastname);
    if(empty($lastname)){
        $lastnameErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    } else if(!$lastnameValidation['isValid']){
        $lastnameErr = '<div class="alert alert-danger">' . $lastnameValidation['error'] . '</div>';
        $validationErr = true;
    }

    if(empty($email)){
        $emailErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;    
    } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $emailErr = '<div class="alert alert-danger">L\'adresse email n\'est pas valide.</div>';
        $validationErr = true; 
    } else if(strlen($email) > 48){
        $emailErr = '<div class="alert alert-danger">L\'adrese email ne peut faire que 48 caractères.</div>';
        $validationErr = true; 
    } else if($db->isEmailUsed($email)){
        $emailErr = '<div class="alert alert-danger">Cette adresse email n\'est pas disponible.</div>';
        $validationErr = true;   
    }

    if(empty($password)){
        $passwordErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    } else if(!preg_match("/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,32}$/", $password)){
        $passwordErr = '<div class="alert alert-danger">Le mot de passe doit faire entre 8 et 32 caractères et doit contenir un chiffre, une minuscule, une majuscule et un caractère spécial.</div>';
        $validationErr = true;
    }

    if(empty($passwordConf)){
        $passwordConfErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    } else if($passwordConf !== $password){
        $passwordConfErr = '<div class="alert alert-danger">Les mots de passe ne correspondent pas.</div>';
        $validationErr = true;
    }                       

    if(!$validationErr){
        $firstname = $firstnameValidation['formatedName'];
        $lastname = $lastnameValidation['formatedName'];
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        $isUserSaved = $db->saveUser($username, $firstname, $lastname, $email, $passwordHash);

        if(!$isUserSaved){
            throw new Exception("L'inscription n'a pas pu être effectuée.");
        }else{
            header("Location: " . BASE_URL . "confirmation.php" . "?username=" . urlencode($username));
        }
    }
}