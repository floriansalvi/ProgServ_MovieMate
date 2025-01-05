<?php

use ch\UserManager;

require_once './config/autoload.php';
require_once './config/base_url.php';

// Initialize a connection to the database.
$db = new UserManager();

// Retrieve the token query string in the url.
$token = filter_input(INPUT_GET, 'token', filter: FILTER_UNSAFE_RAW);

// Initialize the message variable.
$message = "";

// Validate the token. If not valid, display a message to the user.
if(empty($token)){
    $message = "<p>Votre compte n'a pas pu être activé. Assurez-vous d'utiliser le lien reçu par mail lors de votre inscription.</p>";
    exit;
} else if(!preg_match('/^[a-zA-Z0-9]+$/', $token)){
    $message = "<p>Votre compte n'a pas pu être activé. Assurez-vous d'utiliser le lien reçu par mail lors de votre inscription.</p>";
    exit;   
};

// Activate the user account and store whether is was successfuly activated or not.
$isUserActivated = $db->activateUser($token);

// Check whether the account was activated or not and display a message to the user.
if($isUserActivated){
    $message = '<p>Votre compte a été activé. Vous pouvez désormais vous connecter.</p><br><a href="' . BASE_URL . 'login.php' . '" title="Login"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>';
}else{
    $message = "<p>Votre compte n'a pas pu être activé. Assurez-vous d'utiliser le lien reçu par mail lors de votre inscription.</p>";
};

