<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($name) || empty($email) || empty($message)) {
        header('Location: index.php?error=' . urlencode('All fields are required'));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: index.php?error=' . urlencode('Invalid email format'));
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    if ($stmt->execute()) {
        header('Location: index.php?success=' . urlencode('Message sent successfully'));
    } else {
        header('Location: index.php?error=' . urlencode('Failed to send message. Please try again.'));
    }
    $stmt->close();
}

$conn->close();
