<?php

use ch\UserManager;

require_once './config/autoload.php';
require_once './config/base_url.php';

$db = new UserManager();

$usernameErr = $passwordErr = $errorMessage = "";
$validationErr = false;

if(filter_has_var(INPUT_POST, 'login')) {
    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

    if(empty($username)){
        $usernameErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    }

    if(empty($password)){
        $passwordErr = '<div class="alert alert-danger">Ce champ ne peut pas être vide.</div>';
        $validationErr = true;
    }
    
    if(!$validationErr){
        $datas = $db->getUserDatas($username, $password);
        $username_ok = $datas['username_ok'];
        $password_ok = $datas['password_ok'];
        $activated = $datas['activated'];

        if($username_ok && $password_ok){
            if($activated){
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
                $location = $_SESSION['lastVisitedPage'] ?? BASE_URL;
                header("Location: " . $location);
                exit();
            }else{
                $errorMessage = '<div class="alert alert-danger">Votre compte n\'a pas été validé. Cherchez le mail de confirmation reçu lors de votre inscription.</div>';
            }
        }else{
            $errorMessage = '<div class="alert alert-danger">Données d\'authentification incorrectes.</div>';
        }
    }



}