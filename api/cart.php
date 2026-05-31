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

$csrf_token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$action = $_POST['action'] ?? '';

try {
    $cart_id = null;

    // Handle Database Cart for Logged-in Users
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch();

        if (!$cart) {
            $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            $cart_id = $pdo->lastInsertId();
        } else {
            $cart_id = $cart['id'];
        }
    } else {
        // Handle Session Cart for Guest Users
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
    }

    if ($action === 'add') {
        $variant_id = filter_input(INPUT_POST, 'variant_id', FILTER_VALIDATE_INT);
        $quantity = 1; // Default to 1 for direct add

        if (!$variant_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid variant ID']);
            exit;
        }

        if ($user_id) {
            // DB logic
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND variant_id = ?");
            $stmt->execute([$cart_id, $variant_id]);
            $item = $stmt->fetch();

            if ($item) {
                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?");
                $stmt->execute([$item['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$cart_id, $variant_id, $quantity]);
            }
        } else {
            // Session logic
            if (isset($_SESSION['guest_cart'][$variant_id])) {
                $_SESSION['guest_cart'][$variant_id] += 1;
            } else {
                $_SESSION['guest_cart'][$variant_id] = 1;
            }
        }
        echo json_encode(['success' => true, 'message' => 'Item added to cart']);

    } elseif ($action === 'update') {
        $cart_item_id = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if (!$cart_item_id || !$quantity || $quantity < 1) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        if ($user_id) {
            // DB logic: here cart_item_id maps directly to cart_items.id
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?");
            $stmt->execute([$quantity, $cart_item_id, $cart_id]);
        } else {
            // Session logic: frontend sends variant_id as cart_item_id for guests
            if (isset($_SESSION['guest_cart'][$cart_item_id])) {
                $_SESSION['guest_cart'][$cart_item_id] = $quantity;
            }
        }
        echo json_encode(['success' => true, 'message' => 'Quantity updated']);

    } elseif ($action === 'remove') {
        $cart_item_id = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);

        if (!$cart_item_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        if ($user_id) {
            // DB logic
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
            $stmt->execute([$cart_item_id, $cart_id]);
        } else {
            // Session logic
            if (isset($_SESSION['guest_cart'][$cart_item_id])) {
                unset($_SESSION['guest_cart'][$cart_item_id]);
            }
        }
        echo json_encode(['success' => true, 'message' => 'Item removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
