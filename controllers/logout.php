<?php
    require_once '../config/base_url.php';
    session_start();
    session_destroy();
    $location = $_SESSION['lastVisitedPage'] ?? BASE_URL;
    header("Location: " . $location);
    exit();