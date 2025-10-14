<?php
/**
 * MyShop - 공통 헤더
 * 모든 페이지에서 include하여 사용
 */

// 세션 및 DB 연결
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';

// 로그인 필수 페이지에서 사용
// requireLogin(); // 필요한 페이지에서 개별 호출

// 로그인한 사용자 정보
$current_user = getLoginUser();

// 페이지 타이틀 (각 페이지에서 $page_title 설정 가능)
$page_title = $page_title ?? 'MyShop';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($page_title); ?> - MyShop</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- 페이지별 추가 CSS -->
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- 헤더 -->
    <div class="header">
        <div class="header-content">
            <h1>
                <a href="index.php">🏪 MyShop</a>
            </h1>
            
            <?php if (isLoggedIn()): ?>
                <!-- 메인 네비게이션 -->
                <nav class="main-nav">
                    <a href="index.php" class="nav-link">
                        🏠 홈
                    </a>
                    <a href="customers/list.php" class="nav-link">
                        🏢 거래처
                    </a>
                    <a href="products/list.php" class="nav-link">
                        📦 상품
                    </a>
                    <a href="transactions/in_out.php" class="nav-link">
                        📊 입출고
                    </a>
                    <a href="transactions/history.php" class="nav-link">
                        📋 거래내역
                    </a>
                    <a href="transactions/payment.php" class="nav-link">
                        💰 수금/지급
                    </a>
                </nav>
                
                <!-- 사용자 정보 -->
                <div class="user-info">
                    <span class="user-name">
                        👤 <?php echo escape($current_user['user_name']); ?>
                        (<?php echo escape($current_user['user_code']); ?>)
                    </span>
                    <a href="logout.php" class="btn btn-light btn-sm">
                        로그아웃
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 메인 컨테이너 시작 -->
    <div class="container">
