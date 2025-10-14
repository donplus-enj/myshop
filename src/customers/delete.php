<?php
/**
 * MyShop - 거래처 삭제
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$customer_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($customer_code)) {
    header('Location: list.php');
    exit;
}

// 거래 내역이 있는지 확인
$check_query = "SELECT COUNT(*) as count FROM transactions WHERE customer_code = ?";
$check_result = fetchOne($check_query, array($customer_code));

if ($check_result['success'] && $check_result['data']['count'] > 0) {
    header('Location: list.php?error=has_transactions');
    exit;
}

// 거래처 삭제
$delete_query = "DELETE FROM customers WHERE customer_code = ?";
$result = executeNonQuery($delete_query, array($customer_code));

if ($result['success']) {
    header('Location: list.php?success=deleted');
} else {
    header('Location: list.php?error=delete_failed');
}
exit;
?>