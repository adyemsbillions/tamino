<?php
session_start();
require_once 'db_connect.php';

// Check if host is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'host') {
    header('Location: host_login.php?error=' . urlencode('Please log in to access the dashboard'));
    exit;
}

// Fetch host details
$stmt = $conn->prepare("SELECT name FROM hosts WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$host = $result->fetch_assoc();
$host_name = $host['name'] ?? 'Host User';
$stmt->close();

// Fetch host's podcasts
$stmt = $conn->prepare("SELECT id, name, description, status FROM podcasts WHERE host_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$podcasts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch assigned staff count for each podcast
$podcast_staff_count = [];
foreach ($podcasts as $podcast) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM podcast_staff WHERE podcast_id = ?");
    $stmt->bind_param("i", $podcast['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $podcast_staff_count[$podcast['id']] = $count;
    $stmt->close();
}

// Fetch tasks count for each podcast
$podcast_tasks_count = [];
foreach ($podcasts as $podcast) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM tasks WHERE podcast_id = ?");
    $stmt->bind_param("i", $podcast['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $podcast_tasks_count[$podcast['id']] = $count;
    $stmt->close();
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Dashboard - Tamino ETV</title>
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

    .host-section {
        background: #FFFFFF;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        color: #1A1A1A;
    }

    .host-section h3 {
        font-size: 1.2rem;
        color: #FF6200;
        margin-bottom: 10px;
    }

    .host-section table {
        width: 100%;
        border-collapse: collapse;
    }

    .host-section th,
    .host-section td {
        padding: 8px;
        border: 1px solid #e5e5e5;
        text-align: left;
        font-size: 0.85rem;
    }

    .host-section th {
        background: #F9F9F9;
        font-weight: 600;
    }

    .host-section .btn {
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

    .host-section .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 98, 0, 0.4);
    }

    .host-section .btn.disabled {
        background: #666666;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .host-section .delete-btn {
        background: #FF6200;
    }

    .host-section .add-btn {
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

        .host-section table {
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

        .host-section {
            padding: 12px;
        }

        .host-section th,
        .host-section td {
            padding: 6px;
        }

        .host-section .btn {
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
            <li><a href="#manage-podcasts">Manage Podcasts</a></li>
            <li><a href="#tasks">Tasks</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="message">
                <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : (isset($_GET['success']) ? htmlspecialchars($_GET['success']) : ''); ?>
            </div>

            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($host_name); ?>!</h1>
                <p>Manage your podcasts and staff assignments</p>
            </div>

            <div class="host-section" id="manage-podcasts">
                <h3>Manage Podcasts</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Assigned Staff</th>
                            <th>Tasks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($podcasts as $podcast): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($podcast['id']); ?></td>
                            <td><?php echo htmlspecialchars($podcast['name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($podcast['description'] ?? '', 0, 50)) . (strlen($podcast['description'] ?? '') > 50 ? '...' : ''); ?>
                            </td>
                            <td><?php echo ucfirst(htmlspecialchars($podcast['status'])); ?></td>
                            <td><?php echo $podcast_staff_count[$podcast['id']] ?? 0; ?></td>
                            <td><?php echo $podcast_tasks_count[$podcast['id']] ?? 0; ?></td>
                            <td>
                                <a href="podcast_details.php?id=<?php echo $podcast['id']; ?>"
                                    class="btn <?php echo $podcast['status'] !== 'approved' ? 'disabled' : ''; ?>"
                                    <?php echo $podcast['status'] !== 'approved' ? 'onclick="return false;"' : ''; ?>>View
                                    Details</a>
                                <a href="#edit-podcast-<?php echo $podcast['id']; ?>" class="btn edit-podcast"
                                    data-modal="podcastModal">Edit</a>
                                <a href="#delete-podcast-<?php echo $podcast['id']; ?>"
                                    class="btn delete-btn delete-podcast">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button class="add-btn" data-modal="podcastModal">Add New Podcast</button>
            </div>

            <div class="logout-section">
                <form action="login.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Podcast Modal -->
    <div class="modal" id="podcastModal">
        <div class="modal-content">
            <span class="close-modal" id="closePodcastModal">&times;</span>
            <h2 id="podcastModalTitle">Add Podcast</h2>
            <form id="podcastForm" action="manage_podcast.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="podcast_id" id="podcast_id">
                <div class="form-group">
                    <label for="podcast_name">Podcast Name</label>
                    <input type="text" id="podcast_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="podcast_description">Description</label>
                    <input type="text" id="podcast_description" name="description">
                </div>
                <button type="submit" class="submit-btn">Save</button>
            </form>
        </div>
    </div>

    <script>
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
        if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove('active');
            hamburger.classList.remove('active');
        }
    });

    // Modal handling
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Ensure modals are hidden on page load
    document.addEventListener('DOMContentLoaded', () => {
        ['podcastModal'].forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Open modals via buttons
    document.querySelectorAll('.btn[data-modal], .add-btn[data-modal]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const modalId = button.getAttribute('data-modal');
            const isEdit = button.classList.contains('edit-podcast');
            const modalTitle = document.getElementById('podcastModalTitle');
            const form = document.getElementById('podcastForm');
            const podcastIdInput = document.getElementById('podcast_id');
            const nameInput = document.getElementById('podcast_name');
            const descriptionInput = document.getElementById('podcast_description');

            if (isEdit) {
                const row = button.closest('tr');
                modalTitle.textContent = 'Edit Podcast';
                form.querySelector('input[name="action"]').value = 'edit';
                podcastIdInput.value = row.cells[0].textContent;
                nameInput.value = row.cells[1].textContent;
                descriptionInput.value = row.cells[2].textContent.trim().replace('...', '');
            } else {
                modalTitle.textContent = 'Add Podcast';
                form.querySelector('input[name="action"]').value = 'add';
                podcastIdInput.value = '';
                nameInput.value = '';
                descriptionInput.value = '';
            }
            openModal(modalId);
        });
    });

    // Close modals
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Close modals when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Delete Podcast
    document.querySelectorAll('.delete-podcast').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this podcast?')) {
                const form = document.createElement('form');
                form.action = 'manage_podcast.php';
                form.method = 'POST';
                form.style.display = 'none';
                form.innerHTML = `
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="podcast_id" value="${e.target.closest('tr').cells[0].textContent}">
                    `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
    </script>
</body>

</html>
<?php $conn->close(); ?>