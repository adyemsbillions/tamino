<?php
// Database connection configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'tamino_etv';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
