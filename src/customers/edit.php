<?php
/**
 * MyShop - 거래처 수정
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '거래처 수정';
$error_message = '';
$customer_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($customer_code)) {
    header('Location: list.php');
    exit;
}

// 기존 거래처 정보 조회
$query = "SELECT * FROM customers WHERE customer_code = ?";
$result = fetchOne($query, array($customer_code));

if (!$result['success'] || !$result['data']) {
    header('Location: list.php');
    exit;
}

$customer = $result['data'];

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = array(
        'customer_name' => trim($_POST['customer_name'] ?? ''),
        'ceo_name' => trim($_POST['ceo_name'] ?? ''),
        'business_number' => trim($_POST['business_number'] ?? ''),
        'business_type' => trim($_POST['business_type'] ?? ''),
        'business_item' => trim($_POST['business_item'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'fax' => trim($_POST['fax'] ?? ''),
        'mobile' => trim($_POST['mobile'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'manager_name' => trim($_POST['manager_name'] ?? ''),
        'manager_contact' => trim($_POST['manager_contact'] ?? ''),
        'notes' => trim($_POST['notes'] ?? '')
    );
    
    // 유효성 검사
    if (empty($form_data['customer_name'])) {
        $error_message = '거래처명을 입력해주세요.';
    } else {
        // 거래처 수정
        $update_query = "UPDATE customers SET 
            customer_name = ?, ceo_name = ?, business_number = ?, business_type = ?, business_item = ?,
            address = ?, phone = ?, fax = ?, mobile = ?, email = ?, 
            manager_name = ?, manager_contact = ?, notes = ?
        WHERE customer_code = ?";
        
        $params = array(
            $form_data['customer_name'],
            $form_data['ceo_name'],
            $form_data['business_number'],
            $form_data['business_type'],
            $form_data['business_item'],
            $form_data['address'],
            $form_data['phone'],
            $form_data['fax'],
            $form_data['mobile'],
            $form_data['email'],
            $form_data['manager_name'],
            $form_data['manager_contact'],
            $form_data['notes'],
            $customer_code
        );
        
        $result = executeNonQuery($update_query, $params);
        
        if ($result['success']) {
            header('Location: list.php?success=updated');
            exit;
        } else {
            $error_message = '거래처 수정 중 오류가 발생했습니다.';
        }
    }
    
    // 오류 발생 시 입력값 유지
    $customer = array_merge($customer, $form_data);
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2 class="page-title">거래처 수정</h2>
        <div>
            <a href="view.php?code=<?php echo $customer_code; ?>" class="btn btn-outline">상세보기</a>
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
                        <label>거래처 코드</label>
                        <input 
                            type="text" 
                            class="form-control"
                            value="<?php echo escape($customer_code); ?>"
                            disabled
                        >
                        <small style="color: #666; font-size: 12px;">거래처 코드는 수정할 수 없습니다</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_name" class="required">거래처명 (상호)</label>
                        <input 
                            type="text" 
                            id="customer_name" 
                            name="customer_name" 
                            class="form-control"
                            value="<?php echo escape($customer['customer_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="ceo_name">대표자명</label>
                        <input 
                            type="text" 
                            id="ceo_name" 
                            name="ceo_name" 
                            class="form-control"
                            value="<?php echo escape($customer['ceo_name']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="business_number">사업자등록번호</label>
                        <input 
                            type="text" 
                            id="business_number" 
                            name="business_number" 
                            class="form-control"
                            maxlength="12"
                            value="<?php echo escape($customer['business_number']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="business_type">업태</label>
                        <input 
                            type="text" 
                            id="business_type" 
                            name="business_type" 
                            class="form-control"
                            value="<?php echo escape($customer['business_type']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="business_item">종목</label>
                        <input 
                            type="text" 
                            id="business_item" 
                            name="business_item" 
                            class="form-control"
                            value="<?php echo escape($customer['business_item']); ?>"
                        >
                    </div>
                </div>
                
                <!-- 우측 컬럼 -->
                <div>
                    <h3 style="margin-bottom: 20px; color: #667eea;">연락처 정보</h3>
                    
                    <div class="form-group">
                        <label for="phone">전화번호</label>
                        <input 
                            type="text" 
                            id="phone" 
                            name="phone" 
                            class="form-control"
                            value="<?php echo escape($customer['phone']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="fax">팩스번호</label>
                        <input 
                            type="text" 
                            id="fax" 
                            name="fax" 
                            class="form-control"
                            value="<?php echo escape($customer['fax']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile">이동전화번호</label>
                        <input 
                            type="text" 
                            id="mobile" 
                            name="mobile" 
                            class="form-control"
                            value="<?php echo escape($customer['mobile']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email">이메일</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control"
                            value="<?php echo escape($customer['email']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="manager_name">담당자명</label>
                        <input 
                            type="text" 
                            id="manager_name" 
                            name="manager_name" 
                            class="form-control"
                            value="<?php echo escape($customer['manager_name']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="manager_contact">담당자 연락처</label>
                        <input 
                            type="text" 
                            id="manager_contact" 
                            name="manager_contact" 
                            class="form-control"
                            value="<?php echo escape($customer['manager_contact']); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="address">사업장 주소</label>
                        <textarea 
                            id="address" 
                            name="address" 
                            class="form-control"
                            rows="3"
                        ><?php echo escape($customer['address']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">비고</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            class="form-control"
                            rows="4"
                        ><?php echo escape($customer['notes']); ?></textarea>
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

<?php
include '../includes/footer.php';
?>