<?php
/**
 * ModelCars Pro - Customer Logout Handler
 * File: logout.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/auth.php';

// Log out user
$logoutResult = logoutUser();

// Set flash message and redirect to homepage
setFlashMessage('info', $logoutResult['message'] ?? 'You have been logged out.');
redirect('index.php');
