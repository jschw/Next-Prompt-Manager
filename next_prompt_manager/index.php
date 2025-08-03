<?php
session_start();
if (!isset($_SESSION['dashboard_token'])) {
    header('Location: login.php');
    exit;
} else {
    header('Location: dashboard.php');
    exit;
}