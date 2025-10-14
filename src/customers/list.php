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

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2 class="page-title">거래처 관리</h2>
        <a href="add.php" class="btn btn-primary">+ 거래처 추가</a>
    </div>
    
    <?php if ($success_message == 'added'): ?>
        <div class="alert alert-success">
            거래처가 성공적으로 등록되었습니다.
        </div>
    <?php elseif ($success_message == 'updated'): ?>
        <div class="alert alert-success">
            거래처 정보가 성공적으로 수정되었습니다.
        </div>
    <?php elseif ($success_message == 'deleted'): ?>
        <div class="alert alert-success">
            거래처가 성공적으로 삭제되었습니다.
        </div>
    <?php endif; ?>
    
    <div class="section-box">
        <!-- 검색 바 -->
        <form method="GET" action="" class="search-bar">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="거래처명, 거래처코드, 사업자번호로 검색..."
                value="<?php echo escape($search_keyword); ?>"
            >
            <button type="submit" class="btn btn-primary">검색</button>
            <?php if (!empty($search_keyword)): ?>
                <a href="list.php" class="btn btn-outline">초기화</a>
            <?php endif; ?>
        </form>
        
        <!-- 거래처 목록 -->
        <?php if (count($customers) > 0): ?>
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
                            <td><?php echo escape($customer['customer_name']); ?></td>
                            <td><?php echo escape($customer['ceo_name']); ?></td>
                            <td><?php echo escape($customer['business_number']); ?></td>
                            <td><?php echo escape($customer['phone']); ?></td>
                            <td><?php echo escape($customer['mobile']); ?></td>
                            <td><?php echo $customer['created_at'] ? date('Y-m-d', strtotime($customer['created_at']->format('Y-m-d'))) : ''; ?></td>
                            <td class="table-actions">
                                <a href="view.php?code=<?php echo $customer['customer_code']; ?>" 
                                   class="btn btn-sm btn-primary" 
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
                                   onclick="return confirm('정말 삭제하시겠습니까?');">
                                    🗑️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p class="text-muted text-center" style="margin-top: 20px;">
                총 <?php echo number_format(count($customers)); ?>개의 거래처
            </p>
        <?php else: ?>
            <p class="text-center text-muted" style="padding: 40px;">
                <?php if (!empty($search_keyword)): ?>
                    검색 결과가 없습니다.
                <?php else: ?>
                    등록된 거래처가 없습니다. 새 거래처를 추가해주세요.
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php
include '../includes/footer.php';
?>