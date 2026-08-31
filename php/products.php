<?php
/**
 * ModelCars Pro - Product Query & Data Functions
 * File: php/products.php
 */

require_once __DIR__ . '/config.php';

/**
 * Return default in-memory seed products (used when MySQL is offline)
 */
function getDefaultMockProducts() {
    return [
        [
            'id' => 1,
            'category_id' => 1,
            'category_name' => 'Sports Cars',
            'brand' => 'Hot Wheels',
            'name' => 'Ferrari F40 Die-Cast',
            'slug' => 'ferrari-f40-die-cast',
            'scale' => '1:64',
            'description' => 'The iconic 1987 Ferrari F40 rendered in stunning high-gloss crimson red. Features detailed dual pop-up style headlamp markings, aerodynamic rear wing, authentic star-spoke racing wheels, and twin-turbo V8 engine cover contours.',
            'price' => 599.00,
            'old_price' => 899.00,
            'stock_quantity' => 15,
            'rating' => 5.0,
            'reviews_count' => 24,
            'badge' => 'Hot Deal',
            'image' => 'Ferrari F40.jpeg',
            'is_featured' => 1
        ],
        [
            'id' => 2,
            'category_id' => 4,
            'category_name' => 'Limited Edition',
            'brand' => 'Matchbox',
            'name' => 'Lamborghini Countach 1:64',
            'slug' => 'lamborghini-countach-1-64',
            'scale' => '1:64',
            'description' => 'A vibrant Giallo Fly yellow Lamborghini Countach 5000 Quattrovalvole scale model. Features radical Bertone wedge lines, flared rear arches, rear wing spoiler, and collector display pedestal.',
            'price' => 750.00,
            'old_price' => null,
            'stock_quantity' => 8,
            'rating' => 4.8,
            'reviews_count' => 18,
            'badge' => 'Limited',
            'image' => 'Lamborghini Countach.jpeg',
            'is_featured' => 1
        ],
        [
            'id' => 3,
            'category_id' => 1,
            'category_name' => 'Sports Cars',
            'brand' => 'Hot Wheels',
            'name' => 'Bugatti Chiron Special Edition',
            'slug' => 'bugatti-chiron-special-edition',
            'scale' => '1:64',
            'description' => 'Signature two-tone metallic French Racing Blue and Nocturne Black Bugatti Chiron miniature. Features precision horseshoe grille casting, quad-LED optical headlamp stamps, and aerodynamic side C-line curvature.',
            'price' => 699.00,
            'old_price' => null,
            'stock_quantity' => 20,
            'rating' => 4.9,
            'reviews_count' => 32,
            'badge' => 'New',
            'image' => 'Bugatti Chiron.jpeg',
            'is_featured' => 1
        ],
        [
            'id' => 4,
            'category_id' => 6,
            'category_name' => 'Premium Collection',
            'brand' => 'Tamiya',
            'name' => 'Porsche 911 GT2 Precision Model',
            'slug' => 'porsche-911-gt2-precision-model',
            'scale' => '1:24',
            'description' => 'Pure Grand Prix White Porsche 993 GT2 competition model with oversized carbon-fiber bi-plane rear wing, authentic #24 Shell/Bosch competition livery, race-spec BBS multi-piece modular wheels, and roll-cage interior.',
            'price' => 1299.00,
            'old_price' => null,
            'stock_quantity' => 6,
            'rating' => 4.7,
            'reviews_count' => 21,
            'badge' => null,
            'image' => 'Porsche 911 GT2.jpeg',
            'is_featured' => 1
        ],
        [
            'id' => 5,
            'category_id' => 1,
            'category_name' => 'Sports Cars',
            'brand' => 'Hot Wheels',
            'name' => 'McLaren F1 Collector\'s Series',
            'slug' => 'mclaren-f1-collectors-series',
            'scale' => '1:64',
            'description' => 'Brilliant metallic magnesium silver McLaren F1 3-seater supercar model. Meticulously contoured dihedral door shutlines, central driver seat visualization, and quad exhaust array.',
            'price' => 599.00,
            'old_price' => 799.00,
            'stock_quantity' => 12,
            'rating' => 5.0,
            'reviews_count' => 45,
            'badge' => 'Popular',
            'image' => 'McLaren F1 .jpeg',
            'is_featured' => 1
        ],
        [
            'id' => 6,
            'category_id' => 2,
            'category_name' => 'Classics',
            'brand' => 'Matchbox',
            'name' => 'Classic Jaguar E-Type Vintage',
            'slug' => 'classic-jaguar-e-type-vintage',
            'scale' => '1:43',
            'description' => 'Timeless British Racing Green Jaguar E-Type Series 1 Fixed Head Coupe. Classic wire-spoke chrome wheels with knock-off hubs, dual exhaust tips, wooden steering wheel interior accent, and E-TYPE 1 license plate.',
            'price' => 850.00,
            'old_price' => null,
            'stock_quantity' => 10,
            'rating' => 4.6,
            'reviews_count' => 19,
            'badge' => null,
            'image' => 'Classic Jaguar E-Type.jpeg',
            'is_featured' => 1
        ]
    ];
}

/**
 * Return default in-memory categories
 */
function getDefaultMockCategories() {
    return [
        ['id' => 1, 'name' => 'Sports Cars', 'slug' => 'sports-cars', 'icon' => '🏁', 'count' => 245],
        ['id' => 2, 'name' => 'Classics', 'slug' => 'classics', 'icon' => '🚗', 'count' => 128],
        ['id' => 3, 'name' => 'Vintage', 'slug' => 'vintage', 'icon' => '🔧', 'count' => 89],
        ['id' => 4, 'name' => 'Limited Edition', 'slug' => 'limited-edition', 'icon' => '⭐', 'count' => 42],
        ['id' => 5, 'name' => 'Gift Sets', 'slug' => 'gift-sets', 'icon' => '🎁', 'count' => 67],
        ['id' => 6, 'name' => 'Premium Collection', 'slug' => 'premium-collection', 'icon' => '🏆', 'count' => 156]
    ];
}

/**
 * Fetch all categories
 */
function getCategories() {
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC");
            $cats = $stmt->fetchAll();
            if (!empty($cats)) return $cats;
        } catch (Exception $e) {}
    }
    return getDefaultMockCategories();
}

/**
 * Fetch distinct brands for filter sidebar
 */
function getBrands() {
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT DISTINCT brand FROM products ORDER BY brand ASC");
            $brands = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($brands)) return $brands;
        } catch (Exception $e) {}
    }
    return ['Hot Wheels', 'Matchbox', 'Tamiya'];
}

/**
 * Fetch all products with optional filters
 */
function getAllProducts($categoryId = null, $brand = null, $sort = 'featured', $search = null) {
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
            $params = [];

            if (!empty($categoryId)) {
                $sql .= " AND p.category_id = :cat_id";
                $params[':cat_id'] = $categoryId;
            }

            if (!empty($brand)) {
                $sql .= " AND p.brand = :brand";
                $params[':brand'] = $brand;
            }

            if (!empty($search)) {
                $sql .= " AND (p.name LIKE :search OR p.brand LIKE :search OR p.description LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            switch ($sort) {
                case 'price_low':
                    $sql .= " ORDER BY p.price ASC";
                    break;
                case 'price_high':
                    $sql .= " ORDER BY p.price DESC";
                    break;
                case 'rating':
                    $sql .= " ORDER BY p.rating DESC";
                    break;
                case 'newest':
                    $sql .= " ORDER BY p.id DESC";
                    break;
                case 'featured':
                default:
                    $sql .= " ORDER BY p.is_featured DESC, p.id ASC";
                    break;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            if (!empty($results)) return $results;
        } catch (Exception $e) {}
    }

    // In-memory fallback
    $products = getDefaultMockProducts();

    if (!empty($categoryId)) {
        $products = array_filter($products, function($p) use ($categoryId) {
            return $p['category_id'] == $categoryId;
        });
    }

    if (!empty($brand)) {
        $products = array_filter($products, function($p) use ($brand) {
            return strcasecmp($p['brand'], $brand) === 0;
        });
    }

    if (!empty($search)) {
        $q = strtolower($search);
        $products = array_filter($products, function($p) use ($q) {
            return strpos(strtolower($p['name']), $q) !== false ||
                   strpos(strtolower($p['brand']), $q) !== false ||
                   strpos(strtolower($p['description']), $q) !== false;
        });
    }

    // Sort
    usort($products, function($a, $b) use ($sort) {
        if ($sort === 'price_low') return $a['price'] <=> $b['price'];
        if ($sort === 'price_high') return $b['price'] <=> $a['price'];
        if ($sort === 'rating') return $b['rating'] <=> $a['rating'];
        if ($sort === 'newest') return $b['id'] <=> $a['id'];
        return $b['is_featured'] <=> $a['is_featured'];
    });

    return array_values($products);
}

/**
 * Fetch featured products for the homepage
 */
function getFeaturedProducts($limit = 6) {
    $products = getAllProducts(null, null, 'featured');
    return array_slice($products, 0, $limit);
}

/**
 * Fetch single product by ID
 */
function getProductById($id) {
    $id = (int)$id;
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? LIMIT 1");
            $stmt->execute([$id]);
            $res = $stmt->fetch();
            if ($res) return $res;
        } catch (Exception $e) {}
    }

    // Fallback search in mock
    $products = getDefaultMockProducts();
    foreach ($products as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

/**
 * Get related products within the same category
 */
function getRelatedProducts($currentProductId, $categoryId, $limit = 3) {
    $products = getAllProducts($categoryId);
    $related = [];
    foreach ($products as $p) {
        if ($p['id'] != $currentProductId) {
            $related[] = $p;
            if (count($related) >= $limit) break;
        }
    }
    // If not enough in category, pull general products
    if (count($related) < $limit) {
        $all = getDefaultMockProducts();
        foreach ($all as $p) {
            if ($p['id'] != $currentProductId && !in_array($p, $related)) {
                $related[] = $p;
                if (count($related) >= $limit) break;
            }
        }
    }
    return $related;
}
