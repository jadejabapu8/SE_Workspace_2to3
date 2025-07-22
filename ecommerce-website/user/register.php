<?php
require_once '../includes/config.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $agree_terms = isset($_POST['agree_terms']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!$agree_terms) {
        $error = 'Please agree to the terms and conditions';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists';
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$first_name, $last_name, $email, $hashed_password, $phone])) {
                $success = 'Account created successfully! You can now log in.';
                // Clear form data
                $first_name = $last_name = $email = $phone = '';
            } else {
                $error = 'Failed to create account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - ShopHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-container" style="max-width: 600px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <a href="../index.php" class="logo">
                    <i class="fas fa-shopping-bag"></i> ShopHub
                </a>
                <h2 style="margin-top: 20px; color: #2c3e50;">Create Account</h2>
                <p style="color: #666;">Join our community and start shopping</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    <div style="margin-top: 10px;">
                        <a href="login.php" class="btn btn-small">Sign In Now</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" data-validate>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" 
                               value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" 
                               value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                           placeholder="(555) 123-4567">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <small style="color: #666; font-size: 12px;">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                
                <div style="margin: 20px 0;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; line-height: 1.4;">
                        <input type="checkbox" name="agree_terms" style="margin-top: 4px;" required>
                        <span style="font-size: 14px;">
                            I agree to the <a href="../terms.php" style="color: #e74c3c;">Terms of Service</a> 
                            and <a href="../privacy.php" style="color: #e74c3c;">Privacy Policy</a>
                        </span>
                    </label>
                </div>
                
                <div style="margin: 20px 0;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="newsletter">
                        <span style="font-size: 14px;">Subscribe to our newsletter for exclusive deals</span>
                    </label>
                </div>
                
                <button type="submit" class="btn" style="width: 100%; margin-bottom: 20px;">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div style="text-align: center; padding-top: 20px; border-top: 1px solid #eee;">
                <p style="color: #666; margin-bottom: 15px;">Already have an account?</p>
                <a href="login.php" class="btn btn-outline" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </a>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="../index.php" style="color: #666; text-decoration: none; font-size: 14px;">
                    <i class="fas fa-arrow-left"></i> Back to Store
                </a>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = getPasswordStrength(password);
            
            // Remove existing strength indicator
            const existingIndicator = this.parentNode.querySelector('.password-strength');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            // Add new strength indicator
            if (password.length > 0) {
                const indicator = document.createElement('div');
                indicator.className = 'password-strength';
                indicator.style.cssText = `
                    margin-top: 5px;
                    padding: 5px;
                    border-radius: 3px;
                    font-size: 12px;
                    text-align: center;
                `;
                
                switch (strength) {
                    case 'weak':
                        indicator.style.background = '#f8d7da';
                        indicator.style.color = '#721c24';
                        indicator.textContent = 'Weak password';
                        break;
                    case 'medium':
                        indicator.style.background = '#fff3cd';
                        indicator.style.color = '#856404';
                        indicator.textContent = 'Medium strength';
                        break;
                    case 'strong':
                        indicator.style.background = '#d4edda';
                        indicator.style.color = '#155724';
                        indicator.textContent = 'Strong password';
                        break;
                }
                
                this.parentNode.appendChild(indicator);
            }
        });
        
        function getPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            if (strength < 3) return 'weak';
            if (strength < 4) return 'medium';
            return 'strong';
        }
    </script>
</body>
</html>