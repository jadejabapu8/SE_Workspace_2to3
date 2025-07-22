# E-Commerce Website - ShopHub

A complete, responsive e-commerce website built with PHP, MySQL, HTML, CSS, and JavaScript featuring full admin panel and user management.

## 🚀 Features

### Frontend Features
- **Responsive Design**: Mobile-first, fully responsive layout
- **User Authentication**: Registration, login, logout with secure password hashing
- **Product Catalog**: Browse products by categories with search and filtering
- **Shopping Cart**: Add to cart, update quantities, remove items
- **Wishlist**: Save favorite products for later
- **Product Reviews**: Rate and review products
- **User Dashboard**: Manage profile, orders, and account settings

### Admin Panel Features
- **Dashboard**: Overview statistics and quick actions
- **Product Management**: Add, edit, delete products with full CRUD operations
- **Category Management**: Organize products into categories
- **Order Management**: View and manage customer orders
- **User Management**: View and manage registered users
- **Inventory Management**: Track stock levels with low-stock alerts
- **Reports**: Sales and analytics reporting

### Technical Features
- **Secure Authentication**: Password hashing, session management
- **AJAX Operations**: Dynamic cart updates, notifications
- **Responsive UI**: Modern, mobile-friendly interface
- **Search Functionality**: Full-text search across products
- **Pagination**: Efficient data pagination throughout
- **Form Validation**: Client-side and server-side validation
- **Error Handling**: Comprehensive error management

## 📋 Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache/Nginx with mod_rewrite
- **Extensions**: PDO, PDO_MySQL, Session support

## 🛠️ Installation

### 1. Download/Clone the Project
```bash
# If using Git
git clone <repository-url>
cd ecommerce-website

# Or download and extract the ZIP file
```

### 2. Set Up Web Server
- Place the `ecommerce-website` folder in your web server's document root
- For XAMPP: `C:\xampp\htdocs\ecommerce-website`
- For WAMP: `C:\wamp64\www\ecommerce-website`
- For Linux: `/var/www/html/ecommerce-website`

### 3. Create Database
1. Open phpMyAdmin or your MySQL client
2. Create a new database named `ecommerce`
3. Import the database schema from `database/ecommerce.sql`

```sql
-- Or run these commands manually:
CREATE DATABASE ecommerce;
USE ecommerce;
-- Then copy and paste the contents of database/ecommerce.sql
```

### 4. Configure Database Connection
Edit `includes/config.php` and update the database settings:

```php
define('DB_HOST', 'localhost');     // Your MySQL host
define('DB_USERNAME', 'root');      // Your MySQL username
define('DB_PASSWORD', '');          // Your MySQL password
define('DB_NAME', 'ecommerce');     // Database name
```

### 5. Set Up Directory Permissions
Make sure the following directories are writable:
```bash
chmod 755 assets/images/uploads/
chmod 755 assets/images/products/
chmod 755 assets/images/categories/
```

### 6. Access the Website
- **Frontend**: `http://localhost/ecommerce-website/`
- **Admin Panel**: `http://localhost/ecommerce-website/admin/`

## 🔐 Default Login Credentials

### Admin Access
- **URL**: `http://localhost/ecommerce-website/admin/`
- **Username**: `admin`
- **Email**: `admin@ecommerce.com`
- **Password**: `password`

### Test User Account
You can create user accounts through the registration page, or use these sample credentials if available:
- **Email**: `user@example.com`
- **Password**: `password123`

## 📁 Project Structure

```
ecommerce-website/
├── admin/                  # Admin panel files
│   ├── dashboard.php      # Admin dashboard
│   ├── products.php       # Product management
│   ├── categories.php     # Category management
│   ├── orders.php         # Order management
│   ├── users.php          # User management
│   ├── login.php          # Admin login
│   └── logout.php         # Admin logout
├── user/                   # User account pages
│   ├── login.php          # User login
│   ├── register.php       # User registration
│   ├── profile.php        # User profile
│   └── dashboard.php      # User dashboard
├── includes/               # PHP includes and utilities
│   ├── config.php         # Database configuration
│   ├── add_to_cart.php    # Cart functionality
│   └── get_cart_count.php # Cart count API
├── assets/                 # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   └── main.js        # Main JavaScript
│   └── images/            # Image uploads
├── database/               # Database files
│   └── ecommerce.sql      # Database schema
├── index.php              # Homepage
├── products.php           # Product listing
├── product.php            # Single product page
├── cart.php               # Shopping cart
├── search.php             # Search results
└── README.md              # This file
```

## 🎨 Customization

### Styling
- Main CSS file: `assets/css/style.css`
- The design uses a modern, responsive grid layout
- Color scheme can be customized by changing CSS variables

### Branding
- Update logo and site name in `index.php`
- Modify the color scheme in `style.css`
- Replace placeholder images with your own

### Features
- Add new product fields in the database and update forms
- Extend user profile with additional information
- Implement payment gateway integration
- Add email notifications

## 🔧 Configuration Options

### Site Settings
Edit `includes/config.php` to customize:

```php
// Site URLs
define('SITE_URL', 'http://localhost/ecommerce-website/');
define('ADMIN_URL', 'http://localhost/ecommerce-website/admin/');

// Upload paths
define('UPLOAD_PATH', 'assets/images/uploads/');

// Email settings (for future implementation)
define('SMTP_HOST', 'your-smtp-host');
define('SMTP_USERNAME', 'your-email@domain.com');
define('SMTP_PASSWORD', 'your-email-password');
```

## 🛡️ Security Features

- **Password Hashing**: Using PHP's `password_hash()` function
- **SQL Injection Protection**: Prepared statements throughout
- **XSS Prevention**: HTML escaping on all outputs
- **Session Security**: Secure session handling
- **CSRF Protection**: Can be added to forms
- **Input Validation**: Server-side validation on all forms

## 🚀 Performance Optimization

- **Database Indexing**: Proper indexes on frequently queried columns
- **Image Optimization**: Compress images before upload
- **Caching**: Implement caching for product listings
- **CDN**: Use CDN for static assets
- **Minification**: Minify CSS and JavaScript files

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `includes/config.php`
   - Ensure MySQL service is running
   - Verify database exists and user has permissions

2. **File Upload Issues**
   - Check directory permissions (should be 755 or 777)
   - Verify `upload_max_filesize` in php.ini
   - Ensure the uploads directory exists

3. **Session Issues**
   - Check that session.save_path is writable
   - Verify session configuration in php.ini
   - Clear browser cookies if needed

4. **Styling Issues**
   - Check that CSS files are loading correctly
   - Verify file paths are correct
   - Clear browser cache

### Error Logs
Check your web server error logs for detailed error information:
- Apache: `/var/log/apache2/error.log`
- Nginx: `/var/log/nginx/error.log`
- XAMPP: `xampp/apache/logs/error.log`

## 📝 Future Enhancements

- **Payment Integration**: PayPal, Stripe, or other gateways
- **Email Notifications**: Order confirmations, shipping updates
- **Inventory Management**: Advanced stock tracking
- **Multi-language Support**: Internationalization
- **Advanced Search**: Filters, sorting, faceted search
- **Mobile App**: React Native or Flutter app
- **API Development**: RESTful API for mobile apps
- **Analytics**: Advanced reporting and analytics

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

For support, please:
1. Check this README file
2. Review the troubleshooting section
3. Check the project's issue tracker
4. Create a new issue with detailed information

## 📧 Contact

For questions or suggestions, please contact:
- Email: support@shophub.com
- Website: https://shophub.com

---

**Happy Selling! 🛒**