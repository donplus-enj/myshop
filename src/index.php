<?php
/**
 * MyShop - 메인 페이지
 * 재고관리 및 거래처 관리 시스템 대시보드
 */

// 페이지 타이틀 설정
$page_title = '메인';

// 공통 헤더 로드 (로그인 체크 포함)
require_once 'includes/header.php';
//requireLogin();

// 로그인하지 않은 경우 login 페이지로 이동
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>

<!-- 환영 메시지 -->
<div class="welcome-card">
    <h2>환영합니다! 🎉</h2>
    <p>MyShop 상품 재고 및 거래처 관리 시스템</p>
</div>

<!-- 메뉴 그리드 -->
<div class="menu-grid">
    <!-- 거래처 관리 -->
    <a href="customers/list.php" class="menu-card">
        <div class="icon">🏢</div>
        <h3>거래처 관리</h3>
        <p>거래처 등록 및 관리</p>
    </a>
    
    <!-- 상품 관리 -->
    <a href="products/list.php" class="menu-card">
        <div class="icon">📦</div>
        <h3>상품 관리</h3>
        <p>상품 등록 및 관리</p>
    </a>
    
    <!-- 입출고 관리 -->
    <a href="transactions/in_out.php" class="menu-card">
        <div class="icon">📊</div>
        <h3>입출고 관리</h3>
        <p>입고/출고/반품 처리</p>
    </a>
    
    <!-- 거래내역 조회 -->
    <a href="transactions/history.php" class="menu-card">
        <div class="icon">📋</div>
        <h3>거래조회</h3>
        <p>거래내역 조회 및 출력</p>
    </a>
    
    <!-- 수금/지급 관리 -->
    <a href="transactions/payment.php" class="menu-card disabled">
        <div class="icon">💰</div>
        <h3>입금/지출</h3>
        <p>입금(수금) 및 지출(지급) 관리</p>
        <span class="coming-soon">준비중</span>
    </a>
    
    <!-- 통계/보고서 -->
    <a href="reports.php" class="menu-card disabled">
        <div class="icon">📈</div>
        <h3>통계/집계</h3>
        <p>각종 통계/집계 보고서</p>
        <span class="coming-soon">준비중</span>
    </a>
</div>

<?php
// 공통 푸터 로드
require_once 'includes/footer.php';
?>