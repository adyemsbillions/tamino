<?php
session_start();
require_once 'db_connect.php';

// Check if staff is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: staff_login.php?error=' . urlencode('Please log in to access the dashboard'));
    exit;
}

// Fetch staff details
$stmt = $conn->prepare("SELECT name, account_name, bank_name, account_number FROM staff WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
$staff_name = $staff['name'] ?? 'Staff Member';
$account_name = $staff['account_name'] ?? '';
$bank_name = $staff['bank_name'] ?? '';
$account_number = $staff['account_number'] ?? '';
$stmt->close();

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// List of 50 banks
$banks = [
    'Access Bank Plc',
    'Afriland First Bank',
    'ALAT by Wema',
    'ASV Savings',
    'Baxi',
    'CBN',
    'Citibank Nigeria Limited',
    'Ecobank Nigeria Plc',
    'Fidelity Bank Plc',
    'First Bank of Nigeria Limited',
    'First City Monument Bank Limited',
    'First Trust Mortgage Bank',
    'FSDH Merchant Bank',
    'GTBank Plc',
    'Heritage Bank Plc',
    'Keystone Bank Limited',
    'Kuda Bank',
    'Moniepoint Microfinance Bank',
    'Opay',
    'Palmpay',
    'Paragon Trust Bank',
    'Payhippo',
    'Polaris Bank',
    'Providus Bank Limited',
    'Quantum Trust Merchant Bank',
    'Rand Merchant Bank',
    'Reliance MFB',
    'Sterling Bank Plc',
    'Standard Chartered Bank Nigeria Limited',
    'Stanbic IBTC Bank Plc',
    'Titan Trust Bank Limited',
    'Union Bank of Nigeria Plc',
    'United Bank for Africa Plc',
    'Unity Bank Plc',
    'VFD Microfinance Bank',
    'Wema Bank Plc',
    'Zenith Bank Plc',
    'Access Bank Diamond',
    'Diamond Bank (Legacy)',
    'FCMB (Legacy)',
    'Intercontinental Bank (Legacy)',
    'Mainstreet Bank (Legacy)',
    'Oceanic Bank (Legacy)',
    'Skye Bank (Legacy)',
    'Spring Bank (Legacy)',
    'Unity Bank (Legacy)',
    'Zenith Direct (Legacy)',
    'First Marina Trust',
    'FBN Merchant Bank',
    'Globus Bank',
    'Suntrust Bank'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Tamino ETV</title>
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

        /* Sidebar */
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

        /* Hamburger */
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

        /* Main Content */
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

        /* Message Display */
        .message {
            display: <?php echo isset($_GET['error']) || isset($_GET['success']) ? 'block' : 'none';
                        ?>;
            background: <?php echo isset($_GET['error']) ? '#FF6200' : '#FFC107';
                        ?>;
            color: #FFFFFF;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Bank Details Section */
        .bank-details {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .bank-details h3 {
            font-size: 1.2rem;
            color: #FF6200;
            margin-bottom: 10px;
        }

        .bank-details p {
            font-size: 0.85rem;
            color: #666666;
            margin-bottom: 8px;
        }

        .bank-details .btn {
            padding: 8px 16px;
            background: linear-gradient(135deg, #FF6200, #FFC107);
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }

        .bank-details .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(255, 98, 0, 0.4);
        }

        /* Modal */
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
        .modal-content select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #1A1A1A;
            background: #F9F9F9;
            transition: border-color 0.3s ease;
        }

        .modal-content input:focus,
        .modal-content select:focus {
            outline: none;
            border-color: #FF6200;
        }

        #otherBankInput {
            display: none;
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

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
        }

        .dashboard-card {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .dashboard-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .dashboard-card h3 {
            font-size: 1.2rem;
            color: #FF6200;
            margin-bottom: 8px;
        }

        .dashboard-card p {
            font-size: 0.85rem;
            color: #666666;
            margin-bottom: 12px;
        }

        .dashboard-card .btn {
            display: block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #FF6200, #FFC107);
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
            touch-action: manipulation;
        }

        .dashboard-card .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(255, 98, 0, 0.4);
        }

        /* Logout Section */
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

        /* Responsive Design */
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

            .dashboard-header h1 {
                font-size: 1.5rem;
            }

            .dashboard-header p {
                font-size: 0.8rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-card img {
                height: 100px;
            }

            .dashboard-card h3 {
                font-size: 1.1rem;
            }

            .dashboard-card p {
                font-size: 0.8rem;
            }

            .dashboard-card .btn {
                font-size: 0.85rem;
                padding: 8px 16px;
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
            .modal-content select {
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

            .dashboard-card {
                padding: 12px;
            }

            .modal-content {
                width: 98%;
            }
        }
    </style>
</head>

<body>
    <!-- Hamburger for mobile -->
    <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <span class="close-btn" id="closeSidebar">&times;</span>
        <div class="sidebar-header">
            <h2>TAMINO ETV</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="#manage-podcasts">Manage Podcasts</a></li>
            <li><a href="#view-schedule">View Schedule</a></li>
            <li><a href="#academy-resources">Academy Resources</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="message">
                <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : (isset($_GET['success']) ? htmlspecialchars($_GET['success']) : ''); ?>
            </div>

            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($staff_name); ?>!</h1>
                <p>Manage your podcasting tasks and resources</p>
            </div>

            <!-- Bank Details Section -->
            <div class="bank-details">
                <h3>Bank Account Details</h3>
                <?php if ($account_name && $bank_name && $account_number): ?>
                    <p><strong>Account Name:</strong> <?php echo htmlspecialchars($account_name); ?></p>
                    <p><strong>Bank Name:</strong> <?php echo htmlspecialchars($bank_name); ?></p>
                    <p><strong>Account Number:</strong> <?php echo htmlspecialchars($account_number); ?></p>
                <?php else: ?>
                    <p>No bank account details provided.</p>
                <?php endif; ?>
                <button class="btn" id="openBankModal">Update Bank Details</button>
            </div>

            <div class="dashboard-grid">
                <!-- Manage Podcasts -->
                <div class="dashboard-card">
                    <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=400&h=150&fit=crop&crop=center"
                        alt="Podcast studio">
                    <h3>Manage Podcasts</h3>
                    <p>Schedule recordings, edit episodes, and oversee production.</p>
                    <a href="#manage-podcasts" class="btn">Manage Now</a>
                </div>

                <!-- View Schedule -->
                <div class="dashboard-card">
                    <img src="https://plus.unsplash.com/premium_vector-1730908686651-cdc9c0804b4b?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8c2NoZWR1bGV8ZW58MHx8MHx8fDA%3D"
                        alt="Podcast schedule">
                    <h3>View Schedule</h3>
                    <p>Check upcoming studio bookings and recording sessions.</p>
                    <a href="#view-schedule" class="btn">View Schedule</a>
                </div>

                <!-- Academy Resources -->
                <div class="dashboard-card">
                    <img src="https://images.unsplash.com/photo-1593693397690-362cb9666fc2?w=400&h=150&fit=crop&crop=center"
                        alt="Podcast academy">
                    <h3>Academy Resources</h3>
                    <p>Access training materials and manage course schedules.</p>
                    <a href="#academy-resources" class="btn">Access Resources</a>
                </div>
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

    <!-- Modal -->
    <div class="modal" id="bankModal">
        <div class="modal-content">
            <span class="close-modal" id="closeBankModal">&times;</span>
            <h2>Update Bank Details</h2>
            <form action="update_bank_details.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label for="account_name">Account Name</label>
                    <input type="text" id="account_name" name="account_name"
                        value="<?php echo htmlspecialchars($account_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="bank_name_select">Bank Name</label>
                    <select id="bank_name_select" name="bank_name_select" onchange="toggleBankInput()">
                        <option value="">Select a bank</option>
                        <?php foreach ($banks as $bank): ?>
                            <option value="<?php echo htmlspecialchars($bank); ?>"
                                <?php echo $bank_name === $bank ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($bank); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="Other">Other</option>
                    </select>
                    <input type="text" id="otherBankInput" name="bank_name" placeholder="Enter bank name"
                        value="<?php echo !in_array($bank_name, $banks) && $bank_name ? htmlspecialchars($bank_name) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="account_number">Account Number</label>
                    <input type="text" id="account_number" name="account_number"
                        value="<?php echo htmlspecialchars($account_number); ?>" required>
                </div>
                <button type="submit" class="submit-btn">Save Details</button>
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

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !hamburger.contains(e.target)) {
                sidebar.classList.remove('active');
                hamburger.classList.remove('active');
            }
        });

        // Modal toggle
        const bankModal = document.getElementById('bankModal');
        const openBankModal = document.getElementById('openBankModal');
        const closeBankModal = document.getElementById('closeBankModal');

        openBankModal.addEventListener('click', () => {
            bankModal.style.display = 'flex';
            toggleBankInput(); // Initialize bank input visibility
        });

        closeBankModal.addEventListener('click', () => {
            bankModal.style.display = 'none';
        });

        // Close modal when clicking outside
        bankModal.addEventListener('click', (e) => {
            if (e.target === bankModal) {
                bankModal.style.display = 'none';
            }
        });

        // Toggle bank name input based on selection
        function toggleBankInput() {
            const bankSelect = document.getElementById('bank_name_select');
            const otherBankInput = document.getElementById('otherBankInput');
            otherBankInput.style.display = bankSelect.value === 'Other' ? 'block' : 'none';
            if (bankSelect.value !== 'Other') {
                otherBankInput.value = '';
            }
        }
    </script>
</body>

</html>
<?php $conn->close(); ?>