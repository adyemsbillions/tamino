<?php
session_start();
require_once 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php?error=' . urlencode('Please log in to access the dashboard'));
    exit;
}

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch staff list
$stmt = $conn->prepare("SELECT id, name, account_name, bank_name, account_number FROM staff");
$stmt->execute();
$result = $stmt->get_result();
$staff_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Location: admin_dash.php?error=' . urlencode('Invalid CSRF token'));
        exit;
    }

    // Validate input data
    $payment_type = $_POST['payment_type'] ?? '';
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $staff_id = isset($_POST['staff_id']) ? intval($_POST['staff_id']) : 0;

    // Validate payment type
    $valid_payment_types = ['Logistics', 'Salary', 'Others'];
    if (!in_array($payment_type, $valid_payment_types)) {
        header('Location: admin_dash.php?error=' . urlencode('Invalid payment type'));
        exit;
    }

    // Validate amount
    if ($amount <= 0 || !is_numeric($_POST['amount'])) {
        header('Location: admin_dash.php?error=' . urlencode('Invalid amount'));
        exit;
    }

    // Validate staff ID and fetch staff details
    if ($staff_id <= 0) {
        header('Location: admin_dash.php?error=' . urlencode('Invalid staff selection'));
        exit;
    }

    $stmt = $conn->prepare("SELECT account_name, bank_name, account_number FROM staff WHERE id = ?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
    $stmt->close();

    if (!$staff) {
        header('Location: admin_dash.php?error=' . urlencode('Staff member not found'));
        exit;
    }

    // Check if staff has complete bank details
    if (empty($staff['account_name']) || empty($staff['bank_name']) || empty($staff['account_number'])) {
        header('Location: admin_dash.php?error=' . urlencode('Staff member has incomplete bank details'));
        exit;
    }

    // Insert payment record
    $stmt = $conn->prepare("INSERT INTO payments (staff_id, amount, payment_type, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("ids", $staff_id, $amount, $payment_type);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        header('Location: admin_dash.php?success=' . urlencode('Payment recorded successfully'));
    } else {
        header('Location: admin_dash.php?error=' . urlencode('Failed to record payment'));
    }
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - Tamino ETV</title>
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
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        max-width: 500px;
        width: 100%;
        background: #FFFFFF;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        color: #1A1A1A;
    }

    h2 {
        font-size: 1.5rem;
        color: #FF6200;
        margin-bottom: 15px;
        text-align: center;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        font-size: 0.85rem;
        color: #1A1A1A;
        margin-bottom: 5px;
        font-weight: 600;
    }

    input,
    select {
        width: 100%;
        padding: 10px;
        border: 2px solid #e5e5e5;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #1A1A1A;
        background: #F9F9F9;
        transition: border-color 0.3s ease;
    }

    input:focus,
    select:focus {
        outline: none;
        border-color: #FF6200;
    }

    .account-details {
        margin-bottom: 15px;
        font-size: 0.85rem;
        color: #1A1A1A;
        background: #F9F9F9;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #e5e5e5;
        display: none;
    }

    .account-details p {
        margin-bottom: 5px;
    }

    .next-btn,
    .submit-btn {
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .next-btn {
        background: #28a745;
        color: #FFFFFF;
        margin-top: 10px;
        display: none;
    }

    .next-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 128, 0, 0.4);
    }

    .submit-btn {
        background: linear-gradient(135deg, #FF6200, #FFC107);
        color: #FFFFFF;
        display: none;
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(255, 98, 0, 0.4);
    }

    .back-btn {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: #FF6200;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .back-btn:hover {
        text-decoration: underline;
    }

    .error-message,
    .success-message {
        display: none;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 15px;
        text-align: center;
        font-size: 0.9rem;
    }

    .error-message {
        background: #FF6200;
        color: #FFFFFF;
    }

    .success-message {
        background: #FFC107;
        color: #FFFFFF;
    }

    @media (max-width: 480px) {
        .container {
            width: 95%;
            padding: 15px;
        }

        h2 {
            font-size: 1.3rem;
        }

        input,
        select {
            font-size: 0.85rem;
            padding: 8px;
        }

        .next-btn,
        .submit-btn {
            font-size: 0.85rem;
            padding: 8px;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <h2>Make Payment</h2>
        <div class="error-message" id="errorMessage">
            <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>
        </div>
        <div class="success-message" id="successMessage">
            <?php echo isset($_GET['success']) ? htmlspecialchars($_GET['success']) : ''; ?>
        </div>
        <form id="paymentForm" action="make_payment.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="payment_type">Payment Type</label>
                <select id="payment_type" name="payment_type" required>
                    <option value="">Select Payment Type</option>
                    <option value="Logistics">Logistics</option>
                    <option value="Salary">Salary</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="staff_id">Select Staff</label>
                <select id="staff_id" name="staff_id" required>
                    <option value="">Select Staff</option>
                    <?php foreach ($staff_list as $staff): ?>
                    <option value="<?php echo htmlspecialchars($staff['id']); ?>"
                        data-account-name="<?php echo htmlspecialchars($staff['account_name'] ?? 'N/A'); ?>"
                        data-bank-name="<?php echo htmlspecialchars($staff['bank_name'] ?? 'N/A'); ?>"
                        data-account-number="<?php echo htmlspecialchars($staff['account_number'] ?? 'N/A'); ?>">
                        <?php echo htmlspecialchars($staff['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="account-details" id="accountDetails">
                <p id="accountName"></p>
                <p id="bankName"></p>
                <p id="accountNumber"></p>
            </div>
            <button type="button" class="next-btn" id="nextBtn">Next</button>
            <button type="submit" class="submit-btn" id="payBtn">Pay</button>
        </form>
        <a href="admin_dash.php" class="back-btn">Back to Dashboard</a>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM fully loaded');

        // Show/hide messages
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        if (errorMessage.textContent.trim()) errorMessage.style.display = 'block';
        if (successMessage.textContent.trim()) successMessage.style.display = 'block';

        // Show Next button on staff selection
        const staffSelect = document.getElementById('staff_id');
        const nextBtn = document.getElementById('nextBtn');
        const payBtn = document.getElementById('payBtn');
        const accountDetails = document.getElementById('accountDetails');

        staffSelect.addEventListener('change', () => {
            console.log('staff_id change event triggered, value:', staffSelect.value);
            if (staffSelect.value !== '') {
                console.log('Staff selected, showing Next button');
                nextBtn.style.display = 'block';
                payBtn.style.display = 'none';
                accountDetails.style.display = 'none';
            } else {
                console.log('No staff selected, hiding buttons and details');
                nextBtn.style.display = 'none';
                payBtn.style.display = 'none';
                accountDetails.style.display = 'none';
            }
        });

        // Show account details and Pay button on Next click
        nextBtn.addEventListener('click', () => {
            const staffId = staffSelect.value;
            const selectedOption = staffSelect.options[staffSelect.selectedIndex];
            console.log('Next button clicked, Staff ID:', staffId);
            if (staffId === '') {
                console.log('Validation failed: No staff selected');
                alert('Please select a staff member.');
                return;
            }
            console.log('Selected staff text:', selectedOption.text);
            console.log('Account Name:', selectedOption.getAttribute('data-account-name'));
            console.log('Bank Name:', selectedOption.getAttribute('data-bank-name'));
            console.log('Account Number:', selectedOption.getAttribute('data-account-number'));
            document.getElementById('accountName').textContent = 'Account Name: ' + (selectedOption
                .getAttribute('data-account-name') || 'N/A');
            document.getElementById('bankName').textContent = 'Bank Name: ' + (selectedOption
                .getAttribute('data-bank-name') || 'N/A');
            document.getElementById('accountNumber').textContent = 'Account Number: ' + (selectedOption
                .getAttribute('data-account-number') || 'N/A');
            accountDetails.style.display = 'block';
            nextBtn.style.display = 'none';
            payBtn.style.display = 'block';
        });

        // Client-side form validation
        document.getElementById('paymentForm').addEventListener('submit', (e) => {
            const paymentType = document.getElementById('payment_type').value;
            const amount = parseFloat(document.getElementById('amount').value);
            const staffId = staffSelect.value;
            console.log('Form submitted', {
                paymentType,
                amount,
                staffId
            });
            if (!paymentType) {
                console.log('Validation failed: Payment Type is empty');
                e.preventDefault();
                alert('Please select a valid payment type.');
                return;
            }
            if (!amount || isNaN(amount) || amount <= 0) {
                console.log('Validation failed: Amount is invalid', amount);
                e.preventDefault();
                alert('Please enter a valid amount greater than 0.');
                return;
            }
            if (staffId === '') {
                console.log('Validation failed: Staff ID is empty');
                e.preventDefault();
                alert('Please select a staff member.');
                return;
            }
        });
    });
    </script>
</body>

</html>
<?php $conn->close(); ?>