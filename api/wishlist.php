<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to use the wishlist']);
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    // Ensure table exists (in a real app, this should only run once during setup)
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id)
    )");

    if ($action === 'toggle') {
        $stmt = $pdo->prepare("SELECT id FROM wishlist_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);

        if ($stmt->fetch()) {
            $delStmt = $pdo->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?");
            $delStmt->execute([$user_id, $product_id]);
            echo json_encode(['success' => true, 'status' => 'removed']);
        } else {
            $insStmt = $pdo->prepare("INSERT INTO wishlist_items (user_id, product_id) VALUES (?, ?)");
            $insStmt->execute([$user_id, $product_id]);
            echo json_encode(['success' => true, 'status' => 'added']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
