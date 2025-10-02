<?php
session_start();
require_once 'db_connect.php';

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: admin_dash.php?error=' . urlencode('Invalid CSRF token'));
    exit;
}

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php?error=' . urlencode('Unauthorized access'));
    exit;
}

$action = $_POST['action'] ?? '';
$podcast_id = trim($_POST['podcast_id'] ?? '');

if (empty($podcast_id)) {
    header('Location: admin_dash.php?error=' . urlencode('Invalid podcast ID'));
    exit;
}

if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE podcasts SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $podcast_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_dash.php?success=' . urlencode('Podcast approved successfully'));
    exit;
} elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE podcasts SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $podcast_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_dash.php?success=' . urlencode('Podcast rejected successfully'));
    exit;
} else {
    header('Location: admin_dash.php?error=' . urlencode('Invalid action'));
    exit;
}