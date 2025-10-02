<?php
session_start();
require_once 'db_connect.php';

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: staff_dash.php?error=' . urlencode('Invalid CSRF token'));
    exit;
}

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: staff_login.php?error=' . urlencode('Unauthorized access'));
    exit;
}

$action = $_POST['action'] ?? '';
$task_id = trim($_POST['task_id'] ?? '');
$staff_id = $_SESSION['user_id'];

if (empty($task_id)) {
    header('Location: staff_dash.php?error=' . urlencode('Invalid task ID'));
    exit;
}

if ($action === 'complete') {
    // Verify task is assigned to the staff
    $stmt = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND staff_id = ?");
    $stmt->bind_param("ii", $task_id, $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        header('Location: staff_dash.php?error=' . urlencode('Unauthorized task access'));
        $stmt->close();
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE tasks SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $stmt->close();
    header('Location: staff_dash.php?success=' . urlencode('Task marked as completed'));
    exit;
} else {
    header('Location: staff_dash.php?error=' . urlencode('Invalid action'));
    exit;
}