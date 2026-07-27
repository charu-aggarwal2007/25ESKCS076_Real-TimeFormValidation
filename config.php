<?php
/**
 * Database Configuration
 * ------------------------------------------------------------
 * Update these values if your XAMPP/WAMP/MySQL setup is different.
 * Default XAMPP values are already filled in below.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // default XAMPP root password is empty
define('DB_NAME', 'student_registration_db');

// Create connection using MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');

// Start session for admin panel (safe to call on every page)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
