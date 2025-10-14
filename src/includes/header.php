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

// 현재 페이지 경로 (활성 메뉴 표시용)
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

/**
 * 활성 메뉴 체크 함수
 * @param string $page 페이지명
 * @param string|null $dir 디렉토리명
 * @return string 'active' 또는 빈 문자열
 */
function isActive($page, $dir = null) {
    global $current_page, $current_dir;
    
    if ($dir) {
        return ($current_dir === $dir) ? 'active' : '';
    }
    
    return ($current_page === $page) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($page_title); ?> - MyShop</title>

	<!-- SVG 이모지 Favicon -->
	<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏪</text></svg>">

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
                        🚚 입출고
                    </a>
                    <a href="transactions/history.php" class="nav-link">
                        📋 거래조회
                    </a>
                    <a href="transactions/payment.php" class="nav-link">
                        💰 입금/지출
                    </a>
                    <a href="report.php" class="nav-link">
                        📊 통계/집계
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
