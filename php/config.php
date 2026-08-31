<?php
/**
 * ModelCars Pro - Database & Global Configuration
 * File: php/config.php
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'model_cars_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');

// Application Settings
define('APP_NAME', 'ModelCars Pro');
define('CURRENCY_SYMBOL', 'Rs. ');
define('FREE_SHIPPING_THRESHOLD', 5000);
define('STANDARD_SHIPPING_FEE', 350);

/**
 * Returns a PDO Database Connection instance or null if unavailable.
 * Gracefully handles connection failures so site works even in standalone mock mode.
 */
function getDbConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 2
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Fallback: database not connected yet
        return null;
    }
}

/**
 * Format numerical amount to standard currency format (Rs. 1,299.00 or Rs. 1,299)
 */
function formatPrice($amount) {
    if ($amount === null || $amount === '') return '';
    return CURRENCY_SYMBOL . number_format((float)$amount, 0);
}

/**
 * Sanitize strings for safe HTML output
 */
function sanitize($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Helper to get proper relative path for product image
 */
function getProductImagePath($filename) {
    if (empty($filename)) {
        return 'images/Ferrari F40.jpeg';
    }
    // Check if located in images/products/ or images/
    $root = dirname(__DIR__);
    if (file_exists($root . '/images/products/' . $filename)) {
        return 'images/products/' . $filename;
    }
    if (file_exists($root . '/images/' . $filename)) {
        return 'images/' . $filename;
    }
    // Fallback relative path
    return 'images/' . $filename;
}

/**
 * Set a Flash Message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Get and clear Flash Message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Safe redirect helper
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}
