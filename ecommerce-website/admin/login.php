<?php
require_once '../includes/config.php';

$error = '';
$success = '';

// Redirect if already logged in as admin
if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Check admin credentials (support both username and email)
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username/email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ShopHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        .admin-logo {
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-logo">
            <i class="fas fa-user-shield"></i>
        </div>
        <h1 style="color: #2c3e50; margin-bottom: 10px;">Admin Panel</h1>
        <p style="color: #666; margin-bottom: 30px;">Sign in to manage your store</p>
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb; text-align: left;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; text-align: left;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" data-validate style="text-align: left;">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <div style="position: relative;">
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                           style="padding-left: 45px;" required>
                    <i class="fas fa-user" style="position: absolute; left: 15px; top: 15px; color: #999;"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" 
                           style="padding-left: 45px;" required>
                    <i class="fas fa-lock" style="position: absolute; left: 15px; top: 15px; color: #999;"></i>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px;">
                    <input type="checkbox" name="remember" style="margin: 0;">
                    <span>Remember me</span>
                </label>
                <a href="forgot-password.php" style="color: #e74c3c; text-decoration: none; font-size: 14px;">
                    Forgot password?
                </a>
            </div>
            
            <button type="submit" class="btn" style="width: 100%; padding: 15px; font-size: 16px; margin-bottom: 20px;">
                <i class="fas fa-sign-in-alt"></i> Sign In to Admin Panel
            </button>
        </form>
        
        <div style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;">
            <a href="../index.php" style="color: #666; text-decoration: none; font-size: 14px;">
                <i class="fas fa-arrow-left"></i> Back to Website
            </a>
        </div>
    </div>
    
    <!-- Demo admin credentials -->
    <div style="position: fixed; bottom: 20px; left: 20px; background: rgba(0,0,0,0.8); color: white; padding: 15px; border-radius: 10px; max-width: 300px; font-size: 14px;">
        <h4 style="margin: 0 0 10px 0; color: #e74c3c;">Demo Admin Login</h4>
        <p style="margin: 5px 0;"><strong>Username:</strong> admin</p>
        <p style="margin: 5px 0;"><strong>Email:</strong> admin@ecommerce.com</p>
        <p style="margin: 5px 0;"><strong>Password:</strong> password</p>
        <p style="margin: 10px 0 0 0; font-size: 12px; opacity: 0.8;">Default admin credentials for testing</p>
    </div>
    
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Auto-hide demo credentials after 15 seconds
        setTimeout(() => {
            const demoBox = document.querySelector('[style*="position: fixed"]');
            if (demoBox) {
                demoBox.style.opacity = '0';
                demoBox.style.transition = 'opacity 0.5s';
                setTimeout(() => demoBox.remove(), 500);
            }
        }, 15000);
        
        // Focus on username field
        document.getElementById('username').focus();
    </script>
</body>
</html>