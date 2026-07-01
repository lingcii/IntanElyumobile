<?php
/**
 * Google OAuth Callback Handler
 * This file is loaded when Google redirects back after authentication.
 * It reads the token + user from the URL, saves them to sessionStorage,
 * then redirects to the main app dashboard.
 */
$token = isset($_GET['token']) ? $_GET['token'] : null;
$user  = isset($_GET['user'])  ? $_GET['user']  : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Signing in... — Intan Elyu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #F2F2F7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 20px;
            text-align: center;
            padding: 40px;
        }
        .spinner {
            width: 56px; height: 56px;
            border: 4px solid rgba(0,122,255,0.15);
            border-top-color: #007AFF;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .msg { font-size: 17px; font-weight: 600; color: #1C1C1E; }
        .sub { font-size: 14px; color: #8E8E93; margin-top: 6px; }
        .error-icon { font-size: 48px; }
        .btn {
            margin-top: 10px;
            padding: 14px 28px;
            background: #007AFF;
            color: white;
            border: none;
            border-radius: 100px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
    </style>
</head>
<body>

<?php if ($error): ?>
    <div class="error-icon">❌</div>
    <div>
        <p class="msg">Sign-in Failed</p>
        <p class="sub"><?= htmlspecialchars($error) ?></p>
    </div>
    <button class="btn" onclick="window.location.href='/?view=auth'">Try Again</button>

<?php elseif ($token && $user): ?>
    <div class="spinner"></div>
    <div>
        <p class="msg">Signing you in...</p>
        <p class="sub">Just a moment</p>
    </div>
    <script>
        (function() {
            try {
                const token = <?= json_encode($token) ?>;
                const user  = <?= json_encode($user) ?>;

                // Store auth data
                sessionStorage.setItem('auth_token', token);
                sessionStorage.setItem('auth_user', user);

                // Parse user to show name if possible
                const userData = JSON.parse(user);
                if (userData && userData.name) {
                    document.querySelector('.msg').textContent = 'Welcome, ' + userData.name + '!';
                }

                // Redirect to the main app dashboard
                setTimeout(() => {
                    window.location.href = '/?view=dashboard';
                }, 800);
            } catch(e) {
                document.querySelector('.msg').textContent = 'Something went wrong';
                document.querySelector('.sub').textContent = e.message;
            }
        })();
    </script>

<?php else: ?>
    <div class="error-icon">⚠️</div>
    <div>
        <p class="msg">Invalid Callback</p>
        <p class="sub">No authentication data received.</p>
    </div>
    <button class="btn" onclick="window.location.href='/?view=auth'">Go Back</button>
<?php endif; ?>

</body>
</html>
