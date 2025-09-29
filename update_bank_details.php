<?php
session_start();
require_once 'db_connect.php';

// Check if staff is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: staff_login.php?error=' . urlencode('Please log in to update bank details'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Location: staff_dash.php?error=' . urlencode('Invalid CSRF token'));
        exit;
    }

    $account_name = trim($_POST['account_name'] ?? '');
    $bank_name = trim($_POST['bank_name'] ?? '');
    $account_number = trim($_POST['account_number'] ?? '');

    // Validation
    if (empty($account_name) || empty($bank_name) || empty($account_number)) {
        header('Location: staff_dash.php?error=' . urlencode('All fields are required'));
        exit;
    }

    if (!preg_match('/^[A-Za-z\s]{2,100}$/', $account_name)) {
        header('Location: staff_dash.php?error=' . urlencode('Invalid account name'));
        exit;
    }

    if (!preg_match('/^[A-Za-z0-9\s]{2,100}$/', $bank_name)) {
        header('Location: staff_dash.php?error=' . urlencode('Invalid bank name'));
        exit;
    }

    if (!preg_match('/^[0-9]{10,20}$/', $account_number)) {
        header('Location: staff_dash.php?error=' . urlencode('Account number must be 10-20 digits'));
        exit;
    }

    // Check for duplicate account number (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM staff WHERE account_number = ? AND id != ?");
    $stmt->bind_param("si", $account_number, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        header('Location: staff_dash.php?error=' . urlencode('Account number already in use'));
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Update staff bank details
    $stmt = $conn->prepare("UPDATE staff SET account_name = ?, bank_name = ?, account_number = ? WHERE id = ?");
    $stmt->bind_param("sssi", $account_name, $bank_name, $account_number, $_SESSION['user_id']);
    if ($stmt->execute()) {
        header('Location: staff_dash.php?success=' . urlencode('Bank details updated successfully'));
    } else {
        header('Location: staff_dash.php?error=' . urlencode('Failed to update bank details'));
    }
    $stmt->close();
}

$conn->close();
