<artifact identifier="myshop-index-main-page" type="application/vnd.ant.code" language="php" title="index.php - 메인 페이지">
<?php
/**
 * MyShop - 로그인 페이지
 */

require_once 'config/database.php';
require_once 'includes/session.php';

// 로그인하지 않은 경우 login 페이지로 이동
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// 현재 로그인한 사용자 정보
$current_user = getLoginUser();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyShop - 메인</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background: #f5f5f5;
		}

		/* 헤더 */
		.header {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			padding: 20px 40px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}
    
		.header-content {
			max-width: 1200px;
			margin: 0 auto;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
    
		.header h1 {
			font-size: 24px;
		}
    
		.user-info {
			display: flex;
			align-items: center;
			gap: 20px;
		}
    
		.user-name {
			font-size: 14px;
		}
    
		.btn-logout {
			background: rgba(255,255,255,0.2);
			color: white;
			border: 1px solid rgba(255,255,255,0.3);
			padding: 8px 16px;
			border-radius: 5px;
			cursor: pointer;
			text-decoration: none;
			font-size: 14px;
			transition: all 0.3s;
		}
    
		.btn-logout:hover {
			background: rgba(255,255,255,0.3);
		}
    
		/* 메인 컨텐츠 */
		.container {
			max-width: 1200px;
			margin: 40px auto;
			padding: 0 20px;
		}
    
		.welcome-card {
			background: white;
			border-radius: 10px;
			padding: 40px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
			margin-bottom: 30px;
			text-align: center;
		}
    
		.welcome-card h2 {
			color: #333;
			font-size: 28px;
			margin-bottom: 10px;
		}
    
		.welcome-card p {
			color: #666;
			font-size: 16px;
		}
    
		/* 메뉴 그리드 */
		.menu-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 20px;
			margin-top: 30px;
		}
    
		.menu-card {
			background: white;
			border-radius: 10px;
			padding: 30px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
			text-align: center;
			cursor: pointer;
			transition: all 0.3s;
			text-decoration: none;
			color: inherit;
		}
    
		.menu-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 5px 20px rgba(0,0,0,0.15);
		}
    
		.menu-card .icon {
			font-size: 48px;
			margin-bottom: 15px;
		}
    
		.menu-card h3 {
			color: #333;
			font-size: 20px;
			margin-bottom: 10px;
		}
    
		.menu-card p {
			color: #666;
			font-size: 14px;
		}
    
		.menu-card.disabled {
			opacity: 0.5;
			cursor: not-allowed;
		}
    
		.menu-card.disabled:hover {
			transform: none;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}
    
		.coming-soon {
			display: inline-block;
			background: #ffeaa7;
			color: #d63031;
			padding: 4px 8px;
			border-radius: 4px;
			font-size: 12px;
			margin-top: 10px;
		}
	</style>
</head>

<body>
    <!-- 헤더 -->
    <div class="header">
        <div class="header-content">
            <h1>🏪 MyShop</h1>
            <div class="user-info">
                <span class="user-name">
                    👤 <?php echo escape($current_user['user_name'] ?? '사용자'); ?> 
                    (<?php echo escape($current_user['user_code'] ?? ''); ?>)
                </span>
                <a href="logout.php" class="btn-logout">로그아웃</a>
            </div>
        </div>
    </div>

	<!-- 메인 컨텐츠 -->
	<div class="container">
		<!-- 환영 메시지 -->
		<div class="welcome-card">
			<h2>환영합니다! 🎉</h2>
			<p>MyShop 재고관리 및 거래처 관리 시스템</p>
		</div>
		
		<!-- 메뉴 그리드 -->
		<div class="menu-grid">
			<!-- 거래처 관리 -->
			<a href="clients.php" class="menu-card disabled">
				<div class="icon">🏢</div>
				<h3>거래처 관리</h3>
				<p>거래처 등록 및 관리</p>
				<span class="coming-soon">준비중</span>
			</a>
			
			<!-- 상품 관리 -->
			<a href="products.php" class="menu-card disabled">
				<div class="icon">📦</div>
				<h3>상품 관리</h3>
				<p>상품 등록 및 관리</p>
				<span class="coming-soon">준비중</span>
			</a>
			
			<!-- 입출고 관리 -->
			<a href="inventory.php" class="menu-card disabled">
				<div class="icon">📊</div>
				<h3>입출고 관리</h3>
				<p>입고/출고/반품 처리</p>
				<span class="coming-soon">준비중</span>
			</a>
			
			<!-- 거래내역 조회 -->
			<a href="transactions.php" class="menu-card disabled">
				<div class="icon">📋</div>
				<h3>거래내역</h3>
				<p>거래내역 조회 및 출력</p>
				<span class="coming-soon">준비중</span>
			</a>
			
			<!-- 수금/지급 관리 -->
			<a href="payments.php" class="menu-card disabled">
				<div class="icon">💰</div>
				<h3>수금/지급</h3>
				<p>수금 및 지급 관리</p>
				<span class="coming-soon">준비중</span>
			</a>
			
			<!-- 통계/보고서 -->
			<a href="reports.php" class="menu-card disabled">
				<div class="icon">📈</div>
				<h3>통계/보고서</h3>
				<p>각종 통계 및 보고서</p>
				<span class="coming-soon">준비중</span>
			</a>
		</div>
	</div>
</body>
</html>