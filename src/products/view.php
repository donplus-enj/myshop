<?php
/**
 * MyShop - 상품 상세보기
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '상품 상세보기';
$product_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($product_code)) {
    header('Location: list.php');
    exit;
}

// 상품 정보 조회
$query = "SELECT * FROM products WHERE product_code = ?";
$result = fetchOne($query, array($product_code));

if (!$result['success'] || !$result['data']) {
    header('Location: list.php?error=not_found');
    exit;
}

$product = $result['data'];

// 거래 통계 조회
$stats_query = "SELECT 
    COUNT(DISTINCT ti.transaction_id) as total_transactions,
    SUM(CASE WHEN t.transaction_type = 'IN' THEN ti.quantity ELSE 0 END) as total_in_qty,
    SUM(CASE WHEN t.transaction_type = 'OUT' THEN ti.quantity ELSE 0 END) as total_out_qty,
    SUM(CASE WHEN t.transaction_type = 'IN' THEN ti.amount ELSE 0 END) as total_in_amount,
    SUM(CASE WHEN t.transaction_type = 'OUT' THEN ti.amount ELSE 0 END) as total_out_amount
FROM transaction_items ti
INNER JOIN transactions t ON ti.transaction_id = t.transaction_id
WHERE ti.product_code = ?";

$stats_result = fetchOne($stats_query, array($product_code));
$stats = $stats_result['success'] ? $stats_result['data'] : array(
    'total_transactions' => 0,
    'total_in_qty' => 0,
    'total_out_qty' => 0,
    'total_in_amount' => 0,
    'total_out_amount' => 0
);

// 재고 상태 판단
$stock_status = '정상';
$stock_badge = 'badge-success';
if ($product['stock_quantity'] < 0) {
    $stock_status = '마이너스 재고';
    $stock_badge = 'badge-danger';
} elseif ($product['stock_quantity'] == 0) {
    $stock_status = '재고없음';
    $stock_badge = 'badge-warning';
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">📦 상품 상세보기</h2>
    <div style="display: flex; gap: 10px;">
        <a href="edit.php?code=<?php echo $product_code; ?>" class="btn btn-success">
            ✏️ 수정
        </a>
        <a href="delete.php?code=<?php echo $product_code; ?>" 
           class="btn btn-danger"
           onclick="return confirm('⚠️ 정말 삭제하시겠습니까?\n\n거래내역이 있는 경우 삭제할 수 없습니다.');">
            🗑️ 삭제
        </a>
        <a href="list.php" class="btn btn-outline">
            📋 목록
        </a>
    </div>
</div>

<!-- 거래 통계 -->
<?php if ($stats['total_transactions'] > 0): ?>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="stat-value"><?php echo formatNumber($stats['total_transactions']); ?>건</div>
        <div class="stat-label">총 거래건수</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
        <div class="stat-value"><?php echo formatNumber($stats['total_in_qty']); ?>개</div>
        <div class="stat-label">총 입고수량</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #007bff 0%, #00d4ff 100%);">
        <div class="stat-value"><?php echo formatNumber($stats['total_out_qty']); ?>개</div>
        <div class="stat-label">총 출고수량</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
        <div class="stat-value"><?php echo formatCurrency($stats['total_in_amount']); ?></div>
        <div class="stat-label">입고 총액</div>
    </div>
</div>
<?php endif; ?>

<!-- 상품 이미지 및 기본 정보 -->
<div class="section-box">
    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 30px;">
        <!-- 상품 이미지 -->
        <div>
            <?php if (!empty($product['image_url'])): ?>
                <img src="<?php echo escape($product['image_url']); ?>" 
                     alt="<?php echo escape($product['product_name']); ?>"
                     style="width: 100%; border-radius: 10px; border: 2px solid #e0e0e0;"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <?php endif; ?>
            <div style="width: 100%; height: 250px; background: #f0f0f0; border-radius: 10px; display: <?php echo empty($product['image_url']) ? 'flex' : 'none'; ?>; align-items: center; justify-content: center; font-size: 64px;">
                📦
            </div>
            
            <?php if (!empty($product['info_url'])): ?>
                <a href="<?php echo escape($product['info_url']); ?>" 
                   target="_blank" 
                   class="btn btn-outline btn-block" 
                   style="margin-top: 15px;">
                    🔗 상품 안내 페이지
                </a>
            <?php endif; ?>
        </div>
        
        <!-- 기본 정보 -->
        <div>
            <h3 style="margin-bottom: 20px; color: var(--primary-color); font-size: 18px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">
                📋 기본 정보
            </h3>
            
            <div class="info-grid">
                <div>
                    <div class="info-label">상품 코드</div>
                    <div class="info-value">
                        <strong style="color: var(--primary-color); font-size: 16px;">
                            <?php echo escape($product['product_code']); ?>
                        </strong>
                    </div>
                </div>
                
                <div>
                    <div class="info-label">상품명</div>
                    <div class="info-value">
                        <strong style="font-size: 18px;">
                            <?php echo displayValue($product['product_name']); ?>
                        </strong>
                    </div>
                </div>
                
                <div>
                    <div class="info-label">상품 규격</div>
                    <div class="info-value">
                        <?php echo displayValue($product['product_spec'], '미입력'); ?>
                    </div>
                </div>
                
                <div>
                    <div class="info-label">기준 단가</div>
                    <div class="info-value">
                        <strong style="font-size: 18px; color: #667eea;">
                            <?php echo formatCurrency($product['standard_price']); ?>
                        </strong>
                    </div>
                </div>
                
                <div>
                    <div class="info-label">현재 재고수량</div>
                    <div class="info-value">
                        <strong style="font-size: 20px; <?php echo $product['stock_quantity'] < 0 ? 'color: #ef4444;' : 'color: #28a745;'; ?>">
                            <?php echo formatNumber($product['stock_quantity']); ?>개
                        </strong>
                    </div>
                </div>
                
                <div>
                    <div class="info-label">재고 상태</div>
                    <div class="info-value">
                        <span class="badge <?php echo $stock_badge; ?>" style="font-size: 14px; padding: 6px 12px;">
                            <?php echo $stock_status; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($product['description'])): ?>
                <hr class="section-divider">
                <div>
                    <div class="info-label">상품 설명</div>
                    <div class="info-value" style="line-height: 1.6;">
                        <?php echo nl2br(escape($product['description'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 재고 및 가격 정보 -->
<div class="section-box">
    <h3 style="margin-bottom: 20px; color: var(--primary-color); font-size: 18px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">
        💰 재고 및 가격 정보
    </h3>
    
    <div class="info-grid">
        <div>
            <div class="info-label">현재 재고수량</div>
            <div class="info-value">
                <strong style="font-size: 24px; <?php echo $product['stock_quantity'] < 0 ? 'color: #ef4444;' : 'color: #28a745;'; ?>">
                    <?php echo formatNumber($product['stock_quantity']); ?>개
                </strong>
            </div>
        </div>
        
        <div>
            <div class="info-label">기준 단가</div>
            <div class="info-value">
                <strong style="font-size: 20px; color: #667eea;">
                    <?php echo formatCurrency($product['standard_price']); ?>
                </strong>
            </div>
        </div>
        
        <div>
            <div class="info-label">재고 평가금액</div>
            <div class="info-value">
                <strong style="font-size: 20px; color: #764ba2;">
                    <?php echo formatCurrency($product['stock_quantity'] * $product['standard_price']); ?>
                </strong>
            </div>
        </div>
        
        <div>
            <div class="info-label">재고 상태</div>
            <div class="info-value">
                <span class="badge <?php echo $stock_badge; ?>" style="font-size: 16px; padding: 8px 16px;">
                    <?php echo $stock_status; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- 추가 정보 -->
<div class="section-box">
    <h3 style="margin-bottom: 20px; color: var(--primary-color); font-size: 18px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">
        📝 추가 정보
    </h3>
    
    <?php if (!empty($product['notes'])): ?>
        <div style="margin-bottom: 20px;">
            <div class="info-label">비고</div>
            <div class="info-value" style="white-space: pre-wrap; line-height: 1.6;">
                <?php echo escape($product['notes']); ?>
            </div>
        </div>
        <hr class="section-divider">
    <?php endif; ?>
    
    <div class="info-grid">
        <div>
            <div class="info-label">이미지 URL</div>
            <div class="info-value">
                <?php if (!empty($product['image_url'])): ?>
                    <a href="<?php echo escape($product['image_url']); ?>" target="_blank" style="color: var(--primary-color); word-break: break-all;">
                        🖼️ <?php echo escape($product['image_url']); ?>
                    </a>
                <?php else: ?>
                    <span class="text-muted">미입력</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">안내 페이지 URL</div>
            <div class="info-value">
                <?php if (!empty($product['info_url'])): ?>
                    <a href="<?php echo escape($product['info_url']); ?>" target="_blank" style="color: var(--primary-color); word-break: break-all;">
                        🔗 <?php echo escape($product['info_url']); ?>
                    </a>
                <?php else: ?>
                    <span class="text-muted">미입력</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">등록일시</div>
            <div class="info-value">
                🕐 <?php echo formatDateTimeWithTime($product['created_at']); ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">수정일시</div>
            <div class="info-value">
                🕐 <?php echo formatDateTimeWithTime($product['updated_at']); ?>
            </div>
        </div>
    </div>
</div>

<!-- 액션 버튼 -->
<div class="action-buttons">
    <a href="edit.php?code=<?php echo $product_code; ?>" class="btn btn-success btn-lg">
        ✏️ 상품 수정
    </a>
    <a href="list.php" class="btn btn-outline btn-lg">
        📋 목록으로
    </a>
</div>

<?php
require_once '../includes/footer.php';
?>