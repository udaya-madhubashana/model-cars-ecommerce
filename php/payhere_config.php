<?php
/**
 * ModelCars Pro - PayHere Sandbox Configuration
 *
 * IMPORTANT:
 * Keep this file out of GitHub. The project's .gitignore excludes it.
 * These credentials are for the PayHere Sandbox / localhost integration.
 */

define('PAYHERE_MERCHANT_ID', '1238451');
define('PAYHERE_MERCHANT_SECRET', 'MzAyOTQ0MjQyMDk4MjUxMjczNTA4Nzk0ODIyMTY4NjM1MDA5MA==');
define('PAYHERE_SANDBOX_URL', 'https://sandbox.payhere.lk/pay/checkout');
define('PAYHERE_CURRENCY', 'LKR');

/**
 * Build the mandatory PayHere MD5 hash.
 */
function generatePayHereHash($merchantId, $orderId, $amount, $currency, $merchantSecret) {
    $formattedAmount = number_format((float)$amount, 2, '.', '');
    $hashedSecret = strtoupper(md5($merchantSecret));

    return strtoupper(md5(
        $merchantId .
        $orderId .
        $formattedAmount .
        $currency .
        $hashedSecret
    ));
}

/**
 * Split a customer's full name into PayHere's first_name / last_name fields.
 */
function splitPayHereName($fullName) {
    $fullName = trim(preg_replace('/\s+/', ' ', $fullName));
    if ($fullName === '') {
        return ['first_name' => 'Customer', 'last_name' => ''];
    }

    $parts = explode(' ', $fullName);
    $firstName = array_shift($parts);
    $lastName = implode(' ', $parts);

    return [
        'first_name' => $firstName,
        'last_name' => $lastName
    ];
}
