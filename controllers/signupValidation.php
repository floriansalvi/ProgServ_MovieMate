<?php

use ch\UserManager;

require_once './config/autoload.php';
require_once './config/base_url.php';

// Initialize error variables and a flag for validation errors.
$usernameErr = $firstnameErr = $lastnameErr = $emailErr = $passwordErr = $passwordConfErr = "";
$validationErr = false;

/**
 * Validates and formats a name input.
 * 
 * @param string $value The input value to validate.
 * @return array Contains 'isValid', 'error', and 'formatedName'.
 */
function validateAndFormatName($value):array {
    $isValid = true;
    $error = "";
    $formatedName = "";
    
    // Check if the input length exceeds 32 characters.
    if(strlen($value) > 32){
        $isValid = false;
        $error = $error . "Ce champ peut contenir 32 caractères au maximum.";
    }

    // Ensure the input matches the allowed pattern for names.
    if(!preg_match("/^([a-zA-Zäàâèêéïöôüç]+([-'\s][a-zA-Zäàâèêéïöôüç]+)*)$/", $value)){
        $isValid = false;
        $error = $error . "Le champ ne contenir que des lettres, espaces, tirets et apostrophes.";
    }

    // Format the name if there are no errors.
    if(empty($error)){
        $formatedName = ucwords(strtolower($value), " \t\r\n\f\v-'");
    }

    /**
     * Return
     *      'isValid' a bool that indicates whether the input is valid or not
     *      'error' a string that contains errors messages.
     *      'formatedName' a string that contains the formated input.
     */
    return [
        'isValid' => $isValid,
        'error' => $error,
        'formatedName' => $formatedName
    ];
}

// Check if the signup form has been submitted
if(filter_has_var(INPUT_POST, 'signup')) {
    // Retrieve and trim form inputs.
    $username = trim(string: filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW));
    $firstname = trim(filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW));
    $lastname = trim(filter_input(INPUT_POST, 'lastname', FILTER_UNSAFE_RAW));
    $email = trim(string: filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $passwordConf = filter_input(INPUT_POST, 'passwordConf', FILTER_UNSAFE_RAW);

    $db = new UserManager();

    // Validate the username
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

    // Validate the first name
    $firstnameValidation = validateAndFormatName($firstname);
    if(empty($firstname)){
        $firstnameErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    } else if(!$firstnameValidation['isValid']){
        $firstnameErr = '<div class="alert alert-danger">' . $firstnameValidation['error'] . '</div>';
        $validationErr = true;
    }

    // Validate the last name
    $lastnameValidation = validateAndFormatName($lastname);
    if(empty($lastname)){
        $lastnameErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true; 
    } else if(!$lastnameValidation['isValid']){
        $lastnameErr = '<div class="alert alert-danger">' . $lastnameValidation['error'] . '</div>';
        $validationErr = true;
    }

    // Validate the email
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

    // Validate the password
    if(empty($password)){
        $passwordErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    } else if(!preg_match("/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,32}$/", $password)){
        $passwordErr = '<div class="alert alert-danger">Le mot de passe doit faire entre 8 et 32 caractères et doit contenir un chiffre, une minuscule, une majuscule et un caractère spécial.</div>';
        $validationErr = true;
    }

    // Validate the password confirmation
    if(empty($passwordConf)){
        $passwordConfErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    } else if($passwordConf !== $password){
        $passwordConfErr = '<div class="alert alert-danger">Les mots de passe ne correspondent pas.</div>';
        $validationErr = true;
    }                       

    // If no validation errors, proceed to save the user.
    if(!$validationErr){
        
        // Retrieves the formated names and hash the password.
        $firstname = $firstnameValidation['formatedName'];
        $lastname = $lastnameValidation['formatedName'];
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        // Attempt to save the user in the database.
        $isUserSaved = $db->saveUser($username, $firstname, $lastname, $email, $passwordHash);

        // Redirect to the confirmation page if the user is successfully saved.
        if(!$isUserSaved){
            throw new Exception("L'inscription n'a pas pu être effectuée.");
        }else{
            header("Location: " . BASE_URL . "confirmation.php" . "?username=" . urlencode($username));
            exit();
        }
    }
}