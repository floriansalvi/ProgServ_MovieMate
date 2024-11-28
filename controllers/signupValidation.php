<?php

use ch\UserManager;

require_once('./config/autoload.php');

$pseudonymErr = $firstnameErr = $lastnameErr = $emailErr = $passwordErr = $passwordConfErr = $successMessage = "";
$validtionErr = false;

if(filter_has_var(INPUT_POST, 'signup')) {
    $pseudonym = filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW);
    $firstname = filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW);
    $lastname = filter_input(INPUT_POST, 'lastname', FILTER_UNSAFE_RAW);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $passwordConf = filter_input(INPUT_POST, 'passwordConf', FILTER_UNSAFE_RAW);

    $db = new UserManager();

//Pseudo pseudo (used, length, caract)

//Firstname validation (length, caract)

//Lastname validation (length, caract)

//email validation (used, length, caract)

//password validation (length, caract, strength)

//password conf validation (same as password)





}

