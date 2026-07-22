<?php
session_start();

// Destroy PHP session first
session_destroy();

// Redirect to login
header('Location: login.php');
exit;
