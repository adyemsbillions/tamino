<?php
session_start();
require_once 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php?error=' . urlencode('Please log in to manage contacts'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Location: admin_dash.php?error=' . urlencode('Invalid CSRF token'));
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = trim($_POST['contact_id'] ?? '');
        if (empty($id)) {
            header('Location: admin_dash.php?error=' . urlencode('Invalid contact ID'));
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header('Location: admin_dash.php?success=' . urlencode('Contact deleted successfully'));
        } else {
            header('Location: admin_dash.php?error=' . urlencode('Failed to delete contact'));
        }
        $stmt->close();
    }
}

$conn->close();
