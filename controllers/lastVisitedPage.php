<?php

@session_start();
require_once './config/base_url.php';

/**
 * Store the last page the user visited.
 * If there are query strings in the url, they are also stored.
 */
if(!empty($_SERVER['QUERY_STRING'] )){
    $_SESSION['lastVisitedPage'] = BASE_URL . basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'];
}else{
    $_SESSION['lastVisitedPage'] = BASE_URL . basename($_SERVER['PHP_SELF']);
}