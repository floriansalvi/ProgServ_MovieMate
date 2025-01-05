<?php

use ch\UserManager;

require_once './config/autoload.php';
require_once './config/base_url.php';

// Initialize the UserManager object for database operations.
$db = new UserManager();

// Initialize variables for error messages and validation status.
$usernameErr = $passwordErr = $errorMessage = "";
$validationErr = false;

// Check if the login form has been submitted.
if(filter_has_var(INPUT_POST, 'login')) {
    // Retrieve and sanitize the input values for username and password.
    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

    // Validate the username input: check if it is empty.
    if(empty($username)){
        $usernameErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    }

    // Validate the password input: check if it is empty.
    if(empty($password)){
        $passwordErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    }
    
    // Proceed only if there are no validation errors.
    if(!$validationErr){
        // Fetch user data from the database using the provided username and password.
        $datas = $db->getUserDatas($username, $password);
        $username_ok = $datas['username_ok']; // Check if username exists and is correct.
        $password_ok = $datas['password_ok']; // Check if password matches the username.
        $activated = $datas['activated']; // Check if the user account is activated.

        // If both username and password are correct:
        if($username_ok && $password_ok){
            // Check if the user's account is activated.
            if($activated){
                // Set session variables to indicate the user is logged in.
                $_SESSION['is_logged'] = true;
                $_SESSION['user'] = [
                    'id' => $datas['id'],
                    'username' => $datas['username'],
                    'firstname' => $datas['firstname'],
                    'lastname' => $datas['lastname'],
                    'email' => $datas['email'],
                    'created_at' => $datas['created_at'],
                    'cover' => $datas['cover'],
                    'role' => $datas['role']
                ];
                // Redirect the user to the last visited page or the base URL if not set.
                $location = $_SESSION['lastVisitedPage'] ?? BASE_URL;
                header("Location: " . $location);
                exit();
            }else{
                // Error message if the account is not activated.
                $errorMessage = '<div class="alert alert-danger">Votre compte n\'a pas été validé. Cherchez le mail de confirmation reçu lors de votre inscription.</div>';
            }
        }else{
            // Error message if authentication data is incorrect.
            $errorMessage = '<div class="alert alert-danger">Données d\'authentification incorrectes.</div>';
        }
    }



}