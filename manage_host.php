<?php
session_start();
require_once 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php?error=' . urlencode('Please log in to manage hosts'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Location: admin_dash.php?error=' . urlencode('Invalid CSRF token'));
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $host_id = trim($_POST['host_id'] ?? '');
        $passcode = trim($_POST['passcode'] ?? '');

        if (empty($name) || empty($host_id)) {
            header('Location: admin_dash.php?error=' . urlencode('Name and Host ID are required'));
            exit;
        }

        if ($action === 'add') {
            if (empty($passcode)) {
                header('Location: admin_dash.php?error=' . urlencode('Passcode is required for new host'));
                exit;
            }

            $stmt = $conn->prepare("SELECT id FROM hosts WHERE host_id = ?");
            $stmt->bind_param("s", $host_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                header('Location: admin_dash.php?error=' . urlencode('Host ID already exists'));
                $stmt->close();
                exit;
            }
            $stmt->close();

            $hashed_passcode = password_hash($passcode, PASSWORD_DEFAULT);
            $host_type = 'podcast';
            $stmt = $conn->prepare("INSERT INTO hosts (name, host_id, passcode, host_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $host_id, $hashed_passcode, $host_type);
        } else { // edit
            $id = trim($_POST['host_id'] ?? '');
            if (empty($id)) {
                header('Location: admin_dash.php?error=' . urlencode('Invalid host ID'));
                exit;
            }

            $stmt = $conn->prepare("SELECT id FROM hosts WHERE host_id = ? AND id != ?");
            $stmt->bind_param("si", $host_id, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                header('Location: admin_dash.php?error=' . urlencode('Host ID already exists'));
                $stmt->close();
                exit;
            }
            $stmt->close();

            if (!empty($passcode)) {
                $hashed_passcode = password_hash($passcode, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE hosts SET name = ?, host_id = ?, passcode = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $host_id, $hashed_passcode, $id);
            } else {
                $stmt = $conn->prepare("UPDATE hosts SET name = ?, host_id = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $host_id, $id);
            }
        }

        if ($stmt->execute()) {
            header('Location: admin_dash.php?success=' . urlencode('Host ' . $action . 'ed successfully'));
        } else {
            header('Location: admin_dash.php?error=' . urlencode('Failed to ' . $action . ' host'));
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $id = trim($_POST['host_id'] ?? '');
        if (empty($id)) {
            header('Location: admin_dash.php?error=' . urlencode('Invalid host ID'));
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM hosts WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header('Location: admin_dash.php?success=' . urlencode('Host deleted successfully'));
        } else {
            header('Location: admin_dash.php?error=' . urlencode('Failed to delete host'));
        }
        $stmt->close();
    }
}

$conn->close();
