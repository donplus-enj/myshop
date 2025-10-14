<?php
/**
 * MyShop - 상품 추가
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '상품 추가';
$error_message = '';
$form_data = array();

// 다음 상품 코드 자동 생성
$next_code_query = "SELECT RIGHT('0000' + CAST(ISNULL(MAX(CAST(product_code AS INT)), 0) + 1 AS VARCHAR), 4) AS next_code FROM products";
$result = fetchOne($next_code_query);
$auto_product_code = $result['success'] ? $result['data']['next_code'] : '0001';

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 폼 데이터 받기
    $form_data = array(
        'product_code' => trim($_POST['product_code'] ?? ''),
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
    if (empty($form_data['product_code'])) {
        $error_message = '상품 코드를 입력해주세요.';
    } elseif (!preg_match('/^[0-9]{4}$/', $form_data['product_code'])) {
        $error_message = '상품 코드는 4자리 숫자여야 합니다.';
    } elseif (empty($form_data['product_name'])) {
        $error_message = '상품명을 입력해주세요.';
    } elseif (!is_numeric($form_data['standard_price'])) {
        $error_message = '기준단가는 숫자여야 합니다.';
    } elseif (!is_numeric($form_data['stock_quantity'])) {
        $error_message = '초기 재고수량은 숫자여야 합니다.';
    } else {
        // 코드 중복 체크
        $check_query = "SELECT COUNT(*) as count FROM products WHERE product_code = ?";
        $check_result = fetchOne($check_query, array($form_data['product_code']));
        
        if ($check_result['success'] && $check_result['data']['count'] > 0) {
            $error_message = '이미 사용 중인 상품 코드입니다.';
        } else {
            // 상품 추가
            $insert_query = "INSERT INTO products (
                product_code, product_name, product_spec, description, image_url, info_url, notes,
                standard_price, stock_quantity
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = array(
                $form_data['product_code'],
                $form_data['product_name'],
                $form_data['product_spec'],
                $form_data['description'],
                $form_data['image_url'],
                $form_data['info_url'],
                $form_data['notes'],
                $form_data['standard_price'],
                $form_data['stock_quantity']
            );
            
            $result = executeNonQuery($insert_query, $params);
            
            if ($result['success']) {
                header('Location: list.php?success=added');
                exit;
            } else {
                $error_message = '상품 등록 중 오류가 발생했습니다.';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2 class="page-title">상품 추가</h2>
        <a href="list.php" class="btn btn-outline">목록으로</a>
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
                        <label for="product_code" class="required">상품 코드</label>
                        <input 
                            type="text" 
                            id="product_code" 
                            name="product_code" 
                            class="form-control"
                            maxlength="4"
                            pattern="[0-9]{4}"
                            value="<?php echo isset($form_data['product_code']) ? escape($form_data['product_code']) : $auto_product_code; ?>"
                            placeholder="4자리 숫자 (예: 0001)"
                            required
                        >
                        <small style="color: #666; font-size: 12px;">자동생성: <?php echo $auto_product_code; ?> (수정 가능)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_name" class="required">상품명</label>
                        <input 
                            type="text" 
                            id="product_name" 
                            name="product_name" 
                            class="form-control"
                            value="<?php echo $form_data['product_name'] ?? ''; ?>"
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
                            placeholder="예: 500ml, 1kg, 15인치"
                            value="<?php echo $form_data['product_spec'] ?? ''; ?>"
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
                            value="<?php echo $form_data['standard_price'] ?? '0'; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity">초기 재고수량</label>
                        <input 
                            type="number" 
                            id="stock_quantity" 
                            name="stock_quantity" 
                            class="form-control"
                            value="<?php echo $form_data['stock_quantity'] ?? '0'; ?>"
                        >
                        <small style="color: #666; font-size: 12px;">음수 입력 가능 (마이너스 재고 허용)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">상품설명</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-control"
                            rows="4"
                            placeholder="상품에 대한 상세 설명을 입력하세요"
                        ><?php echo $form_data['description'] ?? ''; ?></textarea>
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
                            placeholder="https://example.com/image.jpg"
                            value="<?php echo $form_data['image_url'] ?? ''; ?>"
                        >
                        <small style="color: #666; font-size: 12px;">외부 이미지 URL을 입력하세요</small>
                    </div>
                    
                    <!-- 이미지 미리보기 -->
                    <div id="image-preview" style="margin-bottom: 20px; display: none;">
                        <label>이미지 미리보기</label>
                        <div style="border: 2px dashed #e0e0e0; border-radius: 10px; padding: 20px; text-align: center;">
                            <img id="preview-img" src="" alt="미리보기" style="max-width: 100%; max-height: 200px; border-radius: 5px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="info_url">상품 안내 페이지 URL</label>
                        <input 
                            type="url" 
                            id="info_url" 
                            name="info_url" 
                            class="form-control"
                            placeholder="https://example.com/product-info"
                            value="<?php echo $form_data['info_url'] ?? ''; ?>"
                        >
                        <small style="color: #666; font-size: 12px;">제품 상세 페이지 링크</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">비고</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            class="form-control"
                            rows="6"
                            placeholder="추가 메모사항을 입력하세요"
                        ><?php echo $form_data['notes'] ?? ''; ?></textarea>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 2px solid #f0f0f0;">
                <button type="submit" class="btn btn-primary" style="min-width: 150px;">
                    ✅ 상품 등록
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

// 페이지 로드 시 기존 이미지 URL이 있으면 미리보기
window.addEventListener('load', function() {
    const imageUrl = document.getElementById('image_url').value.trim();
    if (imageUrl) {
        document.getElementById('image_url').dispatchEvent(new Event('input'));
    }
});
</script>

<?php
include '../includes/footer.php';
?>