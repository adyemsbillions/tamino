<?php
session_start();

// Generate CSRF token for the signup form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Tamino ETV</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1A1A1A 0%, #333333 100%);
            padding: 20px;
        }

        .login-card {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #1A1A1A;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .login-header p {
            color: #666666;
            font-size: 16px;
            margin-top: 8px;
        }

        .error-message,
        .success-message {
            display: <?php echo isset($_GET['error']) ? 'block' : (isset($_GET['success']) ? 'block' : 'none');
                        ?>;
            background: <?php echo isset($_GET['error']) ? '#FF6200' : '#FFC107';
                        ?>;
            color: #FFFFFF;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1A1A1A;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e5e5;
            border-radius: 10px;
            font-size: 16px;
            color: #1A1A1A;
            background: #F9F9F9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #FF6200;
            box-shadow: 0 0 8px rgba(255, 98, 0, 0.2);
        }

        .login-btn,
        .signup-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #FF6200, #FFC107);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }

        .login-btn:hover,
        .signup-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(255, 98, 0, 0.4);
        }

        .login-btn:disabled,
        .signup-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
        }

        .signup-link a {
            color: #FFC107;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #FF6200;
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #FFC107;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #FF6200;
            text-decoration: underline;
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
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 20px;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .modal-content h2 {
            font-size: 1.5rem;
            color: #FF6200;
            margin-bottom: 15px;
            text-align: center;
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

        .modal-content input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #1A1A1A;
            background: #F9F9F9;
            transition: border-color 0.3s ease;
        }

        .modal-content input:focus {
            outline: none;
            border-color: #FF6200;
        }

        .modal-content .signup-btn {
            font-size: 0.9rem;
            padding: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-card {
                padding: 30px;
                max-width: 90%;
            }

            .login-header h1 {
                font-size: 28px;
            }

            .login-header p {
                font-size: 14px;
            }

            .form-group input {
                font-size: 14px;
                padding: 10px 14px;
            }

            .login-btn,
            .signup-btn {
                font-size: 14px;
                padding: 12px;
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

            .modal-content input {
                font-size: 0.85rem;
                padding: 8px;
            }

            .modal-content .signup-btn {
                font-size: 0.85rem;
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .login-header p {
                font-size: 12px;
            }

            .form-group label {
                font-size: 12px;
            }

            .form-group input {
                font-size: 12px;
                padding: 8px 12px;
            }

            .login-btn,
            .signup-btn {
                font-size: 12px;
                padding: 10px;
            }

            .modal-content {
                width: 98%;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>TAMINO ETV</h1>
                <p>Admin Login</p>
            </div>

            <div class="error-message"><?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>
            </div>
            <div class="success-message">
                <?php echo isset($_GET['success']) ? htmlspecialchars($_GET['success']) : ''; ?></div>

            <form action="login.php" method="POST">
                <input type="hidden" name="userType" value="admin">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="adminEmail">Admin Email</label>
                    <input type="email" id="adminEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="adminPassword">Admin Password</label>
                    <input type="password" id="adminPassword" name="password" required>
                </div>
                <button type="submit" class="login-btn">Login as Admin</button>
            </form>

            <div class="signup-link">
                <a href="#" id="openSignupModal">Create Admin Account</a>
            </div>

            <div class="back-link">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal" id="signupModal">
        <div class="modal-content">
            <span class="close-modal" id="closeSignupModal">&times;</span>
            <h2>Create Admin Account</h2>
            <form action="login.php" method="POST">
                <input type="hidden" name="userType" value="admin">
                <input type="hidden" name="action" value="signup">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label for="signupName">Name</label>
                    <input type="text" id="signupName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="signupEmail">Email</label>
                    <input type="email" id="signupEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="signupPassword">Password</label>
                    <input type="password" id="signupPassword" name="password" required>
                </div>
                <button type="submit" class="signup-btn">Create Account</button>
            </form>
        </div>
    </div>

    <script>
        // Modal toggle
        const signupModal = document.getElementById('signupModal');
        const openSignupModal = document.getElementById('openSignupModal');
        const closeSignupModal = document.getElementById('closeSignupModal');

        openSignupModal.addEventListener('click', (e) => {
            e.preventDefault();
            signupModal.style.display = 'flex';
        });

        closeSignupModal.addEventListener('click', () => {
            signupModal.style.display = 'none';
        });

        // Close modal when clicking outside
        signupModal.addEventListener('click', (e) => {
            if (e.target === signupModal) {
                signupModal.style.display = 'none';
            }
        });
    </script>
</body>

</html>