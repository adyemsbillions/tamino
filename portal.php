<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tamino ETV</title>
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

        .user-type-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 30px;
        }

        .user-type-btn {
            padding: 12px 16px;
            border: 2px solid #FF6200;
            background: #FFFFFF;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #1A1A1A;
            text-align: center;
            transition: all 0.3s ease;
        }

        .user-type-btn:hover {
            background: #FF6200;
            color: #FFFFFF;
            transform: scale(1.05);
        }

        .user-type-btn.active {
            background: #FF6200;
            color: #FFFFFF;
            border-color: #FFC107;
            box-shadow: 0 4px 12px rgba(255, 98, 0, 0.3);
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e5e5;
            border-radius: 10px;
            font-size: 16px;
            color: #1A1A1A;
            background: #F9F9F9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
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

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
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

        .host-type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .host-type-btn {
            padding: 10px 16px;
            border: 2px solid #FFC107;
            background: #FFFFFF;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #1A1A1A;
            text-align: center;
            transition: all 0.3s ease;
        }

        .host-type-btn:hover {
            background: #FFC107;
            color: #1A1A1A;
            transform: scale(1.05);
        }

        .host-type-btn.active {
            background: #FFC107;
            color: #1A1A1A;
            border-color: #FF6200;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>TAMINO ETV</h1>
                <p>Access your account</p>
            </div>

            <!-- User Type Selection -->
            <div class="user-type-selector">
                <button class="user-type-btn active" data-type="staff">Staff</button>
                <button class="user-type-btn" data-type="admin">Admin</button>
                <button class="user-type-btn" data-type="guest">Guest</button>
                <button class="user-type-btn" data-type="host">Host</button>
            </div>

            <!-- Staff Login Form -->
            <div class="form-section active" id="staffForm">
                <form>
                    <div class="form-group">
                        <label for="staffEmail">Email Address</label>
                        <input type="email" id="staffEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="staffPassword">Password</label>
                        <input type="password" id="staffPassword" required>
                    </div>
                    <button type="submit" class="login-btn">Login as Staff</button>
                </form>
            </div>

            <!-- Admin Login Form -->
            <div class="form-section" id="adminForm">
                <form>
                    <div class="form-group">
                        <label for="adminEmail">Admin Email</label>
                        <input type="email" id="adminEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="adminPassword">Admin Password</label>
                        <input type="password" id="adminPassword" required>
                    </div>
                    <button type="submit" class="login-btn">Login as Admin</button>
                </form>
            </div>

            <!-- Guest Login Form -->
            <div class="form-section" id="guestForm">
                <form>
                    <div class="form-group">
                        <label for="sessionCode">Session Code</label>
                        <input type="text" id="sessionCode" placeholder="Enter session code" required>
                    </div>
                    <button type="submit" class="login-btn">Join as Guest</button>
                </form>
            </div>

            <!-- Host Login Form -->
            <div class="form-section" id="hostForm">
                <div class="host-type-selector">
                    <button type="button" class="host-type-btn active" data-host-type="podcast">Podcast</button>
                    <button type="button" class="host-type-btn" data-host-type="film">Film</button>
                </div>

                <form>
                    <div class="form-group">
                        <label for="hostId" id="hostIdLabel">Podcast ID</label>
                        <input type="text" id="hostId" placeholder="Enter podcast ID" required>
                    </div>
                    <div class="form-group">
                        <label for="hostPasscode">Passcode</label>
                        <input type="password" id="hostPasscode" placeholder="Enter passcode from admin" required>
                    </div>
                    <button type="submit" class="login-btn">Login as Host</button>
                </form>
            </div>

            <div class="back-link">
                <a href="index.html">← Back to Home</a>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
</body>

</html>