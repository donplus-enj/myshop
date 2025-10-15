<?php
/**
 * MyShop - 거래처 추가
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '거래처 추가';
$error_message = '';
$form_data = array();

// 다음 거래처 코드 자동 생성
$next_code_query = "SELECT RIGHT('0000' + CAST(ISNULL(MAX(CAST(customer_code AS INT)), 0) + 1 AS VARCHAR), 4) AS next_code FROM customers";
$result = fetchOne($next_code_query);
$auto_customer_code = $result['success'] ? $result['data']['next_code'] : '0001';

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 폼 데이터 받기
    $form_data = array(
        'customer_code' => trim($_POST['customer_code'] ?? ''),
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
    if (empty($form_data['customer_code'])) {
        $error_message = '거래처 코드를 입력해주세요.';
    } elseif (!preg_match('/^[0-9]{4}$/', $form_data['customer_code'])) {
        $error_message = '거래처 코드는 4자리 숫자여야 합니다.';
    } elseif (empty($form_data['customer_name'])) {
        $error_message = '거래처명을 입력해주세요.';
    } else {
        // 코드 중복 체크
        $check_query = "SELECT COUNT(*) as count FROM customers WHERE customer_code = ?";
        $check_result = fetchOne($check_query, array($form_data['customer_code']));
        
        if ($check_result['success'] && $check_result['data']['count'] > 0) {
            $error_message = '이미 사용 중인 거래처 코드입니다.';
        } else {
            // 거래처 추가
            $insert_query = "INSERT INTO customers (
                customer_code, customer_name, ceo_name, business_number, business_type, business_item,
                address, phone, fax, mobile, email, manager_name, manager_contact, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = array(
                $form_data['customer_code'],
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
                $form_data['notes']
            );
            
            $result = executeNonQuery($insert_query, $params);
            
            if ($result['success']) {
                header('Location: list.php?success=added');
                exit;
            } else {
                $error_message = '거래처 등록 중 오류가 발생했습니다.';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2 class="page-title">거래처 추가</h2>
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
                        <label for="customer_code" class="required">거래처 코드</label>
                        <input 
                            type="text" 
                            id="customer_code" 
                            name="customer_code" 
                            class="form-control"
                            maxlength="4"
                            pattern="[0-9]{4}"
                            value="<?php echo isset($form_data['customer_code']) ? escape($form_data['customer_code']) : $auto_customer_code; ?>"
                            placeholder="4자리 숫자 (예: 0001)"
                            required
                        >
                        <small style="color: #666; font-size: 12px;">자동생성: <?php echo $auto_customer_code; ?> (수정 가능)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_name" class="required">거래처명 (상호)</label>
                        <input 
                            type="text" 
                            id="customer_name" 
                            name="customer_name" 
                            class="form-control"
                            value="<?php echo $form_data['customer_name'] ?? ''; ?>"
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
                            value="<?php echo $form_data['ceo_name'] ?? ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="business_number">사업자등록번호</label>
                        <input 
                            type="text" 
                            id="business_number" 
                            name="business_number" 
                            class="form-control"
                            placeholder="000-00-00000"
                            maxlength="12"
                            value="<?php echo $form_data['business_number'] ?? ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="business_type">업태</label>
                        <input 
                            type="text" 
                            id="business_type" 
                            name="business_type" 
                            class="form-control"
                            placeholder="예: 제조업, 도소매업"
                            value="<?php echo $form_data['business_type'] ?? ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="business_item">종목</label>
                        <input 
                            type="text" 
                            id="business_item" 
                            name="business_item" 
                            class="form-control"
                            placeholder="예: 전자제품, 의류"
                            value="<?php echo $form_data['business_item'] ?? ''; ?>"
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
                            placeholder="02-1234-5678"
                            value="<?php echo $form_data['phone'] ?? ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="fax">팩스번호</label>
                        <input 
                            type="text" 
                            id="fax" 
                            name="fax" 
                            class="form-control"
                            placeholder="02-1234-5679"
                            value="<?php echo $form_data['fax'] ?? ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile">이동전화번호</label>
                        <input 
                            type="text" 
                            id="mobile" 
                            name="mobile" 
                            class="form-control"
                            placeholder="010-1234-5678"
                            value="<?php echo $form_data['mobile'] ?? ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email">이메일</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control"
                            placeholder="example@company.com"
                            value="<?php echo $form_data['email'] ?? ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="manager_name">담당자명</label>
                        <input 
                            type="text" 
                            id="manager_name" 
                            name="manager_name" 
                            class="form-control"
                            value="<?php echo $form_data['manager_name'] ?? ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="manager_contact">담당자 연락처</label>
                        <input 
                            type="text" 
                            id="manager_contact" 
                            name="manager_contact" 
                            class="form-control"
                            placeholder="010-1234-5678"
                            value="<?php echo $form_data['manager_contact'] ?? ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="address">사업장 주소</label>
                        <textarea 
                            id="address" 
                            name="address" 
                            class="form-control"
                            rows="3"
                            placeholder="전체 주소를 입력하세요"
                        ><?php echo $form_data['address'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">비고</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            class="form-control"
                            rows="4"
                            placeholder="추가 메모사항을 입력하세요"
                        ><?php echo $form_data['notes'] ?? ''; ?></textarea>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 2px solid #f0f0f0;">
                <button type="submit" class="btn btn-primary" style="min-width: 150px;">
                    ✅ 거래처 등록
                </button>
                <a href="list.php" class="btn btn-outline" style="min-width: 150px; margin-left: 10px;">
                    ❌ 취소
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// 실시간 코드 중복 체크
let checkTimeout;
const customerCodeInput = document.getElementById('customer_code');
const feedbackDiv = document.createElement('div');
feedbackDiv.id = 'code-feedback';
customerCodeInput.parentNode.appendChild(feedbackDiv);

customerCodeInput.addEventListener('input', function() {
    clearTimeout(checkTimeout);
    const code = this.value.trim();
    
    // 입력값 초기화
    this.classList.remove('is-valid', 'is-invalid');
    feedbackDiv.className = '';
    feedbackDiv.textContent = '';
    
    if (code.length === 4 && /^[0-9]{4}$/.test(code)) {
        checkTimeout = setTimeout(function() {
            // AJAX 요청
            fetch('check_code.php?code=' + code)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.isDuplicate) {
                            customerCodeInput.classList.add('is-invalid');
                            feedbackDiv.className = 'invalid-feedback';
                            feedbackDiv.style.display = 'block';
                            feedbackDiv.textContent = '⚠️ ' + data.message;
                        } else {
                            customerCodeInput.classList.add('is-valid');
                            feedbackDiv.className = 'valid-feedback';
                            feedbackDiv.style.display = 'block';
                            feedbackDiv.textContent = '✅ ' + data.message;
                        }
                    }
                })
                .catch(error => {
                    console.error('코드 중복 체크 실패:', error);
                });
        }, 500);
    }
});
</script>

<?php
include '../includes/footer.php';
?>