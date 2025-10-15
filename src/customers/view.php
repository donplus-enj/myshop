<?php
/**
 * MyShop - 거래처 상세보기
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '거래처 상세보기';
$customer_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($customer_code)) {
    header('Location: list.php');
    exit;
}

// 거래처 정보 조회
$query = "SELECT * FROM customers WHERE customer_code = ?";
$result = fetchOne($query, array($customer_code));

if (!$result['success'] || !$result['data']) {
    header('Location: list.php?error=not_found');
    exit;
}

$customer = $result['data'];

// 거래 통계 조회
$stats_query = "SELECT 
    COUNT(*) as total_transactions,
    SUM(CASE WHEN transaction_type = 'IN' THEN total_amount ELSE 0 END) as total_in,
    SUM(CASE WHEN transaction_type = 'OUT' THEN total_amount ELSE 0 END) as total_out,
    SUM(CASE WHEN transaction_type = 'RECEIVE' THEN total_amount ELSE 0 END) as total_receive,
    SUM(CASE WHEN transaction_type = 'PAYMENT' THEN total_amount ELSE 0 END) as total_payment
FROM transactions 
WHERE customer_code = ?";

$stats_result = fetchOne($stats_query, array($customer_code));
$stats = $stats_result['success'] ? $stats_result['data'] : array(
    'total_transactions' => 0,
    'total_in' => 0,
    'total_out' => 0,
    'total_receive' => 0,
    'total_payment' => 0
);

/**
 * DateTime 포맷 함수
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '-';
    
    if ($datetime instanceof DateTime) {
        return $datetime->format('Y-m-d H:i');
    }
    
    if (is_string($datetime)) {
        $timestamp = strtotime($datetime);
        if ($timestamp !== false) {
            return date('Y-m-d H:i', $timestamp);
        }
    }
    
    return '-';
}

/**
 * 값이 비어있으면 대체 텍스트 반환
 */
function displayValue($value, $default = '-') {
    return !empty($value) ? escape($value) : '<span class="text-muted">' . $default . '</span>';
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">📋 거래처 상세보기</h2>
    <div style="display: flex; gap: 10px;">
        <a href="edit.php?code=<?php echo $customer_code; ?>" class="btn btn-success">
            ✏️ 수정
        </a>
        <a href="delete.php?code=<?php echo $customer_code; ?>" 
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
        <div class="stat-value"><?php echo number_format($stats['total_transactions']); ?>건</div>
        <div class="stat-label">총 거래건수</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
        <div class="stat-value"><?php echo number_format($stats['total_in']); ?>원</div>
        <div class="stat-label">입고 총액</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #007bff 0%, #00d4ff 100%);">
        <div class="stat-value"><?php echo number_format($stats['total_out']); ?>원</div>
        <div class="stat-label">출고 총액</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8 0%, #20c9e3 100%);">
        <div class="stat-value"><?php echo number_format($stats['total_receive']); ?>원</div>
        <div class="stat-label">수금 총액</div>
    </div>
</div>
<?php endif; ?>

<!-- 기본 정보 -->
<div class="section-box">
    <h3 style="margin-bottom: 20px; color: var(--primary-color); font-size: 18px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">
        🏢 기본 정보
    </h3>
    
    <div class="info-grid">
        <div>
            <div class="info-label">거래처 코드</div>
            <div class="info-value">
                <strong style="color: var(--primary-color); font-size: 16px;">
                    <?php echo escape($customer['customer_code']); ?>
                </strong>
            </div>
        </div>
        
        <div>
            <div class="info-label">거래처명 (상호)</div>
            <div class="info-value">
                <strong style="font-size: 16px;">
                    <?php echo displayValue($customer['customer_name']); ?>
                </strong>
            </div>
        </div>
        
        <div>
            <div class="info-label">대표자명</div>
            <div class="info-value">
                <?php echo displayValue($customer['ceo_name'], '미입력'); ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">사업자등록번호</div>
            <div class="info-value">
                <?php echo displayValue($customer['business_number'], '미입력'); ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">업태</div>
            <div class="info-value">
                <?php echo displayValue($customer['business_type'], '미입력'); ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">종목</div>
            <div class="info-value">
                <?php echo displayValue($customer['business_item'], '미입력'); ?>
            </div>
        </div>
    </div>
</div>

<!-- 연락처 정보 -->
<div class="section-box">
    <h3 style="margin-bottom: 20px; color: var(--primary-color); font-size: 18px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">
        📞 연락처 정보
    </h3>
    
    <div class="info-grid">
        <div>
            <div class="info-label">전화번호</div>
            <div class="info-value">
                <?php if (!empty($customer['phone'])): ?>
                    <a href="tel:<?php echo escape($customer['phone']); ?>" style="color: var(--primary-color);">
                        📞 <?php echo escape($customer['phone']); ?>
                    </a>
                <?php else: ?>
                    <span class="text-muted">미입력</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">팩스번호</div>
            <div class="info-value">
                <?php echo displayValue($customer['fax'], '미입력'); ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">이동전화번호</div>
            <div class="info-value">
                <?php if (!empty($customer['mobile'])): ?>
                    <a href="tel:<?php echo escape($customer['mobile']); ?>" style="color: var(--primary-color);">
                        📱 <?php echo escape($customer['mobile']); ?>
                    </a>
                <?php else: ?>
                    <span class="text-muted">미입력</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">이메일</div>
            <div class="info-value">
                <?php if (!empty($customer['email'])): ?>
                    <a href="mailto:<?php echo escape($customer['email']); ?>" style="color: var(--primary-color);">
                        📧 <?php echo escape($customer['email']); ?>
                    </a>
                <?php else: ?>
                    <span class="text-muted">미입력</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">담당자명</div>
            <div class="info-value">
                <?php echo displayValue($customer['manager_name'], '미입력'); ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">담당자 연락처</div>
            <div class="info-value">
                <?php if (!empty($customer['manager_contact'])): ?>
                    <a href="tel:<?php echo escape($customer['manager_contact']); ?>" style="color: var(--primary-color);">
                        📱 <?php echo escape($customer['manager_contact']); ?>
                    </a>
                <?php else: ?>
                    <span class="text-muted">미입력</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <hr class="section-divider">
    
    <div>
        <div class="info-label">사업장 주소</div>
        <div class="info-value" style="min-height: 60px;">
            <?php if (!empty($customer['address'])): ?>
                📍 <?php echo nl2br(escape($customer['address'])); ?>
            <?php else: ?>
                <span class="text-muted">미입력</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 추가 정보 -->
<div class="section-box">
    <h3 style="margin-bottom: 20px; color: var(--primary-color); font-size: 18px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">
        📝 추가 정보
    </h3>
    
    <div>
        <div class="info-label">비고</div>
        <div class="info-value" style="min-height: 80px; white-space: pre-wrap;">
            <?php echo displayValue($customer['notes'], '메모 없음'); ?>
        </div>
    </div>
    
    <hr class="section-divider">
    
    <div class="info-grid">
        <div>
            <div class="info-label">등록일시</div>
            <div class="info-value">
                🕐 <?php echo formatDateTime($customer['created_at']); ?>
            </div>
        </div>
        
        <div>
            <div class="info-label">수정일시</div>
            <div class="info-value">
                🕐 <?php echo formatDateTime($customer['updated_at']); ?>
            </div>
        </div>
    </div>
</div>

<!-- 액션 버튼 -->
<div class="action-buttons">
    <a href="edit.php?code=<?php echo $customer_code; ?>" class="btn btn-success btn-lg">
        ✏️ 거래처 수정
    </a>
    <a href="list.php" class="btn btn-outline btn-lg">
        📋 목록으로
    </a>
</div>

<?php
require_once '../includes/footer.php';
?>