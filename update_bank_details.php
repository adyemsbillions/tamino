<?php
session_start();
require_once 'db_connect.php';

// Check if staff is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: staff_login.php?error=' . urlencode('Unauthorized access'));
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: staff_dash.php?error=' . urlencode('Invalid CSRF token'));
    exit;
}

// Validate form inputs
$account_name = trim($_POST['account_name'] ?? '');
$bank_name_select = trim($_POST['bank_name_select'] ?? '');
$bank_name = ($bank_name_select === 'Other') ? trim($_POST['bank_name'] ?? '') : $bank_name_select;
$account_number = trim($_POST['account_number'] ?? '');

if (empty($account_name) || empty($bank_name) || empty($account_number)) {
    header('Location: staff_dash.php?error=' . urlencode('All fields are required'));
    exit;
}

// Sanitize inputs
$account_name = filter_var($account_name, FILTER_SANITIZE_STRING);
$bank_name = filter_var($bank_name, FILTER_SANITIZE_STRING);
$account_number = filter_var($account_number, FILTER_SANITIZE_STRING);

// Update staff bank details
$stmt = $conn->prepare("UPDATE staff SET account_name = ?, bank_name = ?, account_number = ? WHERE id = ?");
$stmt->bind_param("sssi", $account_name, $bank_name, $account_number, $_SESSION['user_id']);
if ($stmt->execute()) {
    header('Location: staff_dash.php?success=' . urlencode('Bank details updated successfully'));
} else {
    header('Location: staff_dash.php?error=' . urlencode('Failed to update bank details'));
}
$stmt->close();
$conn->close();
exit;