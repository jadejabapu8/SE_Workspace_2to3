<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Get dashboard statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
$stats['total_products'] = $stmt->fetch()['count'];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $stmt->fetch()['count'];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $stmt->fetch()['count'];

// Total revenue
$stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
$stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

// Recent orders
$stmt = $pdo->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_orders = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->prepare("SELECT * FROM products WHERE stock_quantity <= 10 AND status = 'active' ORDER BY stock_quantity ASC LIMIT 10");
$stmt->execute();
$low_stock_products = $stmt->fetchAll();

// Recent users
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ShopHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div style="padding: 20px; text-align: center; border-bottom: 1px solid #34495e;">
            <h3 style="color: #e74c3c; margin: 0;">
                <i class="fas fa-shopping-bag"></i> ShopHub Admin
            </h3>
            <p style="color: #bdc3c7; font-size: 14px; margin: 5px 0 0 0;">
                Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
            </p>
        </div>
        
        <nav>
            <ul class="admin-nav">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li style="border-top: 1px solid #34495e; margin-top: 20px; padding-top: 20px;">
                    <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
                </li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <h1 class="admin-title">Dashboard</h1>
            <p style="color: #666;">Welcome to your admin dashboard. Here's an overview of your store.</p>
        </div>
        
        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 10px;">
                    <i class="fas fa-box"></i>
                </div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo number_format($stats['total_products']); ?></h3>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Total Products</p>
            </div>
            
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 10px;">
                    <i class="fas fa-users"></i>
                </div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo number_format($stats['total_users']); ?></h3>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Registered Users</p>
            </div>
            
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 10px;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo number_format($stats['total_orders']); ?></h3>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Total Orders</p>
            </div>
            
            <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 10px;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo formatPrice($stats['total_revenue']); ?></h3>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Total Revenue</p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50;">Quick Actions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="products.php?action=add" class="btn" style="text-align: center; padding: 20px;">
                    <i class="fas fa-plus"></i><br>Add Product
                </a>
                <a href="categories.php?action=add" class="btn btn-outline" style="text-align: center; padding: 20px;">
                    <i class="fas fa-tag"></i><br>Add Category
                </a>
                <a href="orders.php" class="btn btn-outline" style="text-align: center; padding: 20px;">
                    <i class="fas fa-list"></i><br>View Orders
                </a>
                <a href="reports.php" class="btn btn-outline" style="text-align: center; padding: 20px;">
                    <i class="fas fa-chart-line"></i><br>Sales Reports
                </a>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Recent Orders -->
            <div class="table-container">
                <div style="padding: 20px; border-bottom: 1px solid #eee;">
                    <h3 style="margin: 0; color: #2c3e50;">Recent Orders</h3>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($recent_orders)): ?>
                        <div style="padding: 40px; text-align: center; color: #999;">
                            <i class="fas fa-shopping-cart" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <p>No orders yet</p>
                        </div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                            <br>
                                            <small style="color: #666;"><?php echo htmlspecialchars($order['email']); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <div style="padding: 15px; border-top: 1px solid #eee; text-align: center;">
                    <a href="orders.php" class="btn btn-small">View All Orders</a>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <div class="table-container">
                <div style="padding: 20px; border-bottom: 1px solid #eee;">
                    <h3 style="margin: 0; color: #2c3e50;">
                        <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i>
                        Low Stock Alert
                    </h3>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($low_stock_products)): ?>
                        <div style="padding: 40px; text-align: center; color: #999;">
                            <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 15px; color: #27ae60;"></i>
                            <p>All products are well stocked!</p>
                        </div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock_products as $product): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <br>
                                            <small style="color: #666;">SKU: <?php echo htmlspecialchars($product['sku']); ?></small>
                                        </td>
                                        <td>
                                            <span style="color: <?php echo $product['stock_quantity'] <= 5 ? '#e74c3c' : '#f39c12'; ?>; font-weight: bold;">
                                                <?php echo $product['stock_quantity']; ?> units
                                            </span>
                                        </td>
                                        <td>
                                            <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-small">
                                                Restock
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <div style="padding: 15px; border-top: 1px solid #eee; text-align: center;">
                    <a href="products.php?filter=low_stock" class="btn btn-small">View All Low Stock</a>
                </div>
            </div>
        </div>
        
        <!-- Recent Users -->
        <div class="table-container" style="margin-top: 30px;">
            <div style="padding: 20px; border-bottom: 1px solid #eee;">
                <h3 style="margin: 0; color: #2c3e50;">Recent Users</h3>
            </div>
            <?php if (empty($recent_users)): ?>
                <div style="padding: 40px; text-align: center; color: #999;">
                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <p>No users registered yet</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="users.php?action=view&id=<?php echo $user['id']; ?>" class="btn btn-small">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <div style="padding: 15px; border-top: 1px solid #eee; text-align: center;">
                <a href="users.php" class="btn btn-small">View All Users</a>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Auto-refresh dashboard data every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 300000);
        
        // Highlight low stock items
        document.querySelectorAll('[style*="color: #e74c3c"]').forEach(item => {
            item.parentNode.parentNode.style.background = '#fff5f5';
        });
    </script>
</body>
</html>