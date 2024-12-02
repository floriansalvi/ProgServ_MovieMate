<?php

use ch\UserManager;

require_once './config/autoload.php';

$db = new UserManager();

$usernameErr = $profilCoverErr = $passwordErr = $errorMessage = "";
$validationErr = false;

