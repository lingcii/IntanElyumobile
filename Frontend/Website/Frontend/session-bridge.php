<?php
session_start();

// Check if PHP session has user data
if (!isset($_SESSION['user_id'])) {
    $loginRedirect = 'login.php';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if (str_contains($scriptName, '/views/')) {
        $loginRedirect = '../../login.php';
    }
    header('Location: ' . $loginRedirect);
    exit;
}

if (!function_exists('is_ajax_request')) {
    function is_ajax_request() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || isset($_GET['spa_ajax']) 
            || (isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true');
    }
}

