<?php

use ch\UserManager;

require_once './config/autoload.php';
require_once 'config/base_url.php';

$db = new UserManager();

$token = filter_input(INPUT_GET, 'token', filter: FILTER_UNSAFE_RAW);

$message = "";

if(empty($token)){
    $message = "Votre compte n'a pas pu être activé. Assurez-vous d'utiliser le lien reçu par mail lors de votre inscription.";
    exit;
} else if(!preg_match('/^[a-zA-Z0-9]+$/', $token)){
    $message = "Votre compte n'a pas pu être activé. Assurez-vous d'utiliser le lien reçu par mail lors de votre inscription.";
    exit;   
};

$isUserActivated = $db->activateUser($token);

if($isUserActivated){
    $message = "Votre compte a été activité. Vous pouvez désormais vous connecter.";
}else{
    $message = "Votre compte n'a pas pu être activé. Assurez-vous d'utiliser le lien reçu par mail lors de votre inscription.";
};

