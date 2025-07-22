<?php
require_once '../includes/config.php';

// Clear all admin session data
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

// Destroy the session
session_destroy();

// Redirect to admin login
redirect('login.php');
?>