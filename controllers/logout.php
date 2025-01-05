<?php
    require_once '../config/base_url.php';
    
    /**
     * log the user out by destroying the session and redirecting the user to the last visited page.
     */
    session_start();
    $location = $_SESSION['lastVisitedPage'] ?? BASE_URL;
    session_destroy();
    header("Location: " . $location);
    exit();