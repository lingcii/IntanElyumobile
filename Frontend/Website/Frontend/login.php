<?php
session_start();
// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? '';
    if ($role === 'pitco') {
        $role = 'picto';
    }
    
    if ($role === 'picto') {
        $redirectUrl = 'views/PICTO/dashboard.php';
    } elseif ($role === 'lupto') {
        $redirectUrl = 'views/LUPTO/dashboard.php';
    } elseif ($role === 'municipal' || str_ends_with($role, '_mto')) {
        $redirectUrl = 'views/MUNICIPAL/dashboard.php';
    } else {
        $redirectUrl = 'views/LUPTO/dashboard.php';
    }
    header('Location: ' . $redirectUrl);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INTAN ELYU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/components/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <img src="images/logo.png" alt="LOGO">
            </div>
            <h1>INTAN ELYU </h1>
            <p>San Fernando, La Union</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php
                $errors = [
                    'empty_fields' => 'Please fill in all fields.',
                    'invalid_credentials' => 'Invalid email or password.',
                    'db_error' => 'Database error occurred.',
                    'unauthorized' => 'You are not authorized to access this page.'
                ];
                echo $errors[$_GET['error']] ?? 'An error occurred.';
                ?>
            </div>
        <?php endif; ?>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-user"></i>
                    Email or Name
                </label>
                <input type="text" id="email" name="email" required placeholder="Enter your email or name">
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                    <button type="button" id="togglePassword" class="toggle-btn">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div id="successMessage" class="alert alert-success" style="display: none;"></div>
            <div id="errorMessage" class="alert alert-error" style="display: none;"></div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>
        </form>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Login Successful!</h2>
            <p>Redirecting you to the dashboard...</p>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon error">
                <i class="fas fa-times-circle"></i>
            </div>
            <h2 id="errorModalTitle">Login Failed</h2>
            <p id="errorModalText">Invalid email or password.</p>
            <button type="button" id="closeErrorModal" class="btn-close">Try Again</button>
        </div>
    </div>
    <script src="scripts/api-config.js"></script>
    <script src="scripts/login.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            togglePassword.querySelector('i').className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
    </script>
</body>
</html>
