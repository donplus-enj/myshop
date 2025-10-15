<?php
/**
 * MyShop - 공통 헤더
 * 모든 페이지에서 include하여 사용
 */

// 보안 상수 정의
if (!defined('MYSHOP_APP')) {
    define('MYSHOP_APP', true);
}

// 세션 및 DB 연결
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

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
 * 루트 경로 계산
 * 현재 파일의 위치에 따라 루트까지의 상대 경로 반환
 */
function getBasePath_old() {
    // 현재 스크립트의 경로 가져오기
    $script_path = $_SERVER['SCRIPT_NAME'];
    
    // '/src/' 기준으로 분리
    $parts = explode('/', trim($script_path, '/'));
    
    // 'src' 이후의 depth 계산
    $depth = 0;
    $found_src = false;
    foreach ($parts as $part) {
        if ($found_src && !empty($part)) {
            $depth++;
        }
        if ($part === 'src') {
            $found_src = true;
        }
    }
    
    // depth에 따라 '../' 반복
    if ($depth === 0) {
        return './';
    } elseif ($depth === 1) {
        return '../';
    } else {
        return str_repeat('../', $depth - 1);
    }
}

/**
 * 루트 경로 계산
 * 현재 파일의 위치에 따라 루트까지의 상대 경로 반환
 */
function getBasePath() {
    $script_name = $_SERVER['SCRIPT_NAME'];
    
    // '/src/' 이후의 경로 추출
    $src_pos = strpos($script_name, '/src/');
    
    if ($src_pos !== false) {
        // '/src/' 다음부터 끝까지 추출
        $after_src = substr($script_name, $src_pos + 5); // '/src/' = 5글자
        
        // 디렉토리 깊이 계산 (파일명은 제외)
        $path_parts = explode('/', $after_src);
        array_pop($path_parts); // 마지막 요소(파일명) 제거
        
        // 빈 문자열 제외하고 카운트
        $depth = count(array_filter($path_parts));
        
        if ($depth > 0) {
            return str_repeat('../', $depth);
        }
    }
    
    return './';
}

// 기본 경로 설정
$base_path = getBasePath();

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
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    
    <!-- 페이지별 추가 CSS -->
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $base_path . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- 헤더 -->
    <div class="header">
        <div class="header-content">
            <h1>
                <a href="<?php echo $base_path; ?>index.php">🏪 MyShop</a>
            </h1>
            
            <?php if (isLoggedIn()): ?>
                <!-- 메인 네비게이션 -->
                <nav class="main-nav">
                    <a href="<?php echo $base_path; ?>index.php" class="nav-link <?php echo isActive('index.php'); ?>">
                        🏠 홈
                    </a>
                    <a href="<?php echo $base_path; ?>customers/list.php" class="nav-link <?php echo isActive('list.php', 'customers'); ?>">
                        🏢 거래처
                    </a>
                    <a href="<?php echo $base_path; ?>products/list.php" class="nav-link <?php echo isActive('list.php', 'products'); ?>">
                        📦 상품
                    </a>
                    <a href="<?php echo $base_path; ?>transactions/in_out.php" class="nav-link <?php echo isActive('in_out.php', 'transactions'); ?>">
                        🚚 입출고
                    </a>
                    <a href="<?php echo $base_path; ?>transactions/history.php" class="nav-link <?php echo isActive('history.php', 'transactions'); ?>">
                        📋 거래조회
                    </a>
                    <a href="<?php echo $base_path; ?>transactions/payment.php" class="nav-link <?php echo isActive('payment.php', 'transactions'); ?>">
                        💰 입금/지출
                    </a>
                    <a href="<?php echo $base_path; ?>report.php" class="nav-link <?php echo isActive('report.php'); ?>">
                        📊 통계/집계
                    </a>
                </nav>
                
                <!-- 사용자 정보 -->
                <div class="user-info">
                    <span class="user-name">
                        👤 <?php echo escape($current_user['user_name']); ?>
                        (<?php echo escape($current_user['user_code']); ?>)
                    </span>
                    <a href="<?php echo $base_path; ?>logout.php" class="btn btn-light btn-sm">
                        로그아웃
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 메인 컨테이너 시작 -->
    <div class="container">