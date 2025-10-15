<?php
/**
 * MyShop - 거래처 목록
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '거래처 관리';

// 검색 처리
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';

// 거래처 목록 조회
$query = "SELECT 
            customer_code,
            customer_name,
            ceo_name,
            business_number,
            phone,
            mobile,
            email,
            address,
            created_at
          FROM customers";

$params = array();

if (!empty($search_keyword)) {
    $query .= " WHERE customer_name LIKE ? OR customer_code LIKE ? OR business_number LIKE ?";
    $search_param = '%' . $search_keyword . '%';
    $params = array($search_param, $search_param, $search_param);
}

$query .= " ORDER BY customer_code DESC";

$result = fetchAll($query, $params);
$customers = $result['success'] ? $result['data'] : array();

/**
 * DateTime 안전 포맷 함수
 * MSSQL datetime을 문자열로 변환
 */
function formatDateTime($datetime) {
    if (empty($datetime)) {
        return '';
    }
    
    // DateTime 객체인 경우
    if ($datetime instanceof DateTime) {
        return $datetime->format('Y-m-d');
    }
    
    // 문자열인 경우
    if (is_string($datetime)) {
        $timestamp = strtotime($datetime);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
    }
    
    return '';
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">🏢 거래처 관리</h2>
    <a href="add.php" class="btn btn-primary">+ 거래처 추가</a>
</div>

<?php if ($success_message == 'added'): ?>
    <div class="alert alert-success">
        ✅ 거래처가 성공적으로 등록되었습니다.
    </div>
<?php elseif ($success_message == 'updated'): ?>
    <div class="alert alert-success">
        ✅ 거래처 정보가 성공적으로 수정되었습니다.
    </div>
<?php elseif ($success_message == 'deleted'): ?>
    <div class="alert alert-success">
        ✅ 거래처가 성공적으로 삭제되었습니다.
    </div>
<?php elseif ($error_message == 'has_transactions'): ?>
    <div class="alert alert-error">
        ❌ 거래내역이 있는 거래처는 삭제할 수 없습니다.
    </div>
<?php elseif ($error_message == 'delete_failed'): ?>
    <div class="alert alert-error">
        ❌ 거래처 삭제 중 오류가 발생했습니다.
    </div>
<?php endif; ?>

<div class="section-box">
    <!-- 검색 바 -->
    <form method="GET" action="" class="search-bar">
        <input 
            type="text" 
            name="search" 
            class="form-control" 
            placeholder="🔍 거래처명, 거래처코드, 사업자번호로 검색..."
            value="<?php echo escape($search_keyword); ?>"
        >
        <button type="submit" class="btn btn-primary">검색</button>
        <?php if (!empty($search_keyword)): ?>
            <a href="list.php" class="btn btn-outline">초기화</a>
        <?php endif; ?>
    </form>
    
    <!-- 거래처 목록 -->
    <?php if (count($customers) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>거래처코드</th>
                        <th>거래처명</th>
                        <th>대표자</th>
                        <th>사업자번호</th>
                        <th>전화번호</th>
                        <th>이동전화</th>
                        <th>등록일</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><strong><?php echo escape($customer['customer_code']); ?></strong></td>
                            <td class="text-left">
                                <strong><?php echo escape($customer['customer_name']); ?></strong>
                            </td>
                            <td><?php echo escape($customer['ceo_name'] ?? '-'); ?></td>
                            <td><?php echo escape($customer['business_number'] ?? '-'); ?></td>
                            <td><?php echo escape($customer['phone'] ?? '-'); ?></td>
                            <td><?php echo escape($customer['mobile'] ?? '-'); ?></td>
                            <td><?php echo formatDateTime($customer['created_at']); ?></td>
                            <td class="table-actions">
                                <a href="view.php?code=<?php echo $customer['customer_code']; ?>" 
                                   class="btn btn-sm btn-info" 
                                   title="상세보기">
                                    📋
                                </a>
                                <a href="edit.php?code=<?php echo $customer['customer_code']; ?>" 
                                   class="btn btn-sm btn-success" 
                                   title="수정">
                                    ✏️
                                </a>
                                <a href="delete.php?code=<?php echo $customer['customer_code']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="삭제"
                                   onclick="return confirm('⚠️ 정말 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.');">
                                    🗑️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center" style="margin-top: 20px;">
            <p class="text-muted">
                총 <strong style="color: var(--primary-color);"><?php echo number_format(count($customers)); ?>개</strong>의 거래처
            </p>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="icon">📦</div>
            <p style="font-size: 16px; margin: 0;">
                <?php if (!empty($search_keyword)): ?>
                    '<strong><?php echo escape($search_keyword); ?></strong>' 검색 결과가 없습니다.
                <?php else: ?>
                    등록된 거래처가 없습니다.<br>
                    <small>새 거래처를 추가해주세요.</small>
                <?php endif; ?>
            </p>
            <?php if (empty($search_keyword)): ?>
                <a href="add.php" class="btn btn-primary" style="margin-top: 20px;">
                    + 첫 거래처 추가하기
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
?>