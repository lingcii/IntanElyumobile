<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['user'])) {
        $_SESSION['user_id'] = $input['user']['id'];
        $_SESSION['user_name'] = $input['user']['name'];
        $_SESSION['user_email'] = $input['user']['email'];
        
        $role = $input['user']['role'];
        $_SESSION['user_role'] = $role;
        
        $_SESSION['user_municipality_id'] = $input['user']['municipality_id'];
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false]);
