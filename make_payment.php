<?php
session_start();
require_once 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php?error=' . urlencode('Please log in to access the dashboard'));
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_dash.php?error=' . urlencode('Invalid request method'));
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: admin_dash.php?error=' . urlencode('Invalid CSRF token'));
    exit;
}

// Validate input
$staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$payment_type = filter_input(INPUT_POST, 'payment_type', FILTER_SANITIZE_STRING);

if ($staff_id === false || $staff_id === null) {
    header('Location: admin_dash.php?error=' . urlencode('Invalid staff ID'));
    exit;
}
if ($amount === false || $amount === null || $amount <= 0) {
    header('Location: admin_dash.php?error=' . urlencode('Invalid amount. Must be a positive number'));
    exit;
}
if (!$payment_type || !in_array($payment_type, ['Logistics', 'Salary', 'Others'])) {
    header('Location: admin_dash.php?error=' . urlencode('Invalid payment type'));
    exit;
}

// Verify staff exists and has bank details
$stmt = $conn->prepare("SELECT id, account_name, bank_name, account_number FROM staff WHERE id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
$stmt->close();

if (!$staff) {
    header('Location: admin_dash.php?error=' . urlencode('Staff member does not exist'));
    exit;
}
if (empty($staff['account_name']) || empty($staff['bank_name']) || empty($staff['account_number'])) {
    header('Location: admin_dash.php?error=' . urlencode('Staff member has incomplete bank details'));
    exit;
}

// Insert payment record
$stmt = $conn->prepare("INSERT INTO payments (staff_id, amount, payment_type, status) VALUES (?, ?, ?, 'Pending')");
$stmt->bind_param("ids", $staff_id, $amount, $payment_type);
if ($stmt->execute()) {
    header('Location: admin_dash.php?success=' . urlencode('Payment recorded successfully. Please process the transfer manually.'));
} else {
    header('Location: admin_dash.php?error=' . urlencode('Failed to record payment: Database error'));
}
$stmt->close();
$conn->close();