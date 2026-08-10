# Seed2Greens - E-Commerce Website

A complete B2C e-commerce platform for agricultural products, built with PHP, MySQL, HTML5, CSS3, and JavaScript.

## Project Information

**Project Name:** Seed2Greens  
**Course:** BSc CSIT  
**Year:** 2026  
**Team Members:**
1. Sandesh Bhandari
2. Yubraj Bhandari
3. Yubesh Joshi
4. Sanskar Upaadhyaya

## About Seed2Greens

Seed2Greens is a B2C e-commerce platform focused on agriculture. It connects customers with quality agricultural products including:

- **Fresh Produce** - Fresh fruits and vegetables sourced directly from farms
- **Seeds** - High-quality crop seeds for better yield
- **Organic Fertilizers** - Natural fertilizers for sustainable farming
- **Agriculture Tools** - Essential farming and gardening tools

## Features

### Customer Features
- User registration and authentication
- Browse products by category
- Search and filter products
- View product details
- Add to cart and manage cart
- Wishlist functionality
- Secure checkout with Cash on Delivery
- Order history and tracking
- User profile management

### Admin Features
- Dashboard with statistics
- Product management (Add, Edit, Delete)
- Category management
- Order management with status updates
- User management
- View total sales, orders, and users

## Technologies Used

| Technology | Purpose |
|------------|---------|
| HTML5 | Frontend structure |
| CSS3 | Styling and responsive design |
| JavaScript | Frontend interactions |
| PHP | Backend logic |
| MySQL | Database |
| Font Awesome | Icons |

## Architecture

**3-Tier Architecture:**
1. **Presentation Layer** - HTML5, CSS3, JavaScript (Frontend)
2. **Application Layer** - PHP Backend (Business Logic)
3. **Data Layer** - MySQL Database

## Project Structure

```
seed2greens/
├── index.php              # Homepage
├── products.php           # Product listing
├── product.php            # Single product details
├── category.php           # Category products
├── login.php              # User login
├── register.php           # User registration
├── logout.php             # Logout handler
├── cart.php               # Shopping cart
├── wishlist.php           # Wishlist
├── checkout.php           # Checkout page
├── orders.php             # Order history
├── order-details.php      # Single order details
├── profile.php            # User profile
├── about.php              # About page
├── contact.php            # Contact page
│
├── admin/
│   ├── login.php          # Admin login
│   ├── dashboard.php      # Admin dashboard
│   ├── products.php       # Manage products
│   ├── add-product.php    # Add new product
│   ├── edit-product.php   # Edit product
│   ├── orders.php         # Manage orders
│   ├── order-details.php  # Order details
│   ├── users.php          # Manage users
│   ├── categories.php     # Manage categories
│   └── logout.php         # Admin logout
│
├── config/
│   └── database.php       # Database connection (PDO)
│
├── includes/
│   ├── header.php         # HTML head and top bar
│   ├── footer.php         # Footer and scripts
│   ├── navbar.php         # Navigation bar
│   ├── auth.php           # Authentication functions
│   └── functions.php      # Helper functions
│
├── assets/
│   ├── css/
│   │   └── style.css      # Complete stylesheet
│   ├── js/
│   │   └── script.js      # JavaScript functions
│   └── images/            # Product images
│
├── database/
│   └── database.sql       # Database schema and sample data
│
└── README.md              # Project documentation
```

## Database Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP / WAMP / LAMP server

### Installation Steps

1. **Clone or download the project**
   ```bash
   git clone <repository-url>
   cd seed2greens
   ```

2. **Configure Database Connection**
   - Open `config/database.php`
   - Update the database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'seed2greens');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. **Import Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a database named `seed2greens`
   - Click on the database
   - Click "Import" tab
   - Select `database/database.sql` file
   - Click "Go" to import

4. **Start the Server**
   ```bash
   # If using XAMPP, place the project in htdocs folder
   # Then visit: http://localhost/seed2greens
   ```

## Default Credentials

### Admin Login
- **Username:** `admin`
- **Password:** `admin123`

### Sample Customer Accounts
- **Email:** ramesh@example.com
- **Password:** password

- **Email:** sita@example.com
- **Password:** password

- **Email:** gopal@example.com
- **Password:** password

## User Flow

```
1. Register / Login
2. Browse Products (by category, search, or view all)
3. View Product Details
4. Add to Cart / Wishlist
5. View Cart and proceed to Checkout
6. Enter delivery details
7. Place Order (Cash on Delivery)
8. View Order History
9. Track Order Status
```

## Admin Flow

```
1. Login to Admin Panel
2. View Dashboard Statistics
3. Manage Products (Add, Edit, Delete)
4. Manage Categories
5. View and Update Order Status
6. Manage Registered Users
```

## Database Schema

### Tables

| Table | Description |
|-------|-------------|
| users | Customer accounts |
| categories | Product categories |
| products | Product information |
| cart | Shopping cart items |
| wishlist | User wishlist items |
| orders | Order records |
| order_items | Individual order items |
| admin | Admin accounts |

## Security Features

- Password hashing with `password_hash()`
- Password verification with `password_verify()`
- PDO prepared statements to prevent SQL injection
- Session-based authentication
- Admin authentication protection
- Input sanitization and validation
- Output escaping to prevent XSS

## Responsive Design

The website is fully responsive and works on:
- Desktop (1200px+)
- Laptop (992px - 1199px)
- Tablet (768px - 991px)
- Mobile (< 768px)

## Sample Products Included

### Fresh Produce
- Tomato, Potato, Red Apple, Cauliflower, Spinach, Carrot, Cabbage

### Seeds
- Tomato Seeds, Radish Seeds, Cucumber Seeds, Spinach Seeds, Chili Seeds, Brinjal Seeds

### Organic Fertilizers
- Vermicompost, Organic Compost, Cow Manure Fertilizer, Neem Cake Fertilizer, Bone Meal Fertilizer

### Agriculture Tools
- Hand Trowel, Garden Hoe, Pruning Shears, Watering Can, Garden Fork, Gardening Gloves

## Payment Integration Note

Currently, **Cash on Delivery** is the only active payment method. The structure is ready to integrate:
- **eSewa** - Popular Nepali payment gateway
- **Khalti** - Another Nepali digital wallet

To integrate eSewa/Khalti, update the checkout form and add the respective API integration in `checkout.php`.

## Development Notes

- All PHP files use PDO for database access
- Passwords are hashed before storage
- All user inputs are sanitized
- Flash messages provide user feedback
- Cart and wishlist are user-specific
- Orders are linked to users
- Stock is automatically updated when orders are placed

## Troubleshooting

1. **Database connection error:** Check `config/database.php` credentials
2. **Images not showing:** Place product images in `assets/images/` folder
3. **Login not working:** Ensure database is imported correctly
4. **Cart not updating:** Check browser console for JavaScript errors

## Future Enhancements

- Payment gateway integration (eSewa, Khalti)
- Email notifications for orders
- Product reviews and ratings
- Advanced search with filters
- Order tracking with real-time updates
- Mobile app version
- Multi-language support

## License

This is an academic project developed for educational purposes.

---

**Seed2Greens** - Growing Together with Nepali Farmers
