<?php
require_once 'config.php';

header('Content-Type: application/json');

$cart_count = 0;

if (isLoggedIn()) {
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $cart_count = $result['count'] ?? 0;
    } catch (Exception $e) {
        // If there's an error, return 0
        $cart_count = 0;
    }
}

echo json_encode(['count' => $cart_count]);
?>