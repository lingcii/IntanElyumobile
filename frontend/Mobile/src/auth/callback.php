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
    <link rel="stylesheet" href="../assets/css/views/callback.css?v=<?= time() ?>">
</head>
<body>

<?php if ($error): ?>
    <div class="error-icon">❌</div>
    <div>
        <p class="msg">Sign-in Failed</p>
        <p class="sub"><?= htmlspecialchars($error) ?></p>
    </div>
    <button class="btn" onclick="window.location.href='../index.php?view=auth'">Try Again</button>

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
                localStorage.setItem('intan_elyu_token', token);
                localStorage.setItem('Intan_Elyu_Token', token); // Fallback for case sensitivity used in app
                localStorage.setItem('auth_user', user);

                // Parse user to show name if possible
                const userData = JSON.parse(user);
                if (userData && userData.name) {
                    document.querySelector('.msg').textContent = 'Welcome, ' + userData.name + '!';
                }

                // Redirect to the main app dashboard
                setTimeout(() => {
                    window.location.href = '../index.php?view=dashboard';
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
    <button class="btn" onclick="window.location.href='../index.php?view=auth'">Go Back</button>
<?php endif; ?>

</body>
</html>
