<?php
/**
 * MyShop - 상품 삭제
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$product_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($product_code)) {
    header('Location: list.php');
    exit;
}

// 거래 내역이 있는지 확인
$check_query = "SELECT COUNT(*) as count FROM transaction_items WHERE product_code = ?";
$check_result = fetchOne($check_query, array($product_code));

if ($check_result['success'] && $check_result['data']['count'] > 0) {
    header('Location: list.php?error=has_transactions');
    exit;
}

// 상품 삭제
$delete_query = "DELETE FROM products WHERE product_code = ?";
$result = executeNonQuery($delete_query, array($product_code));

if ($result['success']) {
    header('Location: list.php?success=deleted');
} else {
    header('Location: list.php?error=delete_failed');
}
exit;
?>