<?php
/**
 * ModelCars Pro - Order Processing & Management
 * File: php/orders.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/auth.php';

/**
 * Generate a unique customer-facing order number (e.g. MCP-2024-7891)
 */
function generateOrderNumber() {
    return 'MCP-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
}

/**
 * Process and create a new order from current cart contents
 */
function createOrder($orderData) {
    $cartItems = getCartItems();
    if (empty($cartItems)) {
        return [
            'success' => false,
            'message' => 'Your cart is empty. Please add items before checking out.'
        ];
    }

    // Validation
    $required = ['customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'city', 'postal_code', 'payment_method'];
    foreach ($required as $field) {
        if (empty($orderData[$field])) {
            return [
                'success' => false,
                'message' => 'Please fill in all required delivery and payment fields.'
            ];
        }
    }

    if (!filter_var($orderData['customer_email'], FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ];
    }

    $subtotal = getCartSubtotal();
    $shippingFee = getCartShippingFee($subtotal);
    $totalAmount = $subtotal + $shippingFee;
    $orderNumber = generateOrderNumber();
    $currentUser = getCurrentUser();
    $userId = $currentUser ? $currentUser['id'] : null;

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO `orders` (
                    `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, 
                    `shipping_address`, `city`, `postal_code`, `payment_method`, `subtotal`, 
                    `shipping_fee`, `total_amount`, `order_status`
                ) VALUES (
                    :order_number, :user_id, :customer_name, :customer_email, :customer_phone, 
                    :shipping_address, :city, :postal_code, :payment_method, :subtotal, 
                    :shipping_fee, :total_amount, 'Pending'
                )
            ");

            $stmt->execute([
                ':order_number' => $orderNumber,
                ':user_id' => $userId,
                ':customer_name' => trim($orderData['customer_name']),
                ':customer_email' => trim($orderData['customer_email']),
                ':customer_phone' => trim($orderData['customer_phone']),
                ':shipping_address' => trim($orderData['shipping_address']),
                ':city' => trim($orderData['city']),
                ':postal_code' => trim($orderData['postal_code']),
                ':payment_method' => $orderData['payment_method'],
                ':subtotal' => $subtotal,
                ':shipping_fee' => $shippingFee,
                ':total_amount' => $totalAmount
            ]);

            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("
                INSERT INTO `order_items` (
                    `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `total_price`
                ) VALUES (
                    :order_id, :product_id, :product_name, :product_price, :quantity, :total_price
                )
            ");

            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product']['id'],
                    ':product_name' => $item['product']['name'],
                    ':product_price' => $item['unit_price'],
                    ':quantity' => $item['quantity'],
                    ':total_price' => $item['line_total']
                ]);
            }

            $pdo->commit();

            // Clear cart
            clearCart();

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'customer_name' => $orderData['customer_name'],
                'customer_email' => $orderData['customer_email'],
                'payment_method' => $orderData['payment_method']
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }

    // Fallback: in-memory / session order storage
    $_SESSION['last_order'] = [
        'order_number' => $orderNumber,
        'customer_name' => $orderData['customer_name'],
        'customer_email' => $orderData['customer_email'],
        'customer_phone' => $orderData['customer_phone'],
        'shipping_address' => $orderData['shipping_address'] . ', ' . $orderData['city'] . ' (' . $orderData['postal_code'] . ')',
        'payment_method' => $orderData['payment_method'],
        'subtotal' => $subtotal,
        'shipping_fee' => $shippingFee,
        'total_amount' => $totalAmount,
        'items' => $cartItems,
        'created_at' => date('Y-m-d H:i:s')
    ];

    clearCart();

    return [
        'success' => true,
        'order_number' => $orderNumber,
        'total_amount' => $totalAmount,
        'customer_name' => $orderData['customer_name'],
        'customer_email' => $orderData['customer_email'],
        'payment_method' => $orderData['payment_method']
    ];
}

/**
 * Retrieve details for a placed order
 */
function getOrderDetails($orderNumber) {
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `order_number` = ? LIMIT 1");
            $stmt->execute([$orderNumber]);
            $order = $stmt->fetch();
            if ($order) {
                $itemStmt = $pdo->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
                $itemStmt->execute([$order['id']]);
                $order['items'] = $itemStmt->fetchAll();
                return $order;
            }
        } catch (Exception $e) {}
    }

    // Fallback: check session last_order
    if (isset($_SESSION['last_order']) && $_SESSION['last_order']['order_number'] === $orderNumber) {
        return $_SESSION['last_order'];
    }

    return null;
}
