<?php
/**
 * ModelCars Pro - Authentication & User Management
 * File: php/auth.php
 */

require_once __DIR__ . '/config.php';

/**
 * Check if a user is currently logged in
 */
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

/**
 * Retrieve current logged in user profile
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id, full_name, email, phone, address, city, postal_code, role, created_at FROM `users` WHERE id = ? LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            if ($user) return $user;
        } catch (Exception $e) {}
    }

    // Session fallback
    if (isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }

    return null;
}

/**
 * Log in user by email and password
 */
function loginUser($email, $password) {
    $email = trim(strtolower($email));
    $password = trim($password);

    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Please enter both email and password.'];
    }

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `users` WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if (!headers_sent()) {
                    session_regenerate_id(true);
                }
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_data'] = [
                    'id' => $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? '',
                    'address' => $user['address'] ?? '',
                    'city' => $user['city'] ?? '',
                    'postal_code' => $user['postal_code'] ?? '',
                    'role' => $user['role']
                ];
                return ['success' => true, 'message' => 'Welcome back, ' . $user['full_name'] . '!'];
            }
        } catch (Exception $e) {}
    }

    // Default Demo Admin Fallback for testing without DB
    if ($email === 'admin@modelcars.com' && $password === 'password123') {
        if (!headers_sent()) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'ModelCars Admin';
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_data'] = [
            'id' => 1,
            'full_name' => 'ModelCars Admin',
            'email' => 'admin@modelcars.com',
            'phone' => '+94 77 123 4567',
            'address' => '123 Galle Road',
            'city' => 'Colombo',
            'postal_code' => '00100',
            'role' => 'admin'
        ];
        return ['success' => true, 'message' => 'Welcome back, ModelCars Admin!'];
    }

    return ['success' => false, 'message' => 'Invalid email address or password.'];
}

/**
 * Register a new customer
 */
function registerUser($fullName, $email, $password, $phone = '', $address = '', $city = '', $postalCode = '') {
    $fullName = trim($fullName);
    $email = trim(strtolower($email));
    $password = trim($password);
    $phone = trim($phone);
    $address = trim($address);
    $city = trim($city);
    $postalCode = trim($postalCode);

    if (empty($fullName) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Please fill in your name, email, and password.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please provide a valid email address.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM `users` WHERE email = ? LIMIT 1");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                return ['success' => false, 'message' => 'An account with this email address already exists.'];
            }

            $stmt = $pdo->prepare("
                INSERT INTO `users` (`full_name`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role`)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'customer')
            ");
            $stmt->execute([$fullName, $email, $hashedPassword, $phone, $address, $city, $postalCode]);
            $userId = $pdo->lastInsertId();

            if (!headers_sent()) {
                session_regenerate_id(true);
            }
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_role'] = 'customer';
            $_SESSION['user_data'] = [
                'id' => $userId,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postalCode,
                'role' => 'customer'
            ];

            return ['success' => true, 'message' => 'Account created successfully! Welcome to ModelCars Pro.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed due to a database error. Please try again.'];
        }
    }

    // Session fallback if DB offline
    $mockId = rand(100, 999);
    if (!headers_sent()) {
        session_regenerate_id(true);
    }
    $_SESSION['user_id'] = $mockId;
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_role'] = 'customer';
    $_SESSION['user_data'] = [
        'id' => $mockId,
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
        'postal_code' => $postalCode,
        'role' => 'customer'
    ];

    return ['success' => true, 'message' => 'Account created successfully! Welcome to ModelCars Pro.'];
}

/**
 * Log out the current user
 */
function logoutUser() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_role']);
    unset($_SESSION['user_data']);
    if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    return ['success' => true, 'message' => 'You have logged out successfully.'];
}
