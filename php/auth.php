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
    if (!$pdo) {
        // Week 06 requires persistent database-backed authentication.
        // Do not silently fall back to session-only mock users because they
        // disappear after logout and make login appear to be broken.
        return ['success' => false, 'message' => 'Database connection is unavailable. Please start MySQL in XAMPP and verify the database configuration.'];
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM `users` WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Use the stored bcrypt/Argon2-compatible password hash.
        // Never compare or hash the submitted password directly.
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
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Unable to verify your login because of a database error. Please check the XAMPP MySQL database.'];
    }

    return ['success' => false, 'message' => 'Invalid email address or password.'];
}

/**
 * Helper to get in-memory registered users for standalone mock testing
 */
function getMockUsers() {
    $mockUsers = $_SESSION['mock_registered_users'] ?? [];
    if (!isset($mockUsers['admin@modelcars.com'])) {
        $mockUsers['admin@modelcars.com'] = [
            'id' => 1,
            'full_name' => 'ModelCars Admin',
            'email' => 'admin@modelcars.com',
            'password' => '$2y$10$4n9xHwM7gCeqYjKx0W8tfeU0Gk05j4C2J1lK7lS0yU1qF5n9wQn2O', // bcrypt of password123
            'phone' => '+94 77 123 4567',
            'address' => '123 Galle Road',
            'city' => 'Colombo',
            'postal_code' => '00100',
            'role' => 'admin'
        ];
    }
    return $mockUsers;
}

/**
 * Helper to save in-memory registered user
 */
function saveMockUser($userData) {
    if (!isset($_SESSION['mock_registered_users'])) {
        $_SESSION['mock_registered_users'] = [];
    }
    $_SESSION['mock_registered_users'][$userData['email']] = $userData;
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

    // Persistent database storage is required for Week 06 authentication.
    if (!$pdo) {
        return ['success' => false, 'message' => 'Registration requires a database connection. Please start MySQL in XAMPP and verify the database configuration.'];
    }

    return ['success' => false, 'message' => 'Registration could not be completed. Please try again.'];
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

/**
 * Authentication Guard: Requires the user to be logged in.
 * If not authenticated, redirects to login.php with return redirect parameter.
 */
function requireAuth($returnUrl = null) {
    if (!isLoggedIn()) {
        if ($returnUrl === null) {
            $returnUrl = $_SERVER['REQUEST_URI'] ?? 'profile.php';
        }
        $safeRedirect = urlencode($returnUrl);
        setFlashMessage('warning', 'Please sign in to access this page.');
        redirect('login.php?redirect=' . $safeRedirect);
    }
}

/**
 * Validates that a redirect URL is local and safe against open-redirect attacks.
 */
function getSafeRedirectUrl($redirectParam, $default = 'index.php') {
    if (empty($redirectParam)) {
        return $default;
    }
    // Disallow external URLs (e.g., http://, https://, //)
    if (preg_match('/^(\/\/|https?:\/\/|ftp:\/\/)/i', $redirectParam)) {
        return $default;
    }
    // Only allow safe alphanumeric, query parameters, and relative path characters
    if (preg_match('/^[a-zA-Z0-9_\-\.\/\?=&]+$/', $redirectParam)) {
        return $redirectParam;
    }
    return $default;
}

/**
 * Update user profile details
 * Strictly enforces that users can only update their own profile.
 */
function updateUserProfile($userId, $fullName, $phone = '', $address = '', $city = '', $postalCode = '') {
    // Authorization check: never allow a user to update another user's profile
    if (!isLoggedIn() || (int)$userId !== (int)$_SESSION['user_id']) {
        return ['success' => false, 'message' => 'Unauthorized action.'];
    }

    $fullName = trim($fullName);
    $phone = trim($phone);
    $address = trim($address);
    $city = trim($city);
    $postalCode = trim($postalCode);

    if (empty($fullName)) {
        return ['success' => false, 'message' => 'Full name cannot be empty.'];
    }

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                UPDATE `users` 
                SET `full_name` = ?, `phone` = ?, `address` = ?, `city` = ?, `postal_code` = ? 
                WHERE `id` = ?
            ");
            $stmt->execute([$fullName, $phone, $address, $city, $postalCode, $userId]);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update profile due to database error.'];
        }
    }

    if (!$pdo) {
        return ['success' => false, 'message' => 'Profile changes could not be saved because the database is unavailable.'];
    }

    // Update active session data
    $_SESSION['user_name'] = $fullName;
    if (isset($_SESSION['user_data'])) {
        $_SESSION['user_data']['full_name'] = $fullName;
        $_SESSION['user_data']['phone'] = $phone;
        $_SESSION['user_data']['address'] = $address;
        $_SESSION['user_data']['city'] = $city;
        $_SESSION['user_data']['postal_code'] = $postalCode;
    }

    return ['success' => true, 'message' => 'Your profile information has been updated successfully!'];
}

/**
 * Securely change the user's password
 * Requires current password verification using password_verify().
 */
function changeUserPassword($userId, $currentPassword, $newPassword, $confirmPassword) {
    // Authorization check
    if (!isLoggedIn() || (int)$userId !== (int)$_SESSION['user_id']) {
        return ['success' => false, 'message' => 'Unauthorized action.'];
    }

    $currentPassword = trim($currentPassword);
    $newPassword = trim($newPassword);
    $confirmPassword = trim($confirmPassword);

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        return ['success' => false, 'message' => 'Please fill in all password fields.'];
    }

    if (strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'New password must be at least 6 characters long.'];
    }

    if ($newPassword !== $confirmPassword) {
        return ['success' => false, 'message' => 'New password and confirmation do not match.'];
    }

    if ($currentPassword === $newPassword) {
        return ['success' => false, 'message' => 'New password must be different from your current password.'];
    }

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT `password` FROM `users` WHERE `id` = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Your current password was incorrect.'];
            }

            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ?");
            $updateStmt->execute([$newHash, $userId]);

            return ['success' => true, 'message' => 'Your password has been changed successfully!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error occurred while updating password.'];
        }
    }

    if (!$pdo) {
        return ['success' => false, 'message' => 'Password could not be changed because the database is unavailable.'];
    }

    return ['success' => false, 'message' => 'User not found.'];
}
