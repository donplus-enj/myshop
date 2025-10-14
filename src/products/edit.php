<?php
/**
 * MyShop - 상품 수정
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '상품 수정';
$error_message = '';
$product_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($product_code)) {
    header('Location: list.php');
    exit;
}

// 기존 상품 정보 조회
$query = "SELECT * FROM products WHERE product_code = ?";
$result = fetchOne($query, array($product_code));

if (!$result['success'] || !$result['data']) {
    header('Location: list.php');
    exit;
}

$product = $result['data'];

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = array(
        'product_name' => trim($_POST['product_name'] ?? ''),
        'product_spec' => trim($_POST['product_spec'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'image_url' => trim($_POST['image_url'] ?? ''),
        'info_url' => trim($_POST['info_url'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
        'standard_price' => trim($_POST['standard_price'] ?? '0'),
        'stock_quantity' => trim($_POST['stock_quantity'] ?? '0')
    );
    
    // 유효성 검사
    if (empty($form_data['product_name'])) {
        $error_message = '상품명을 입력해주세요.';
    } elseif (!is_numeric($form_data['standard_price'])) {
        $error_message = '기준단가는 숫자여야 합니다.';
    } elseif (!is_numeric($form_data['stock_quantity'])) {
        $error_message = '재고수량은 숫자여야 합니다.';
    } else {
        // 상품 수정
        $update_query = "UPDATE products SET 
            product_name = ?,
            product_spec = ?,
            description = ?,
            image_url = ?,
            info_url = ?,
            notes = ?,
            standard_price = ?,
            stock_quantity = ?
        WHERE product_code = ?";
        
        $params = array(
            $form_data['product_name'],
            $form_data['product_spec'],
            $form_data['description'],
            $form_data['image_url'],
            $form_data['info_url'],
            $form_data['notes'],
            $form_data['standard_price'],
            $form_data['stock_quantity'],
            $product_code
        );
        
        $result = executeNonQuery($update_query, $params);
        
        if ($result['success']) {
            header('Location: list.php?success=updated');
            exit;
        } else {
            $error_message = '상품 수정 중 오류가 발생했습니다.';
        }
    }
    
    // 오류 발생 시 입력값 유지
    $product = array_merge($product, $form_data);
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2 class="page-title">상품 수정</h2>
        <div>
            <a href="view.php?code=<?php echo $product_code; ?>" class="btn btn-outline">상세보기</a>
            <a href="list.php" class="btn btn-outline">목록으로</a>
        </div>
    </div>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <?php echo escape($error_message); ?>
        </div>
    <?php endif; ?>
    
    <div class="section-box">
        <form method="POST" action="">
            <div class="form-grid">
                <!-- 좌측 컬럼 -->
                <div>
                    <h3 style="margin-bottom: 20px; color: #667eea;">기본 정보</h3>
                    
                    <div class="form-group">
                        <label>상품 코드</label>
                        <input 
                            type="text" 
                            class="form-control"
                            value="<?php echo escape($product_code); ?>"
                            disabled
                        >
                        <small style="color: #666; font-size: 12px;">상품 코드는 수정할 수 없습니다</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_name" class="required">상품명</label>
                        <input 
                            type="text" 
                            id="product_name" 
                            name="product_name" 
                            class="form-control"
                            value="<?php echo escape($product['product_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="product_spec">상품규격</label>
                        <input 
                            type="text" 
                            id="product_spec" 
                            name="product_spec" 
                            class="form-control"
                            value="<?php echo escape($product['product_spec']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="standard_price">기준 단가 (원)</label>
                        <input 
                            type="number" 
                            id="standard_price" 
                            name="standard_price" 
                            class="form-control"
                            min="0"
                            step="1"
                            value="<?php echo escape($product['standard_price']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity">재고수량</label>
                        <input 
                            type="number" 
                            id="stock_quantity" 
                            name="stock_quantity" 
                            class="form-control"
                            value="<?php echo escape($product['stock_quantity']); ?>"
                        >
                        <small style="color: #666; font-size: 12px;">
                            현재 재고: <strong><?php echo number_format($product['stock_quantity']); ?></strong>
                            <?php if ($product['stock_quantity'] < 0): ?>
                                <span style="color: #ef4444;">(마이너스 재고)</span>
                            <?php endif; ?>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">상품설명</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-control"
                            rows="4"
                        ><?php echo escape($product['description']); ?></textarea>
                    </div>
                </div>
                
                <!-- 우측 컬럼 -->
                <div>
                    <h3 style="margin-bottom: 20px; color: #667eea;">추가 정보</h3>
                    
                    <div class="form-group">
                        <label for="image_url">상품 이미지 URL</label>
                        <input 
                            type="url" 
                            id="image_url" 
                            name="image_url" 
                            class="form-control"
                            value="<?php echo escape($product['image_url']); ?>"
                        >
                    </div>
                    
                    <!-- 이미지 미리보기 -->
                    <div id="image-preview" style="margin-bottom: 20px; <?php echo empty($product['image_url']) ? 'display: none;' : ''; ?>">
                        <label>이미지 미리보기</label>
                        <div style="border: 2px dashed #e0e0e0; border-radius: 10px; padding: 20px; text-align: center;">
                            <img id="preview-img" 
                                 src="<?php echo escape($product['image_url']); ?>" 
                                 alt="미리보기" 
                                 style="max-width: 100%; max-height: 200px; border-radius: 5px;"
                                 onerror="document.getElementById('image-preview').style.display='none';">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="info_url">상품 안내 페이지 URL</label>
                        <input 
                            type="url" 
                            id="info_url" 
                            name="info_url" 
                            class="form-control"
                            value="<?php echo escape($product['info_url']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">비고</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            class="form-control"
                            rows="6"
                        ><?php echo escape($product['notes']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 2px solid #f0f0f0;">
                <button type="submit" class="btn btn-primary" style="min-width: 150px;">
                    ✅ 수정 완료
                </button>
                <a href="list.php" class="btn btn-outline" style="min-width: 150px; margin-left: 10px;">
                    ❌ 취소
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// 이미지 URL 입력 시 미리보기
document.getElementById('image_url').addEventListener('input', function() {
    const imageUrl = this.value.trim();
    const preview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (imageUrl) {
        previewImg.src = imageUrl;
        previewImg.onerror = function() {
            preview.style.display = 'none';
        };
        previewImg.onload = function() {
            preview.style.display = 'block';
        };
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php
include '../includes/footer.php';
?>