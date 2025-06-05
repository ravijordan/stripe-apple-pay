<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stripe_apple');

// Stripe configuration
define('STRIPE_SECRET_KEY', 'sk_test_XXXXXXXXXXXXXXXXXX');
define('STRIPE_PUBLIC_KEY', 'pk_test_XXXXXXXXXXXXXXXXXX');

// Create database connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}