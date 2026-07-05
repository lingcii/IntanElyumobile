<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['user'])) {
        $_SESSION['user_id'] = $input['user']['id'];
        $_SESSION['user_name'] = $input['user']['name'];
        $_SESSION['user_email'] = $input['user']['email'];
        $_SESSION['user_role'] = $input['user']['role'];
        $_SESSION['user_municipality_id'] = $input['user']['municipality_id'] ?? null;

        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false]);
