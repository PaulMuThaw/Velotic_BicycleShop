# Velociti Bicycle Shop

Velociti Bicycle Shop is a web-based bicycle e-commerce application
built with PHP and MySQL. It provides a customer-facing storefront for
browsing bicycles and accessories, managing a shopping cart, placing
orders, and sharing feedback. It also includes an administration area
for managing products, customers, users, orders, and customer feedback.

## Features

### Customer Website

-   Browse the bicycle collection and available accessories.
-   View product information and product categories.
-   Create a customer account, log in, and log out.
-   View and update customer profile information.
-   Add products to a session-based shopping cart and update quantities.
-   Continue to checkout and view an order confirmation/receipt.
-   Submit feedback about the shop.
-   Browse the shop's information and about pages.

### Admin Panel

-   View a dashboard with shop overview information and sales/order
    charts.
-   Manage bicycle products, including adding, editing, and deleting
    products.
-   Upload product images.
-   Manage customer records and user accounts.
-   View and manage customer orders.
-   Review customer feedback and update its status.

## Technology Stack

  Technology     Purpose
  -------------- -----------------------------------------
  PHP            Server-side application logic
  MySQL          Relational database
  PDO            PHP database access
  HTML5          Page structure
  CSS3           Styling
  Tailwind CSS   Utility-first interface styling
  JavaScript     Client-side interactions
  Alpine.js      Lightweight interactive UI behaviour
  Chart.js       Dashboard charts and data visualisation
  Font Awesome   Interface icons

Tailwind CSS, Alpine.js, Chart.js, and Font Awesome are included through
CDN links in the source files.

## Project Structure

``` text
bicycle_shop-main/
├── customer/
│   ├── Item.php
│   ├── about.php
│   ├── cart.php
│   ├── checkout.php
│   ├── db_conn.php
│   ├── feedback.php
│   ├── footer.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── navbar.php
│   ├── profile.php
│   ├── receipt.php
│   ├── signup.php
│   └── view_allProduct.php
├── uploads/
│   └── product images and media
├── dashboard.php
├── db_conn.php
├── index.php
├── login.php
├── logout.php
├── manage_bicycles.php
├── manage_customer.php
├── manage_feedback.php
├── manage_users.php
├── orders.php
├── password.php
└── side_navbar.php
```

## Requirements

Install the following software before running the project:

-   PHP (with PDO MySQL support)
-   MySQL or MariaDB
-   Apache web server (for example, through XAMPP)
-   A modern web browser
-   Internet connection for the frontend CDN libraries

## Installation and Setup

1.  **Download or clone the repository.**

    ``` bash
    git clone <repository-url>
    ```

2.  **Move the project into your local web server directory.**

    For XAMPP, place the project folder inside:

    ``` text
    C:\xampp\htdocs\
    ```

3.  **Create the database.**

    Open phpMyAdmin or your MySQL client and create a database named:

    ``` sql
    CREATE DATABASE bicycleshop_db;
    ```

4.  **Import the database schema and data.**

    Import the SQL database file supplied with your project, if
    available. The application expects tables for the shop's users,
    customers, products, categories, orders, order details/items,
    payments, addresses, and feedback. The source archive used for this
    README did not include a database export, so the required tables and
    any initial admin account must be created or imported separately.

5.  **Configure the database connection.**

    Update the database settings in both `db_conn.php` and
    `customer/db_conn.php` to match your local MySQL configuration:

    ``` php
    <?php
    $host = 'localhost';
    $dbname = 'bicycleshop_db';
    $username = 'root';
    $password = '';

    try {
        $conn = new PDO(
            "mysql:host=$host;dbname=$dbname",
            $username,
            $password
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
    ?>
    ```

    Use the username and password configured for your own MySQL
    installation. Do not use an empty database password in a production
    environment.

6.  **Start Apache and MySQL** from the XAMPP Control Panel.

7.  **Open the website** in your browser. For example:

    ``` text
    http://localhost/bicycle_shop-main/customer/
    ```

    The admin entry point is:

    ``` text
    http://localhost/bicycle_shop-main/
    ```

    Sign in using an admin account that exists in your imported
    database. No default admin credentials are included in the source
    archive.

## Database

The application connects to a MySQL database named `bicycleshop_db`
using PHP PDO. Database records are used for product and category
information, customer accounts, user accounts, orders, order
items/details, payments, customer addresses, and feedback.

## Notes

-   Product image uploads are stored in the `uploads/` directory.
-   The application uses PHP sessions for customer login state and
    shopping-cart data.
-   The project source archive does not contain a database `.sql`
    export. A matching database schema and initial records are required
    before the application can run correctly.
-   Configure database credentials and review security settings before
    deploying the project to a public server.
-   Third-party frontend libraries are loaded from CDNs, so an internet
    connection is needed for those resources.

## Future Improvements

-   Add a documented database setup script and sample development data.
-   Integrate a secure online payment provider.
-   Add automated tests and input-validation coverage.
-   Improve deployment configuration and environment-based secrets
    management.
-   Add order tracking and email notifications.

## Author

Developed as a web development project to practise PHP, MySQL, frontend
development, database integration, and e-commerce application workflows.
