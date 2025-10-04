<?php
session_start();
require_once 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php?error=' . urlencode('Please log in to access the dashboard'));
    exit;
}

// Fetch admin details
$stmt = $conn->prepare("SELECT name FROM admins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$admin_name = $admin['name'] ?? 'Admin User';
$stmt->close();

// Fetch staff list
$stmt = $conn->prepare("SELECT id, name, email, account_name, bank_name, account_number FROM staff");
$stmt->execute();
$result = $stmt->get_result();
$staff_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch hosts list
$stmt = $conn->prepare("SELECT id, name, host_id FROM hosts WHERE host_type = 'podcast'");
$stmt->execute();
$result = $stmt->get_result();
$hosts_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch contacts list
$stmt = $conn->prepare("SELECT id, name, email, message, created_at FROM contacts ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$contacts_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch podcasts list
$stmt = $conn->prepare("SELECT p.id, p.name, p.description, p.status, h.name AS host_name FROM podcasts p INNER JOIN hosts h ON p.host_id = h.id ORDER BY p.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$podcasts_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch tasks list
$stmt = $conn->prepare("SELECT t.id, t.description, t.status, t.created_at, p.name AS podcast_name, GROUP_CONCAT(s.name SEPARATOR ', ') AS staff_names 
                        FROM tasks t 
                        INNER JOIN podcasts p ON t.podcast_id = p.id 
                        INNER JOIN staff s ON t.staff_id = s.id 
                        GROUP BY t.id, t.description, t.status, t.created_at, p.name 
                        ORDER BY t.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$tasks_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch payment history
$stmt = $conn->prepare("SELECT p.id, p.staff_id, p.amount, p.payment_type, p.status, p.created_at, s.name AS staff_name 
                        FROM payments p 
                        INNER JOIN staff s ON p.staff_id = s.id 
                        ORDER BY p.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$payment_history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Generate CSRF token only if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tamino ETV</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Arial', sans-serif;
    }

    body {
        background: #1A1A1A;
        color: #FFFFFF;
    }

    .dashboard-container {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 240px;
        background: #FFFFFF;
        color: #1A1A1A;
        padding: 20px;
        position: fixed;
        height: 100%;
        overflow-y: auto;
        transform: translateX(0);
        transition: transform 0.3s ease;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }

    .sidebar-header {
        text-align: center;
        margin-bottom: 20px;
    }

    .sidebar-header h2 {
        color: #FF6200;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .close-btn {
        display: none;
        font-size: 1.5rem;
        color: #FF6200;
        cursor: pointer;
        position: absolute;
        top: 20px;
        right: 20px;
    }

    .sidebar-menu {
        list-style: none;
    }

    .sidebar-menu li {
        margin-bottom: 10px;
    }

    .sidebar-menu a {
        color: #1A1A1A;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        display: block;
        padding: 10px;
        border-radius: 8px;
        transition: background 0.3s ease, color 0.3s ease;
    }

    .sidebar-menu a:hover {
        background: #FF6200;
        color: #FFFFFF;
    }

    .hamburger {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1001;
        cursor: pointer;
        padding: 10px;
    }

    .hamburger span {
        background: #FFC107;
        height: 3px;
        width: 25px;
        margin: 5px 0;
        display: block;
        transition: all 0.3s ease;
    }

    .hamburger.active span:nth-child(1) {
        transform: rotate(45deg) translate(7px, 7px);
    }

    .hamburger.active span:nth-child(2) {
        opacity: 0;
    }

    .hamburger.active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -7px);
    }

    .main-content {
        margin-left: 240px;
        padding: 30px 15px;
        width: calc(100% - 240px);
        background: linear-gradient(135deg, #1A1A1A 0%, #333333 100%);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .dashboard-header {
        margin-bottom: 25px;
    }

    .dashboard-header h1 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #FFFFFF;
    }

    .dashboard-header p {
        font-size: 0.9rem;
        color: #CCCCCC;
        margin-top: 8px;
    }

    .message {
        display: <?php echo isset($_GET['error']) || isset($_GET['success']) ? 'block': 'none';
        ?>;
        background: <?php echo isset($_GET['error']) ? '#FF6200': '#FFC107';
        ?>;
        color: #FFFFFF;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        font-size: 0.9rem;
    }

    .admin-section {
        background: #FFFFFF;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        color: #1A1A1A;
    }

    .admin-section h3 {
        font-size: 1.2rem;
        color: #FF6200;
        margin-bottom: 10px;
    }

    .admin-section table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-section th,
    .admin-section td {
        padding: 8px;
        border: 1px solid #e5e5e5;
        text-align: left;
        font-size: 0.85rem;
    }

    .admin-section th {
        background: #F9F9F9;
        font-weight: 600;
    }

    .admin-section .btn {
        padding: 6px 12px;
        background: linear-gradient(135deg, #FF6200, #FFC107);
        color: #FFFFFF;
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .admin-section .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 98, 0, 0.4);
    }

    .admin-section .approve-btn {
        background: #28a745;
    }

    .admin-section .reject-btn {
        background: #dc3545;
    }

    .admin-section .add-btn {
        display: block;
        width: 100%;
        text-align: center;
        margin-top: 10px;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1002;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: #FFFFFF;
        border-radius: 12px;
        padding: 20px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        position: relative;
    }

    .modal-content h2 {
        font-size: 1.5rem;
        color: #FF6200;
        margin-bottom: 15px;
    }

    .modal-content .close-modal {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 1.5rem;
        color: #FF6200;
        cursor: pointer;
    }

    .modal-content .form-group {
        margin-bottom: 15px;
    }

    .modal-content label {
        display: block;
        font-size: 0.85rem;
        color: #1A1A1A;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .modal-content input,
    .modal-content select,
    .modal-content textarea {
        width: 100%;
        padding: 10px;
        border: 2px solid #e5e5e5;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #1A1A1A;
        background: #F9F9F9;
        transition: border-color 0.3s ease;
    }

    .modal-content textarea {
        height: 100px;
    }

    .modal-content input:focus,
    .modal-content select:focus,
    .modal-content textarea:focus {
        outline: none;
        border-color: #FF6200;
    }

    .modal-content .submit-btn {
        width: 100%;
        padding: 10px;
        background: linear-gradient(135deg, #FF6200, #FFC107);
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .modal-content .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(255, 98, 0, 0.4);
    }

    .logout-section {
        margin-top: 25px;
        text-align: center;
    }

    .logout-btn {
        padding: 10px 20px;
        background: #FF6200;
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .logout-btn:hover {
        background: #FFC107;
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(255, 98, 0, 0.4);
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 200px;
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .close-btn {
            display: block;
        }

        .hamburger {
            display: block;
        }

        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 60px 10px 20px;
        }

        .admin-section table {
            font-size: 0.8rem;
        }

        .modal-content {
            width: 95%;
            padding: 15px;
        }

        .modal-content h2 {
            font-size: 1.3rem;
        }

        .modal-content .form-group {
            margin-bottom: 10px;
        }

        .modal-content input,
        .modal-content select,
        .modal-content textarea {
            font-size: 0.85rem;
            padding: 8px;
        }

        .modal-content .submit-btn {
            font-size: 0.85rem;
            padding: 8px;
        }
    }

    @media (max-width: 480px) {
        .sidebar {
            width: 180px;
        }

        .sidebar-header h2 {
            font-size: 1.2rem;
        }

        .sidebar-menu a {
            font-size: 0.85rem;
            padding: 8px;
        }

        .dashboard-header h1 {
            font-size: 1.3rem;
        }

        .admin-section {
            padding: 12px;
        }

        .admin-section th,
        .admin-section td {
            padding: 6px;
        }

        .admin-section .btn {
            font-size: 0.75rem;
            padding: 4px 8px;
        }

        .modal-content {
            width: 98%;
        }
    }
    </style>
</head>

<body>
    <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="sidebar" id="sidebar">
        <span class="close-btn" id="closeSidebar">&times;</span>
        <div class="sidebar-header">
            <h2>TAMINO ETV</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="#manage-staff">Manage Staff</a></li>
            <li><a href="#manage-hosts">Manage Hosts</a></li>
            <li><a href="#view-contacts">View Contacts</a></li>
            <li><a href="#manage-podcasts">Manage Podcasts</a></li>
            <li><a href="#manage-tasks">Manage Tasks</a></li>
            <li><a href="make_payment.php">Make Payment</a></li>
            <li><a href="#payment-history">Payment History</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="message">
                <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : (isset($_GET['success']) ? htmlspecialchars($_GET['success']) : ''); ?>
            </div>
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h1>
                <p>Manage staff, hosts, contacts, podcasts, tasks, and payments</p>
            </div>
            <!-- Payment History -->
            <div class="admin-section" id="payment-history">
                <h3>Payment History</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Staff Name</th>
                            <th>Amount</th>
                            <th>Payment Type</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payment_history as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['staff_name']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_type']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($payment['status'])); ?></td>
                            <td><?php echo htmlspecialchars($payment['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Manage Staff -->
            <div class="admin-section" id="manage-staff">
                <h3>Manage Staff</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Account Name</th>
                            <th>Bank Name</th>
                            <th>Account Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_list as $staff): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff['id']); ?></td>
                            <td><?php echo htmlspecialchars($staff['name']); ?></td>
                            <td><?php echo htmlspecialchars($staff['email']); ?></td>
                            <td><?php echo htmlspecialchars($staff['account_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($staff['bank_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($staff['account_number'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="#edit-staff-<?php echo $staff['id']; ?>" class="btn edit-staff">Edit</a>
                                <a href="#delete-staff-<?php echo $staff['id']; ?>"
                                    class="btn delete-btn delete-staff">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button class="add-btn" id="addStaffBtn">Add New Staff</button>
            </div>
            <!-- Manage Hosts -->
            <div class="admin-section" id="manage-hosts">
                <h3>Manage Hosts</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Host ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hosts_list as $host): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($host['id']); ?></td>
                            <td><?php echo htmlspecialchars($host['name']); ?></td>
                            <td><?php echo htmlspecialchars($host['host_id']); ?></td>
                            <td>
                                <a href="#edit-host-<?php echo $host['id']; ?>" class="btn edit-host">Edit</a>
                                <a href="#delete-host-<?php echo $host['id']; ?>"
                                    class="btn delete-btn delete-host">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button class="add-btn" id="addHostBtn">Add New Host</button>
            </div>
            <!-- View Contacts -->
            <div class="admin-section" id="view-contacts">
                <h3>View Contacts</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts_list as $contact): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($contact['id']); ?></td>
                            <td><?php echo htmlspecialchars($contact['name']); ?></td>
                            <td><?php echo htmlspecialchars($contact['email']); ?></td>
                            <td><?php echo htmlspecialchars(substr($contact['message'], 0, 50)) . '...'; ?></td>
                            <td><?php echo htmlspecialchars($contact['created_at']); ?></td>
                            <td>
                                <a href="#view-contact-<?php echo $contact['id']; ?>" class="btn view-contact">View</a>
                                <a href="#delete-contact-<?php echo $contact['id']; ?>"
                                    class="btn delete-btn delete-contact">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Manage Podcasts -->
            <div class="admin-section" id="manage-podcasts">
                <h3>Manage Podcasts</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Host</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($podcasts_list as $podcast): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($podcast['id']); ?></td>
                            <td><?php echo htmlspecialchars($podcast['name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($podcast['description'] ?? '', 0, 50)) . (strlen($podcast['description'] ?? '') > 50 ? '...' : ''); ?>
                            </td>
                            <td><?php echo htmlspecialchars($podcast['host_name']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($podcast['status'])); ?></td>
                            <td>
                                <?php if ($podcast['status'] === 'pending'): ?>
                                <a href="#approve-podcast-<?php echo $podcast['id']; ?>"
                                    class="btn approve-btn approve-podcast">Approve</a>
                                <a href="#reject-podcast-<?php echo $podcast['id']; ?>"
                                    class="btn reject-btn reject-podcast">Reject</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Manage Tasks -->
            <div class="admin-section" id="manage-tasks">
                <h3>Manage Tasks</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Podcast</th>
                            <th>Staff</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks_list as $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['id']); ?></td>
                            <td><?php echo htmlspecialchars($task['podcast_name']); ?></td>
                            <td><?php echo htmlspecialchars($task['staff_names']); ?></td>
                            <td><?php echo htmlspecialchars(substr($task['description'], 0, 50)) . (strlen($task['description']) > 50 ? '...' : ''); ?>
                            </td>
                            <td><?php echo ucfirst(htmlspecialchars($task['status'])); ?></td>
                            <td><?php echo htmlspecialchars($task['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Logout -->
            <div class="logout-section">
                <form action="login.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Modals -->
    <!-- Add/Edit Staff Modal -->
    <div class="modal" id="staffModal">
        <div class="modal-content">
            <span class="close-modal" id="closeStaffModal">&times;</span>
            <h2 id="staffModalTitle">Add Staff</h2>
            <form id="staffForm" action="manage_staff.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="staff_id" id="staff_id">
                <div class="form-group">
                    <label for="staff_name">Name</label>
                    <input type="text" id="staff_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="staff_email">Email</label>
                    <input type="email" id="staff_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="staff_password">Password</label>
                    <input type="password" id="staff_password" name="password" required>
                </div>
                <button type="submit" class="submit-btn">Save</button>
            </form>
        </div>
    </div>
    <!-- Add/Edit Host Modal -->
    <div class="modal" id="hostModal">
        <div class="modal-content">
            <span class="close-modal" id="closeHostModal">&times;</span>
            <h2 id="hostModalTitle">Add Host</h2>
            <form id="hostForm" action="manage_host.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="host_id" id="host_id">
                <div class="form-group">
                    <label for="host_name">Name</label>
                    <input type="text" id="host_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="host_host_id">Host ID</label>
                    <input type="text" id="host_host_id" name="host_id" required>
                </div>
                <div class="form-group">
                    <label for="host_passcode">Passcode</label>
                    <input type="password" id="host_passcode" name="passcode" required>
                </div>
                <button type="submit" class="submit-btn">Save</button>
            </form>
        </div>
    </div>
    <!-- View Contact Modal -->
    <div class="modal" id="contactModal">
        <div class="modal-content">
            <span class="close-modal" id="closeContactModal">&times;</span>
            <h2>Contact Details</h2>
            <p id="contact_name"></p>
            <p id="contact_email"></p>
            <p id="contact_message"></p>
            <p id="contact_date"></p>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM fully loaded');

        // Sidebar toggle
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const closeBtn = document.getElementById('closeSidebar');
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('active');
            hamburger.classList.remove('active');
        });
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !hamburger.contains(e
                    .target)) {
                sidebar.classList.remove('active');
                hamburger.classList.remove('active');
            }
        });

        // Modal functions
        const staffModal = document.getElementById('staffModal');
        const hostModal = document.getElementById('hostModal');
        const contactModal = document.getElementById('contactModal');
        const closeStaffModal = document.getElementById('closeStaffModal');
        const closeHostModal = document.getElementById('closeHostModal');
        const closeContactModal = document.getElementById('closeContactModal');

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }
        closeStaffModal.addEventListener('click', () => closeModal('staffModal'));
        closeHostModal.addEventListener('click', () => closeModal('hostModal'));
        closeContactModal.addEventListener('click', () => closeModal('contactModal'));
        window.addEventListener('click', (e) => {
            if (e.target === staffModal) closeModal('staffModal');
            if (e.target === hostModal) closeModal('hostModal');
            if (e.target === contactModal) closeModal('contactModal');
        });

        // Add Staff
        document.getElementById('addStaffBtn').addEventListener('click', () => {
            document.getElementById('staffModalTitle').textContent = 'Add Staff';
            document.getElementById('staffForm').action = 'manage_staff.php';
            document.getElementById('staffForm').querySelector('input[name="action"]').value = 'add';
            document.getElementById('staff_id').value = '';
            document.getElementById('staff_name').value = '';
            document.getElementById('staff_email').value = '';
            document.getElementById('staff_password').value = '';
            staffModal.style.display = 'flex';
        });

        // Edit Staff
        document.querySelectorAll('.edit-staff').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const row = e.target.closest('tr');
                document.getElementById('staffModalTitle').textContent = 'Edit Staff';
                document.getElementById('staffForm').action = 'manage_staff.php';
                document.getElementById('staffForm').querySelector('input[name="action"]')
                    .value = 'edit';
                document.getElementById('staff_id').value = row.cells[0].textContent;
                document.getElementById('staff_name').value = row.cells[1].textContent;
                document.getElementById('staff_email').value = row.cells[2].textContent;
                document.getElementById('staff_password').value = '';
                staffModal.style.display = 'flex';
            });
        });

        // Delete Staff
        document.querySelectorAll('.delete-staff').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this staff member?')) {
                    const form = document.createElement('form');
                    form.action = 'manage_staff.php';
                    form.method = 'POST';
                    form.style.display = 'none';
                    form.innerHTML = `
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="staff_id" value="${e.target.closest('tr').cells[0].textContent}">
                        `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Add Host
        document.getElementById('addHostBtn').addEventListener('click', () => {
            document.getElementById('hostModalTitle').textContent = 'Add Host';
            document.getElementById('hostForm').action = 'manage_host.php';
            document.getElementById('hostForm').querySelector('input[name="action"]').value = 'add';
            document.getElementById('host_id').value = '';
            document.getElementById('host_name').value = '';
            document.getElementById('host_host_id').value = '';
            document.getElementById('host_passcode').value = '';
            hostModal.style.display = 'flex';
        });

        // Edit Host
        document.querySelectorAll('.edit-host').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const row = e.target.closest('tr');
                document.getElementById('hostModalTitle').textContent = 'Edit Host';
                document.getElementById('hostForm').action = 'manage_host.php';
                document.getElementById('hostForm').querySelector('input[name="action"]')
                    .value = 'edit';
                document.getElementById('host_id').value = row.cells[0].textContent;
                document.getElementById('host_name').value = row.cells[1].textContent;
                document.getElementById('host_host_id').value = row.cells[2].textContent;
                document.getElementById('host_passcode').value = '';
                hostModal.style.display = 'flex';
            });
        });

        // Delete Host
        document.querySelectorAll('.delete-host').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this host?')) {
                    const form = document.createElement('form');
                    form.action = 'manage_host.php';
                    form.method = 'POST';
                    form.style.display = 'none';
                    form.innerHTML = `
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="host_id" value="${e.target.closest('tr').cells[0].textContent}">
                        `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // View Contact
        document.querySelectorAll('.view-contact').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const row = e.target.closest('tr');
                document.getElementById('contact_name').textContent = 'Name: ' + row.cells[1]
                    .textContent;
                document.getElementById('contact_email').textContent = 'Email: ' + row.cells[2]
                    .textContent;
                document.getElementById('contact_message').textContent = 'Message: ' + row
                    .cells[3].textContent.replace('...', '');
                document.getElementById('contact_date').textContent = 'Date: ' + row.cells[4]
                    .textContent;
                contactModal.style.display = 'flex';
            });
        });

        // Delete Contact
        document.querySelectorAll('.delete-contact').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this contact?')) {
                    const form = document.createElement('form');
                    form.action = 'manage_contact.php';
                    form.method = 'POST';
                    form.style.display = 'none';
                    form.innerHTML = `
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="contact_id" value="${e.target.closest('tr').cells[0].textContent}">
                        `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Approve Podcast
        document.querySelectorAll('.approve-podcast').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Are you sure you want to approve this podcast?')) {
                    const form = document.createElement('form');
                    form.action = 'manage_podcast_admin.php';
                    form.method = 'POST';
                    form.style.display = 'none';
                    form.innerHTML = `
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="podcast_id" value="${e.target.closest('tr').cells[0].textContent}">
                        `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Reject Podcast
        document.querySelectorAll('.reject-podcast').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Are you sure you want to reject this podcast?')) {
                    const form = document.createElement('form');
                    form.action = 'manage_podcast_admin.php';
                    form.method = 'POST';
                    form.style.display = 'none';
                    form.innerHTML = `
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="podcast_id" value="${e.target.closest('tr').cells[0].textContent}">
                        `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
    </script>
</body>

</html>
<?php $conn->close(); ?>