<?php
    session_start();
    require_once 'config/base_url.php';
    session_destroy();
    header(header: "Location: " . BASE_URL);