<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$action = $_GET['action'] ?? 'list';
$product_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $short_description = sanitize($_POST['short_description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
        $sku = sanitize($_POST['sku'] ?? '');
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'active');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : null;
        $dimensions = sanitize($_POST['dimensions'] ?? '');
        
        // Basic validation
        if (empty($name) || empty($price) || empty($sku)) {
            $error = 'Please fill in all required fields';
        } else {
            // Check for duplicate SKU
            $sku_check_sql = "SELECT id FROM products WHERE sku = ?";
            if ($action === 'edit' && $product_id) {
                $sku_check_sql .= " AND id != ?";
                $stmt = $pdo->prepare($sku_check_sql);
                $stmt->execute([$sku, $product_id]);
            } else {
                $stmt = $pdo->prepare($sku_check_sql);
                $stmt->execute([$sku]);
            }
            
            if ($stmt->fetch()) {
                $error = 'A product with this SKU already exists';
            } else {
                if ($action === 'add') {
                    // Add new product
                    $stmt = $pdo->prepare("
                        INSERT INTO products (name, description, short_description, price, sale_price, sku, 
                        stock_quantity, category_id, status, featured, weight, dimensions, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    if ($stmt->execute([$name, $description, $short_description, $price, $sale_price, 
                                      $sku, $stock_quantity, $category_id, $status, $featured, $weight, $dimensions])) {
                        $message = 'Product added successfully!';
                        $action = 'list';
                    } else {
                        $error = 'Failed to add product';
                    }
                } elseif ($action === 'edit' && $product_id) {
                    // Update existing product
                    $stmt = $pdo->prepare("
                        UPDATE products SET name=?, description=?, short_description=?, price=?, sale_price=?, 
                        sku=?, stock_quantity=?, category_id=?, status=?, featured=?, weight=?, dimensions=?, 
                        updated_at=NOW() WHERE id=?
                    ");
                    
                    if ($stmt->execute([$name, $description, $short_description, $price, $sale_price, 
                                      $sku, $stock_quantity, $category_id, $status, $featured, $weight, $dimensions, $product_id])) {
                        $message = 'Product updated successfully!';
                        $action = 'list';
                    } else {
                        $error = 'Failed to update product';
                    }
                }
            }
        }
    } elseif ($action === 'delete' && $product_id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $message = 'Product deleted successfully!';
        } else {
            $error = 'Failed to delete product';
        }
        $action = 'list';
    }
}

// Get categories for dropdowns
$stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Handle different actions
if ($action === 'list') {
    // Get products with pagination
    $page = intval($_GET['page'] ?? 1);
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    $search = $_GET['search'] ?? '';
    $filter = $_GET['filter'] ?? '';
    
    $where_clauses = [];
    $params = [];
    
    if (!empty($search)) {
        $where_clauses[] = "(name LIKE ? OR sku LIKE ? OR description LIKE ?)";
        $search_term = "%$search%";
        $params = array_merge($params, [$search_term, $search_term, $search_term]);
    }
    
    if ($filter === 'low_stock') {
        $where_clauses[] = "stock_quantity <= 10";
    } elseif ($filter === 'featured') {
        $where_clauses[] = "featured = 1";
    } elseif ($filter === 'inactive') {
        $where_clauses[] = "status = 'inactive'";
    }
    
    $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    
    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON p.category_id = c.id $where_sql");
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_products / $per_page);
    
    // Get products
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_sql 
        ORDER BY p.created_at DESC 
        LIMIT $per_page OFFSET $offset
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
} elseif ($action === 'edit' && $product_id) {
    // Get product for editing
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $error = 'Product not found';
        $action = 'list';
    }
} elseif ($action === 'add') {
    // Initialize empty product for add form
    $product = [
        'name' => '',
        'description' => '',
        'short_description' => '',
        'price' => '',
        'sale_price' => '',
        'sku' => '',
        'stock_quantity' => '',
        'category_id' => '',
        'status' => 'active',
        'featured' => 0,
        'weight' => '',
        'dimensions' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - ShopHub Admin</title>
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
        </div>
        
        <nav>
            <ul class="admin-nav">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
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
        <?php if ($action === 'list'): ?>
            <div class="admin-header">
                <h1 class="admin-title">Products Management</h1>
                <p style="color: #666;">Manage your store products, add new items, and update inventory.</p>
            </div>
            
            <?php if ($message): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Controls -->
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; color: #2c3e50;">Product List</h3>
                    <a href="?action=add" class="btn">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
                
                <!-- Search and Filters -->
                <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 15px; align-items: end;">
                    <form method="GET" style="display: flex; gap: 10px;">
                        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                        <div style="flex: 1;">
                            <input type="text" name="search" placeholder="Search products..." 
                                   value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                        </div>
                        <button type="submit" class="btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    
                    <div>
                        <select onchange="window.location.href='?filter=' + this.value" class="form-control">
                            <option value="">All Products</option>
                            <option value="featured" <?php echo $filter === 'featured' ? 'selected' : ''; ?>>Featured</option>
                            <option value="low_stock" <?php echo $filter === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="inactive" <?php echo $filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div>
                        <span style="color: #666; font-size: 14px;">
                            Total: <?php echo number_format($total_products); ?> products
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="table-container">
                <?php if (empty($products)): ?>
                    <div style="padding: 60px; text-align: center; color: #999;">
                        <i class="fas fa-box" style="font-size: 64px; margin-bottom: 20px;"></i>
                        <h3>No products found</h3>
                        <p>Start by adding your first product to the store.</p>
                        <a href="?action=add" class="btn" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Add Product
                        </a>
                    </div>
                <?php else: ?>
                    <table class="table sortable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <div style="width: 50px; height: 50px; background: #f1f2f6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                <?php if ($product['image']): ?>
                                                    <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                                <?php else: ?>
                                                    <i class="fas fa-image" style="color: #999;"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                <?php if ($product['featured']): ?>
                                                    <span style="background: #ffc107; color: #856404; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 5px;">FEATURED</span>
                                                <?php endif; ?>
                                                <br>
                                                <small style="color: #666;">
                                                    <?php echo htmlspecialchars(substr($product['short_description'] ?? $product['description'], 0, 50)); ?>...
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code style="background: #f1f2f6; padding: 2px 6px; border-radius: 4px;">
                                            <?php echo htmlspecialchars($product['sku']); ?>
                                        </code>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?></td>
                                    <td>
                                        <strong><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></strong>
                                        <?php if ($product['sale_price']): ?>
                                            <br>
                                            <small style="text-decoration: line-through; color: #999;">
                                                <?php echo formatPrice($product['price']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: <?php echo $product['stock_quantity'] <= 10 ? ($product['stock_quantity'] <= 5 ? '#e74c3c' : '#f39c12') : '#27ae60'; ?>; font-weight: bold;">
                                            <?php echo $product['stock_quantity']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $product['status']; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-small">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../product.php?id=<?php echo $product['id']; ?>" target="_blank" class="btn btn-outline btn-small">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="btn btn-small" style="background: #e74c3c;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div style="padding: 20px; text-align: center; border-top: 1px solid #eee;">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>" 
                                   class="btn <?php echo $i === $page ? '' : 'btn-outline'; ?> btn-small" style="margin: 0 2px;">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-header">
                <h1 class="admin-title">
                    <?php echo $action === 'add' ? 'Add New Product' : 'Edit Product'; ?>
                </h1>
                <p style="color: #666;">
                    <?php echo $action === 'add' ? 'Add a new product to your store' : 'Update product information'; ?>
                </p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h3 style="margin: 0; color: #2c3e50;">Product Information</h3>
                    <a href="?action=list" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                
                <form method="POST" data-validate>
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                        <div>
                            <!-- Basic Information -->
                            <div style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #e74c3c; padding-bottom: 10px;">Basic Information</h4>
                                
                                <div class="form-group">
                                    <label for="name">Product Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="short_description">Short Description</label>
                                    <textarea id="short_description" name="short_description" class="form-control" rows="3" 
                                              placeholder="Brief product description for listings"><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Full Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="6" 
                                              placeholder="Detailed product description"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Pricing -->
                            <div style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #e74c3c; padding-bottom: 10px;">Pricing</h4>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div class="form-group">
                                        <label for="price">Regular Price * ($)</label>
                                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0"
                                               value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="sale_price">Sale Price ($)</label>
                                        <input type="number" id="sale_price" name="sale_price" class="form-control" step="0.01" min="0"
                                               value="<?php echo htmlspecialchars($product['sale_price']); ?>">
                                        <small style="color: #666;">Leave empty if not on sale</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <!-- Product Details -->
                            <div style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #e74c3c; padding-bottom: 10px;">Product Details</h4>
                                
                                <div class="form-group">
                                    <label for="sku">SKU *</label>
                                    <input type="text" id="sku" name="sku" class="form-control" 
                                           value="<?php echo htmlspecialchars($product['sku']); ?>" required>
                                    <small style="color: #666;">Unique product identifier</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select id="category_id" name="category_id" class="form-control">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="stock_quantity">Stock Quantity *</label>
                                    <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0"
                                           value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="out_of_stock" <?php echo $product['status'] === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="checkbox" name="featured" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                                        <span>Featured Product</span>
                                    </label>
                                    <small style="color: #666;">Featured products appear on homepage</small>
                                </div>
                            </div>
                            
                            <!-- Shipping -->
                            <div>
                                <h4 style="color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #e74c3c; padding-bottom: 10px;">Shipping Information</h4>
                                
                                <div class="form-group">
                                    <label for="weight">Weight (lbs)</label>
                                    <input type="number" id="weight" name="weight" class="form-control" step="0.01" min="0"
                                           value="<?php echo htmlspecialchars($product['weight']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="dimensions">Dimensions</label>
                                    <input type="text" id="dimensions" name="dimensions" class="form-control" 
                                           value="<?php echo htmlspecialchars($product['dimensions']); ?>"
                                           placeholder="L x W x H">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="border-top: 1px solid #eee; padding-top: 30px; margin-top: 30px; text-align: center;">
                        <button type="submit" class="btn" style="padding: 15px 40px; font-size: 16px;">
                            <i class="fas fa-save"></i> 
                            <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                        </button>
                        <a href="?action=list" class="btn btn-outline" style="padding: 15px 40px; font-size: 16px; margin-left: 15px;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
    
    <script>
        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                window.location.href = '?action=delete&id=' + id;
            }
        }
        
        // Auto-generate SKU based on product name
        document.getElementById('name')?.addEventListener('input', function() {
            const skuField = document.getElementById('sku');
            if (skuField.value === '') {
                const sku = this.value
                    .toUpperCase()
                    .replace(/[^A-Z0-9]/g, '')
                    .substring(0, 10) + Math.floor(Math.random() * 1000);
                skuField.value = sku;
            }
        });
        
        // Sale price validation
        document.getElementById('sale_price')?.addEventListener('input', function() {
            const regularPrice = parseFloat(document.getElementById('price').value) || 0;
            const salePrice = parseFloat(this.value) || 0;
            
            if (salePrice > 0 && salePrice >= regularPrice) {
                alert('Sale price must be less than regular price');
                this.value = '';
            }
        });
    </script>
</body>
</html>