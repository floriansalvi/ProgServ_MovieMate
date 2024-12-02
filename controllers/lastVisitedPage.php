<?php

@session_start();
require_once './config/base_url.php';

if(!empty($_SERVER['QUERY_STRING'] )){
    $_SESSION['lastVisitedPage'] = BASE_URL . basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'];
}else{
    $_SESSION['lastVisitedPage'] = BASE_URL . basename($_SERVER['PHP_SELF']);
}