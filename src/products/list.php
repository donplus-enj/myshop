<?php
/**
 * MyShop - 상품 목록
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '상품 관리';

// 검색 처리
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';

// 상품 목록 조회
$query = "SELECT 
            product_code,
            product_name,
            product_spec,
            standard_price,
            stock_quantity,
            image_url,
            created_at
          FROM products";

$params = array();

if (!empty($search_keyword)) {
    $query .= " WHERE product_name LIKE ? OR product_code LIKE ? OR product_spec LIKE ?";
    $search_param = '%' . $search_keyword . '%';
    $params = array($search_param, $search_param, $search_param);
}

$query .= " ORDER BY product_code DESC";

$result = fetchAll($query, $params);
$products = $result['success'] ? $result['data'] : array();

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">📦 상품 관리</h2>
    <a href="add.php" class="btn btn-primary">+ 상품 추가</a>
</div>

<?php if ($success_message == 'added'): ?>
    <div class="alert alert-success">
        ✅ 상품이 성공적으로 등록되었습니다.
    </div>
<?php elseif ($success_message == 'updated'): ?>
    <div class="alert alert-success">
        ✅ 상품 정보가 성공적으로 수정되었습니다.
    </div>
<?php elseif ($success_message == 'deleted'): ?>
    <div class="alert alert-success">
        ✅ 상품이 성공적으로 삭제되었습니다.
    </div>
<?php elseif ($error_message == 'has_transactions'): ?>
    <div class="alert alert-error">
        ❌ 거래내역이 있는 상품은 삭제할 수 없습니다.
    </div>
<?php elseif ($error_message == 'delete_failed'): ?>
    <div class="alert alert-error">
        ❌ 상품 삭제 중 오류가 발생했습니다.
    </div>
<?php endif; ?>

<div class="section-box">
    <!-- 검색 바 -->
    <form method="GET" action="" class="search-bar">
        <input 
            type="text" 
            name="search" 
            class="form-control" 
            placeholder="🔍 상품명, 상품코드, 규격으로 검색..."
            value="<?php echo escape($search_keyword); ?>"
        >
        <button type="submit" class="btn btn-primary">검색</button>
        <?php if (!empty($search_keyword)): ?>
            <a href="list.php" class="btn btn-outline">초기화</a>
        <?php endif; ?>
    </form>
    
    <!-- 상품 목록 -->
    <?php if (count($products) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">이미지</th>
                        <th>상품코드</th>
                        <th>상품명</th>
                        <th>규격</th>
                        <th class="text-right">기준단가</th>
                        <th class="text-right">재고수량</th>
                        <th class="text-center">재고상태</th>
                        <th>등록일</th>
                        <th class="text-center">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="text-center">
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?php echo escape($product['image_url']); ?>" 
                                         alt="<?php echo escape($product['product_name']); ?>"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #e0e0e0;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display: none; width: 50px; height: 50px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 20px;">📦</div>
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 20px;">📦</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo escape($product['product_code']); ?></strong></td>
                            <td class="text-left">
                                <strong><?php echo escape($product['product_name']); ?></strong>
                            </td>
                            <td><?php echo escape($product['product_spec'] ?? '-'); ?></td>
                            <td class="text-right"><?php echo formatCurrency($product['standard_price']); ?></td>
                            <td class="text-right">
                                <strong style="<?php echo $product['stock_quantity'] < 0 ? 'color: #ef4444;' : ''; ?>">
                                    <?php echo formatNumber($product['stock_quantity']); ?>
                                </strong>
                            </td>
                            <td class="text-center">
                                <?php if ($product['stock_quantity'] < 0): ?>
                                    <span class="badge badge-danger">마이너스</span>
                                <?php elseif ($product['stock_quantity'] == 0): ?>
                                    <span class="badge badge-warning">재고없음</span>
                                <?php else: ?>
                                    <span class="badge badge-success">정상</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDateTime($product['created_at']); ?></td>
                            <td class="table-actions">
                                <a href="view.php?code=<?php echo $product['product_code']; ?>" 
                                   class="btn btn-sm btn-info" 
                                   title="상세보기">
                                    📋
                                </a>
                                <a href="edit.php?code=<?php echo $product['product_code']; ?>" 
                                   class="btn btn-sm btn-success" 
                                   title="수정">
                                    ✏️
                                </a>
                                <a href="delete.php?code=<?php echo $product['product_code']; ?>" 
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
                총 <strong style="color: var(--primary-color);"><?php echo number_format(count($products)); ?>개</strong>의 상품
            </p>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="icon">📦</div>
            <p style="font-size: 16px; margin: 0;">
                <?php if (!empty($search_keyword)): ?>
                    '<strong><?php echo escape($search_keyword); ?></strong>' 검색 결과가 없습니다.
                <?php else: ?>
                    등록된 상품이 없습니다.<br>
                    <small>새 상품을 추가해주세요.</small>
                <?php endif; ?>
            </p>
            <?php if (empty($search_keyword)): ?>
                <a href="add.php" class="btn btn-primary" style="margin-top: 20px;">
                    + 첫 상품 추가하기
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
?>