<?php
session_start();

// Check if PHP session has user data
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!function_exists('is_ajax_request')) {
    function is_ajax_request() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || isset($_GET['spa_ajax']) 
            || (isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true');
    }
}

