<?php
session_start();

// Set session lifetime to 8 hours (28,800 seconds)
ini_set('session.gc_maxlifetime', 28800);
ini_set('session.cookie_lifetime', 28800);

// Define your valid username and password
$valid_username = 'admin'; // Replace with your username
$valid_password = 'password'; // Replace with your password

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate credentials
    if ($username === $valid_username && $password === $valid_password) {
        // Set session variable
        $_SESSION['logged_in'] = true;
        // Redirect to index.html
        header('Location: index.php');
        exit;
    } else {
        // Redirect back to login with an error
        header('Location: login.html?error=1');
        exit;
    }
} else {
    // If the user tries to access this page directly, redirect to login
    header('Location: login.html');
    exit;
}
?>
