<?php
/**
 * ModelCars Pro - Shopping Cart Logic & AJAX Handler
 * File: php/cart.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/products.php';

// Initialize session cart structure if missing
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Add a product to the cart
 */
function addToCart($productId, $quantity = 1) {
    $productId = (int)$productId;
    $quantity = max(1, (int)$quantity);

    $product = getProductById($productId);
    if (!$product) {
        return ['success' => false, 'message' => 'Product not found.'];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }

    // Ensure we don't exceed stock limit if defined
    if (isset($product['stock_quantity']) && $_SESSION['cart'][$productId] > $product['stock_quantity']) {
        $_SESSION['cart'][$productId] = $product['stock_quantity'];
    }

    return [
        'success' => true,
        'message' => 'Added ' . $product['name'] . ' to cart!',
        'cartCount' => getCartCount(),
        'cartSubtotal' => getCartSubtotal()
    ];
}

/**
 * Update quantity for a specific product
 */
function updateCartQuantity($productId, $quantity) {
    $productId = (int)$productId;
    $quantity = (int)$quantity;

    if ($quantity <= 0) {
        return removeFromCart($productId);
    }

    if (isset($_SESSION['cart'][$productId])) {
        $product = getProductById($productId);
        if ($product && isset($product['stock_quantity']) && $quantity > $product['stock_quantity']) {
            $quantity = $product['stock_quantity'];
        }
        $_SESSION['cart'][$productId] = $quantity;
    }

    return [
        'success' => true,
        'message' => 'Cart updated.',
        'cartCount' => getCartCount(),
        'cartSubtotal' => getCartSubtotal()
    ];
}

/**
 * Remove an item from the cart
 */
function removeFromCart($productId) {
    $productId = (int)$productId;
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    return [
        'success' => true,
        'message' => 'Item removed from cart.',
        'cartCount' => getCartCount(),
        'cartSubtotal' => getCartSubtotal()
    ];
}

/**
 * Clear all items in the cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Total item count across all lines in cart
 */
function getCartCount() {
    $count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $qty) {
            $count += (int)$qty;
        }
    }
    return $count;
}

/**
 * Return fully populated items currently in cart
 */
function getCartItems() {
    $items = [];
    if (empty($_SESSION['cart'])) {
        return $items;
    }

    foreach ($_SESSION['cart'] as $productId => $qty) {
        $product = getProductById($productId);
        if ($product) {
            $qty = (int)$qty;
            $lineTotal = (float)$product['price'] * $qty;
            $items[] = [
                'product' => $product,
                'quantity' => $qty,
                'unit_price' => (float)$product['price'],
                'line_total' => $lineTotal
            ];
        }
    }
    return $items;
}

/**
 * Calculate cart subtotal
 */
function getCartSubtotal() {
    $subtotal = 0.0;
    $items = getCartItems();
    foreach ($items as $item) {
        $subtotal += $item['line_total'];
    }
    return $subtotal;
}

/**
 * Calculate shipping fee based on threshold
 */
function getCartShippingFee($subtotal) {
    if ($subtotal <= 0) return 0.0;
    if ($subtotal >= FREE_SHIPPING_THRESHOLD) {
        return 0.0;
    }
    return (float)STANDARD_SHIPPING_FEE;
}

/**
 * Calculate grand total
 */
function getCartGrandTotal() {
    $subtotal = getCartSubtotal();
    $shipping = getCartShippingFee($subtotal);
    return $subtotal + $shipping;
}

// ------------------------------------------------------------------------------
// Handle direct AJAX Cart requests
// ------------------------------------------------------------------------------
if (isset($_REQUEST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_REQUEST['ajax_action'];
    $response = ['success' => false, 'message' => 'Invalid action'];

    if ($action === 'add') {
        $productId = $_POST['product_id'] ?? $_GET['product_id'] ?? 0;
        $qty = $_POST['quantity'] ?? $_GET['quantity'] ?? 1;
        $response = addToCart($productId, $qty);
    } elseif ($action === 'update') {
        $productId = $_POST['product_id'] ?? 0;
        $qty = $_POST['quantity'] ?? 1;
        $response = updateCartQuantity($productId, $qty);
    } elseif ($action === 'remove') {
        $productId = $_POST['product_id'] ?? $_GET['product_id'] ?? 0;
        $response = removeFromCart($productId);
    } elseif ($action === 'get_count') {
        $response = [
            'success' => true,
            'count' => getCartCount(),
            'subtotal' => getCartSubtotal(),
            'formatted_subtotal' => formatPrice(getCartSubtotal())
        ];
    }

    echo json_encode($response);
    exit;
}
