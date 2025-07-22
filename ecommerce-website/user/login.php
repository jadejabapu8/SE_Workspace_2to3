<?php
require_once '../includes/config.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirect to intended page or home
            $redirect_url = $_SESSION['redirect_after_login'] ?? '../index.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirect_url);
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShopHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div style="text-align: center; margin-bottom: 30px;">
                <a href="../index.php" class="logo">
                    <i class="fas fa-shopping-bag"></i> ShopHub
                </a>
                <h2 style="margin-top: 20px; color: #2c3e50;">Welcome Back</h2>
                <p style="color: #666;">Sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" data-validate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="remember" style="margin: 0;">
                        <span style="font-size: 14px;">Remember me</span>
                    </label>
                    <a href="forgot-password.php" style="color: #e74c3c; text-decoration: none; font-size: 14px;">
                        Forgot password?
                    </a>
                </div>
                
                <button type="submit" class="btn" style="width: 100%; margin-bottom: 20px;">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div style="text-align: center; padding-top: 20px; border-top: 1px solid #eee;">
                <p style="color: #666; margin-bottom: 15px;">Don't have an account?</p>
                <a href="register.php" class="btn btn-outline" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="../index.php" style="color: #666; text-decoration: none; font-size: 14px;">
                    <i class="fas fa-arrow-left"></i> Back to Store
                </a>
            </div>
        </div>
    </div>
    
    <!-- Demo credentials info -->
    <div style="position: fixed; bottom: 20px; right: 20px; background: #2c3e50; color: white; padding: 15px; border-radius: 10px; max-width: 300px; font-size: 14px;">
        <h4 style="margin: 0 0 10px 0; color: #e74c3c;">Demo Login</h4>
        <p style="margin: 5px 0;"><strong>Admin:</strong> admin@ecommerce.com</p>
        <p style="margin: 5px 0;"><strong>Password:</strong> password</p>
        <p style="margin: 5px 0; font-size: 12px; opacity: 0.8;">Use these credentials to test the admin panel</p>
    </div>
    
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Auto-hide demo credentials after 10 seconds
        setTimeout(() => {
            const demoBox = document.querySelector('[style*="position: fixed"]');
            if (demoBox) {
                demoBox.style.opacity = '0';
                demoBox.style.transition = 'opacity 0.5s';
                setTimeout(() => demoBox.remove(), 500);
            }
        }, 10000);
    </script>
</body>
</html>