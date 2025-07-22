<?php
require_once 'includes/config.php';

// Get featured products
$stmt = $pdo->prepare("SELECT * FROM products WHERE featured = 1 AND status = 'active' LIMIT 8");
$stmt->execute();
$featured_products = $stmt->fetchAll();

// Get categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' LIMIT 6");
$stmt->execute();
$categories = $stmt->fetchAll();

// Get latest products
$stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8");
$stmt->execute();
$latest_products = $stmt->fetchAll();

// Get cart count for logged in users
$cart_count = 0;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_result = $stmt->fetch();
    $cart_count = $cart_result['count'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Store - Premium Quality Products</title>
    <meta name="description" content="Shop the latest products at the best prices. Premium quality electronics, clothing, books and more.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>📞 Call us: (555) 123-4567 | 📧 Email: info@ecommerce.com</span>
                    <span>🚚 Free shipping on orders over $50!</span>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <a href="index.php" class="logo">
                        <i class="fas fa-shopping-bag"></i> ShopHub
                    </a>
                    
                    <div class="search-box">
                        <form action="search.php" method="GET">
                            <input type="text" name="q" placeholder="Search for products..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="header-actions">
                        <?php if (isLoggedIn()): ?>
                            <a href="user/profile.php" class="header-link">
                                <i class="fas fa-user"></i>
                                <span>My Account</span>
                            </a>
                            <a href="user/wishlist.php" class="header-link">
                                <i class="fas fa-heart"></i>
                                <span>Wishlist</span>
                            </a>
                        <?php else: ?>
                            <a href="user/login.php" class="header-link">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Login</span>
                            </a>
                            <a href="user/register.php" class="header-link">
                                <i class="fas fa-user-plus"></i>
                                <span>Register</span>
                            </a>
                        <?php endif; ?>
                        
                        <a href="cart.php" class="header-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Cart</span>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <nav class="nav">
            <div class="container">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">All Products</a></li>
                    <?php foreach ($categories as $category): ?>
                        <li><a href="products.php?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1>Welcome to ShopHub</h1>
                <p>Discover amazing products at unbeatable prices. Shop with confidence and enjoy fast, free shipping!</p>
                <a href="products.php" class="btn">Shop Now</a>
                <a href="#featured" class="btn btn-outline">View Featured</a>
            </div>
        </section>
        
        <!-- Featured Products -->
        <section id="featured" class="products-section">
            <div class="container">
                <h2 class="section-title">Featured Products</h2>
                <div class="products-grid">
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card fade-in">
                            <div class="product-image">
                                <?php if ($product['image']): ?>
                                    <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <span>No Image</span>
                                <?php endif; ?>
                                
                                <?php if ($product['sale_price']): ?>
                                    <span class="product-badge">Sale</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                
                                <div class="product-price">
                                    <span class="current-price">
                                        <?php echo formatPrice($product['sale_price'] ?? $product['price']); ?>
                                    </span>
                                    <?php if ($product['sale_price']): ?>
                                        <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($product['short_description'] ?? $product['description'], 0, 100)) . '...'; ?>
                                </p>
                                
                                <div class="product-actions">
                                    <button class="btn btn-small add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline btn-small">
                                        View Details
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <button class="wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 40px;">
                    <a href="products.php?featured=1" class="btn">View All Featured Products</a>
                </div>
            </div>
        </section>
        
        <!-- Categories Section -->
        <section class="categories-section" style="background: white; padding: 60px 0;">
            <div class="container">
                <h2 class="section-title">Shop by Category</h2>
                <div class="products-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="product-card fade-in">
                            <div class="product-image">
                                <?php if ($category['image']): ?>
                                    <img src="assets/images/categories/<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-folder" style="font-size: 48px; color: #999;"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                                <p class="product-description">
                                    <?php echo htmlspecialchars($category['description']); ?>
                                </p>
                                <div class="product-actions">
                                    <a href="products.php?category=<?php echo $category['id']; ?>" class="btn">
                                        Browse Category
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <!-- Latest Products -->
        <section class="products-section">
            <div class="container">
                <h2 class="section-title">Latest Products</h2>
                <div class="products-grid">
                    <?php foreach ($latest_products as $product): ?>
                        <div class="product-card fade-in">
                            <div class="product-image">
                                <?php if ($product['image']): ?>
                                    <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <span>No Image</span>
                                <?php endif; ?>
                                
                                <?php if ($product['sale_price']): ?>
                                    <span class="product-badge">Sale</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                
                                <div class="product-price">
                                    <span class="current-price">
                                        <?php echo formatPrice($product['sale_price'] ?? $product['price']); ?>
                                    </span>
                                    <?php if ($product['sale_price']): ?>
                                        <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($product['short_description'] ?? $product['description'], 0, 100)) . '...'; ?>
                                </p>
                                
                                <div class="product-actions">
                                    <button class="btn btn-small add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline btn-small">
                                        View Details
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <button class="wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 40px;">
                    <a href="products.php" class="btn">View All Products</a>
                </div>
            </div>
        </section>
        
        <!-- Newsletter Section -->
        <section style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 0; text-align: center;">
            <div class="container">
                <h2 style="font-size: 2.5rem; margin-bottom: 20px;">Stay Updated</h2>
                <p style="font-size: 1.2rem; margin-bottom: 30px; opacity: 0.9;">Subscribe to our newsletter for exclusive deals and new product announcements</p>
                <form style="max-width: 500px; margin: 0 auto; display: flex; gap: 10px;">
                    <input type="email" placeholder="Enter your email address" style="flex: 1; padding: 15px; border: none; border-radius: 5px; font-size: 16px;">
                    <button type="submit" class="btn" style="background: #e74c3c; padding: 15px 30px;">Subscribe</button>
                </form>
            </div>
        </section>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About ShopHub</h3>
                    <p>Your trusted online shopping destination for premium quality products at affordable prices. We pride ourselves on excellent customer service and fast delivery.</p>
                    <div style="margin-top: 20px;">
                        <a href="#" style="color: #e74c3c; margin-right: 15px; font-size: 20px;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: #e74c3c; margin-right: 15px; font-size: 20px;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: #e74c3c; margin-right: 15px; font-size: 20px;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: #e74c3c; margin-right: 15px; font-size: 20px;"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="shipping.php">Shipping Info</a></li>
                        <li><a href="returns.php">Returns & Exchanges</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                        <li><a href="support.php">Support</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Commerce Street, City, ST 12345</li>
                        <li><i class="fas fa-phone"></i> (555) 123-4567</li>
                        <li><i class="fas fa-envelope"></i> info@shophub.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Fri: 9AM-6PM</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 ShopHub E-commerce. All rights reserved. | Designed with ❤️ for great shopping experience</p>
            </div>
        </div>
    </footer>
    
    <script src="assets/js/main.js"></script>
</body>
</html>