<?php

use ch\UserManager;

require_once './config/autoload.php';
require_once './config/base_url.php';

$db = new UserManager();

$token = filter_input(INPUT_GET, 'token', filter: FILTER_UNSAFE_RAW);

$message = "";

if(empty($token)){
    $message = "<p>Votre compte n'a pas pu être activé. Assurez-vous d'utiliser le lien reçu par mail lors de votre inscription.</p>";
    exit;
} else if(!preg_match('/^[a-zA-Z0-9]+$/', $token)){
    $message = "<p>Votre compte n'a pas pu être activé. Assurez-vous d'utiliser le lien reçu par mail lors de votre inscription.</p>";
    exit;   
};

$isUserActivated = $db->activateUser($token);

if($isUserActivated){
    $message = '<p>Votre compte a été activé. Vous pouvez désormais vous connecter.</p><br><a href="' . BASE_URL . 'login.php' . '" title="Login"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>';
}else{
    $message = "<p>Votre compte n'a pas pu être activé. Assurez-vous d'utiliser le lien reçu par mail lors de votre inscription.</p>";
};

