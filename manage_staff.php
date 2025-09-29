<?php
session_start();
require_once 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php?error=' . urlencode('Please log in to manage staff'));
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
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($email)) {
            header('Location: admin_dash.php?error=' . urlencode('Name and email are required'));
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: admin_dash.php?error=' . urlencode('Invalid email format'));
            exit;
        }

        if ($action === 'add') {
            if (empty($password)) {
                header('Location: admin_dash.php?error=' . urlencode('Password is required for new staff'));
                exit;
            }

            $stmt = $conn->prepare("SELECT id FROM staff WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                header('Location: admin_dash.php?error=' . urlencode('Email already exists'));
                $stmt->close();
                exit;
            }
            $stmt->close();

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO staff (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
        } else { // edit
            $staff_id = trim($_POST['staff_id'] ?? '');
            if (empty($staff_id)) {
                header('Location: admin_dash.php?error=' . urlencode('Invalid staff ID'));
                exit;
            }

            $stmt = $conn->prepare("SELECT id FROM staff WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $staff_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                header('Location: admin_dash.php?error=' . urlencode('Email already exists'));
                $stmt->close();
                exit;
            }
            $stmt->close();

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE staff SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $hashed_password, $staff_id);
            } else {
                $stmt = $conn->prepare("UPDATE staff SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $staff_id);
            }
        }

        if ($stmt->execute()) {
            header('Location: admin_dash.php?success=' . urlencode('Staff ' . $action . 'ed successfully'));
        } else {
            header('Location: admin_dash.php?error=' . urlencode('Failed to ' . $action . ' staff'));
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $staff_id = trim($_POST['staff_id'] ?? '');
        if (empty($staff_id)) {
            header('Location: admin_dash.php?error=' . urlencode('Invalid staff ID'));
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->bind_param("i", $staff_id);
        if ($stmt->execute()) {
            header('Location: admin_dash.php?success=' . urlencode('Staff deleted successfully'));
        } else {
            header('Location: admin_dash.php?error=' . urlencode('Failed to delete staff'));
        }
        $stmt->close();
    }
}

$conn->close();
