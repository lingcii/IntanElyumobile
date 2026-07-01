<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'lupto';
$_SESSION['user_name'] = 'LUPTO Admin';
header('Location: Frontend/views/LUPTO/dashboard.php');
exit;
