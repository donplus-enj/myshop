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
    c.customer_code, c.customer_name, c.ceo_name, c.phone, c.mobile, c.address,
    u.user_name, u.user_code
FROM transactions t
INNER JOIN customers c ON t.customer_code = c.customer_code
INNER JOIN users u ON t.user_code = u.user_code
WHERE t.transaction_id = ?";

$header_result = fetchOne($header_query, array($transaction_id));

if (!$header_result['success'] || !$header_result['data']) {
    header('Location: history.php?error=not_found');
    exit;
}

$transaction = $header_result['data'];

// 거래 상세 정보 조회
$items_query = "SELECT 
    ti.*,
    p.product_name, p.product_spec, p.stock_quantity
FROM transaction_items ti
INNER JOIN products p ON ti.product_code = p.product_code
WHERE ti.transaction_id = ?
ORDER BY ti.item_id";

$items_result = fetchAll($items_query, array($transaction_id));
$items = $items_result['success'] ? $items_result['data'] : array();

// 거래 유형 정보
$transaction_types = array(
    'IN' => array('name' => '입고', 'badge' => 'badge-success', 'icon' => '📥'),
    'OUT' => array('name' => '출고', 'badge' => 'badge-primary', 'icon' => '📤'),
    'IN_RETURN' => array('name' => '입고반품', 'badge' => 'badge-warning', 'icon' => '↩️'),
    'OUT_RETURN' => array('name' => '출고반품', 'badge' => 'badge-warning', 'icon' => '↪️'),
    'RECEIVE' => array('name' => '수금', 'badge' => 'badge-info', 'icon' => '💰'),
    'PAYMENT' => array('name' => '지급', 'badge' => 'badge-danger', 'icon' => '💸')
);

$type_info = $transaction_types[$transaction['transaction_type']];

include '../includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">
        <?php echo $type_info['icon']; ?> 거래 상세보기
    </h2>
    <div style="display: flex; gap: 10px;">
        <button onclick="window.print()" class="btn btn-success">
            🖨️ 인쇄
        </button>
        <a href="history.php" class="btn btn-outline">
            📋 목록
        </a>
    </div>
</div>

<!-- 거래 정보 카드 -->
<div class="section-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f0;">
        <div>
            <h3 style="margin: 0; color: var(--primary-color);">
                거래번호: #<?php echo $transaction_id; ?>
            </h3>
            <p style="margin: 5px 0 0 0; color: #666;">
                <?php echo formatDateTimeWithTime($transaction['created_at']); ?> 등록
            </p>
        </div>
        <div>
            <span class="badge <?php echo $type_info['badge']; ?>" style="font-size: 18px; padding: 10px 20px;">
                <?php echo $type_info['icon']; ?> <?php echo $type_info['name']; ?>
            </span>
        </div>
    </div>
    
    <div class="info-grid">
        <div>
            <div class="info-label">거래일자</div>
            <div class="info-value">
                <strong style="font-size: 16px;">
                    📅 <?php echo formatDateKorean($transaction['transaction_date']); ?>
                </strong>
            </div>
        </div>
        
        <div>
            <div class="info-label">거래 유형</div>
            <div class="info-value">
                <span class="badge <?php echo $type_info['badge']; ?>" style="font-size: 14px; padding: 6px 12px;">
                    <?php echo $type_info['icon']; ?> <?php echo $type_info['name']; ?>
                </span>
            </div>
        </div>
        
        <div>
            <div class="info-label">거래처 코드</div>
            <div class="info-value">
                <a href="../customers/view.php?code=<?php echo $transaction['customer_code']; ?>" 
                   style="color: var(--primary-color); font-weight: 600;">
                    <?php echo escape($transaction['customer_code']); ?>
                </a>
            </div>
        </div>
        
        <div>
            <div class="info-label">거래처명</div>
            <div class="info-value">
                <strong style="font-size: 16px;">
                    🏢 <?php echo escape($transaction['customer_name']); ?>
                </strong>
            </div>
        </div>
        
        <div>
            <div class="info-label">대표자</div>
            <div class="info-value">
                <?php echo displayValue($transaction['ceo_name'], '미입력'); ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">연락처</div>
            <div class="info-value">
                <?php if (!empty($transaction['phone'])): ?>
                    <a href="tel:<?php echo escape($transaction['phone']); ?>" style="color: var(--primary-color);">
                        📞 <?php echo escape($transaction['phone']); ?>
                    </a>
                <?php elseif (!empty($transaction['mobile'])): ?>
                    <a href="tel:<?php echo escape($transaction['mobile']); ?>" style="color: var(--primary-color);">
                        📱 <?php echo escape($transaction['mobile']); ?>
                    </a>
                <?php else: ?>
                    <span class="text-muted">미입력</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">입력자</div>
            <div class="info-value">
                👤 <?php echo escape($transaction['user_name']); ?> 
                (<?php echo escape($transaction['user_code']); ?>)
            </div>
        </div>
        
        <div>
            <div class="info-label">등록일시</div>
            <div class="info-value">
                🕐 <?php echo formatDateTimeWithTime($transaction['created_at']); ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($transaction['notes'])): ?>
        <hr class="section-divider">
        <div>
            <div class="info-label">비고</div>
            <div class="info-value" style="white-space: pre-wrap; line-height: 1.6;">
                <?php echo escape($transaction['notes']); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 상품 목록 -->
<div class="section-box">
    <h3 style="margin-bottom: 20px; color: var(--primary-color); font-size: 18px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">
        📦 상품 목록
    </h3>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">번호</th>
                    <th style="width: 100px;">상품코드</th>
                    <th>상품명</th>
                    <th style="width: 150px;">규격</th>
                    <th style="width: 100px;" class="text-right">수량</th>
                    <th style="width: 120px;" class="text-right">단가</th>
                    <th style="width: 150px;" class="text-right">금액</th>
                    <th style="width: 100px;" class="text-center">현재재고</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $row_num = 1;
                foreach ($items as $item): 
                ?>
                    <tr>
                        <td class="text-center"><?php echo $row_num++; ?></td>
                        <td>
                            <a href="../products/view.php?code=<?php echo $item['product_code']; ?>" 
                               style="color: var(--primary-color); font-weight: 600;">
                                <?php echo escape($item['product_code']); ?>
                            </a>
                        </td>
                        <td>
                            <strong><?php echo escape($item['product_name']); ?></strong>
                        </td>
                        <td><?php echo displayValue($item['product_spec'], '-'); ?></td>
                        <td class="text-right">
                            <strong><?php echo formatNumber($item['quantity']); ?></strong>
                        </td>
                        <td class="text-right">
                            <?php echo formatCurrency($item['unit_price']); ?>
                        </td>
                        <td class="text-right">
                            <strong style="font-size: 15px;">
                                <?php echo formatCurrency($item['amount']); ?>
                            </strong>
                        </td>
                        <td class="text-center">
                            <span class="badge <?php echo $item['stock_quantity'] < 0 ? 'badge-danger' : 'badge-success'; ?>">
                                <?php echo formatNumber($item['stock_quantity']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f8f9fa; font-weight: bold; font-size: 16px;">
                    <td colspan="6" class="text-right">합계</td>
                    <td class="text-right" style="color: var(--primary-color);">
                        <?php echo formatCurrency($transaction['total_amount']); ?>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div style="margin-top: 20px; text-align: center; color: #666;">
        총 <strong style="color: var(--primary-color);"><?php echo count($items); ?>개</strong> 상품
    </div>
</div>

<!-- 액션 버튼 -->
<div class="action-buttons no-print">
    <button onclick="window.print()" class="btn btn-success btn-lg">
        🖨️ 인쇄
    </button>
    <a href="history.php" class="btn btn-outline btn-lg">
        📋 목록으로
    </a>
</div>

<style>
@media print {
    .header, .no-print, .page-header button, .page-header a {
        display: none !important;
    }
    
    .page-header h2 {
        margin-bottom: 20px;
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
    }
    
    .section-box {
        box-shadow: none;
        border: 1px solid #ddd;
        page-break-inside: avoid;
    }
    
    body {
        background: white;
    }
    
    .badge {
        border: 1px solid #333;
    }
}
</style>

<?php
include '../includes/footer.php';
?>