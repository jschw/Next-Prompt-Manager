<?php
require_once 'functions.php';

session_start();

// --- Allow dashboard access via ?token=... ---
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    if (validate_token($token, null, true)) {
        $_SESSION['dashboard_token'] = $token;
        header('Location: dashboard.php');
        exit;
    } else {
        // Invalid token, redirect to login with error
        header('Location: login.php?error=1');
        exit;
    }
}

if (!isset($_SESSION['dashboard_token'])) {
    header('Location: login.php');
    exit;
} else {
    header('Location: dashboard.php');
    exit;
}