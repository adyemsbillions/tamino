<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Login - Tamino ETV</title>
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

        .login-btn {
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

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(255, 98, 0, 0.4);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>TAMINO ETV</h1>
                <p>Host Login (Podcast)</p>
            </div>

            <div class="error-message"><?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>
            </div>
            <div class="success-message">
                <?php echo isset($_GET['success']) ? htmlspecialchars($_GET['success']) : ''; ?></div>

            <form action="login.php" method="POST">
                <input type="hidden" name="userType" value="host">
                <input type="hidden" name="hostType" value="podcast">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="hostId">Podcast ID</label>
                    <input type="text" id="hostId" name="host_id" placeholder="Enter podcast ID" required>
                </div>
                <div class="form-group">
                    <label for="hostPasscode">Passcode</label>
                    <input type="password" id="hostPasscode" name="passcode" placeholder="Enter passcode from admin"
                        required>
                </div>
                <button type="submit" class="login-btn">Login as Host</button>
            </form>

            <div class="back-link">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>
</body>

</html>