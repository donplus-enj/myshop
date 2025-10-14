<?php
/**
 * MyShop - 거래 상세보기
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '거래 상세보기';
$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($transaction_id == 0) {
    header('Location: history.php');
    exit;
}

// 거래 헤더 정보 조회
$header_query = "SELECT 
    t.*,
    c.customer_name, c.phone, c.mobile,
    u.user_name
FROM transactions t
INNER JOIN customers c ON t.customer_code = c.customer_code
INNER JOIN users u ON t.user_code = u.user_code
WHERE t.transaction_id = ?";

$header_result = fetchOne($header_query, array($transaction_id));

if (!$header_result['success'] || !$header_result['data']) {
    header('Location: history.php');
    exit;
}

$transaction = $header_result['data'];

// 거래 상세 정보 조회
$items_query = "SELECT 
    ti.*,
    p.product_name, p.product_spec
FROM transaction_items ti
INNER JOIN products p ON ti.product_code = p.product_code
WHERE ti.transaction_id = ?
ORDER BY ti.item_id";

$items