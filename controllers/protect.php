<?php
session_start();

/**
 * Check whether the user is logged or not. If not, redirect the user to the login page.
 */
if(!isset($_SESSION['is_logged'])){
    header("Location: login.php");
}