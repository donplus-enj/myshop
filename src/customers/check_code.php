<?php
/**
 * MyShop - 상품 코드 중복 체크 API
 * AJAX 요청으로 사용
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// JSON 응답 헤더 설정
header('Content-Type: application/json');

// 로그인 체크
if (!isLoggedIn()) {
    echo json_encode(array(
        'success' => false,
        'message' => '로그인이 필요합니다.'
    ));
    exit;
}

// GET 파라미터로 코드 받기
$code = isset($_GET['code']) ? trim($_GET['code']) : '';

// 코드 검증
if (empty($code)) {
    echo json_encode(array(
        'success' => false,
        'message' => '코드를 입력해주세요.',
        'isDuplicate' => false
    ));
    exit;
}

// 코드 형식 검증 (4자리 숫자)
if (!preg_match('/^[0-9]{4}$/', $code)) {
    echo json_encode(array(
        'success' => false,
        'message' => '코드는 4자리 숫자여야 합니다.',
        'isDuplicate' => false
    ));
    exit;
}

// 수정 모드인 경우 현재 코드 제외
$current_code = isset($_GET['current']) ? trim($_GET['current']) : '';

// 중복 체크 쿼리
if (!empty($current_code) && $current_code === $code) {
    // 현재 코드와 동일한 경우 중복 아님
    echo json_encode(array(
        'success' => true,
        'isDuplicate' => false,
        'message' => '현재 사용 중인 코드입니다.'
    ));
    exit;
}

$query = "SELECT COUNT(*) as count FROM products WHERE product_code = ?";
$result = fetchOne($query, array($code));

if ($result['success']) {
    $count = $result['data']['count'];
    $isDuplicate = ($count > 0);
    
    echo json_encode(array(
        'success' => true,
        'isDuplicate' => $isDuplicate,
        'message' => $isDuplicate ? '이미 사용 중인 코드입니다.' : '사용 가능한 코드입니다.'
    ));
} else {
    echo json_encode(array(
        'success' => false,
        'message' => '코드 중복 체크 중 오류가 발생했습니다.',
        'isDuplicate' => false
    ));
}
?>