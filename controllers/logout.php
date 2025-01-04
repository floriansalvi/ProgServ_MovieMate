<?php
    require_once '../config/base_url.php';
    session_start();
    $location = $_SESSION['lastVisitedPage'] ?? BASE_URL;
    session_destroy();
    header("Location: " . $location);
    exit();