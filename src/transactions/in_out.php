<?php
/**
 * MyShop - 입출고 처리
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '입출고 처리';
$error_message = '';
$success_message = '';

$user = getLoginUser();

// 거래처 목록 조회
$customers_query = "SELECT customer_code, customer_name FROM customers ORDER BY customer_name";
$customers_result = fetchAll($customers_query);
$customers = $customers_result['success'] ? $customers_result['data'] : array();

// 상품 목록 조회
$products_query = "SELECT product_code, product_name, product_spec, stock_quantity, standard_price FROM products ORDER BY product_name";
$products_result = fetchAll($products_query);
$products = $products_result['success'] ? $products_result['data'] : array();

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_type = trim($_POST['transaction_type'] ?? '');
    $transaction_date = trim($_POST['transaction_date'] ?? '');
    $customer_code = trim($_POST['customer_code'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $items = $_POST['items'] ?? array();
    
    // 유효성 검사
    if (empty($transaction_type)) {
        $error_message = '거래 유형을 선택해주세요.';
    } elseif (empty($transaction_date)) {
        $error_message = '거래일자를 입력해주세요.';
    } elseif (empty($customer_code)) {
        $error_message = '거래처를 선택해주세요.';
    } elseif (empty($items)) {
        $error_message = '최소 1개 이상의 상품을 추가해주세요.';
    } else {
        // 트랜잭션 시작
        sqlsrv_begin_transaction($conn);
        
        try {
            // 총액 계산
            $total_amount = 0;
            foreach ($items as $item) {
                $total_amount += floatval($item['amount']);
            }
            
            // 거래 헤더 등록
            $insert_trans_query = "INSERT INTO transactions (
                transaction_date, transaction_type, customer_code, user_code, total_amount, notes
            ) VALUES (?, ?, ?, ?, ?, ?);
            SELECT SCOPE_IDENTITY() AS transaction_id;";
            
            $trans_params = array(
                $transaction_date,
                $transaction_type,
                $customer_code,
                $user['user_code'],
                $total_amount,
                $notes
            );
            
            $trans_stmt = sqlsrv_query($conn, $insert_trans_query, $trans_params);
            if ($trans_stmt === false) {
                throw new Exception('거래 등록 실패');
            }
            
            sqlsrv_next_result($trans_stmt);
            sqlsrv_fetch($trans_stmt);
            $transaction_id = sqlsrv_get_field($trans_stmt, 0);
            
            // 거래 상세 등록 및 재고 업데이트
            foreach ($items as $item) {
                $product_code = $item['product_code'];
                $quantity = intval($item['quantity']);
                $unit_price = floatval($item['unit_price']);
                $amount = floatval($item['amount']);
                
                // 거래 상세 등록
                $insert_item_query = "INSERT INTO transaction_items (
                    transaction_id, product_code, quantity, unit_price, amount
                ) VALUES (?, ?, ?, ?, ?)";
                
                $item_params = array($transaction_id, $product_code, $quantity, $unit_price, $amount);
                $item_stmt = sqlsrv_query($conn, $insert_item_query, $item_params);
                
                if ($item_stmt === false) {
                    throw new Exception('거래 상세 등록 실패');
                }
                
                // 재고 업데이트
                // IN(입고), OUT_RETURN(출고반품): 재고 증가
                // OUT(출고), IN_RETURN(입고반품): 재고 감소
                if ($transaction_type == 'IN' || $transaction_type == 'OUT_RETURN') {
                    $update_stock_query = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_code = ?";
                } else {
                    $update_stock_query = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_code = ?";
                }
                
                $stock_params = array($quantity, $product_code);
                $stock_stmt = sqlsrv_query($conn, $update_stock_query, $stock_params);
                
                if ($stock_stmt === false) {
                    throw new Exception('재고 업데이트 실패');
                }
            }
            
            // 커밋
            sqlsrv_commit($conn);
            
            $success_message = '입출고 처리가 완료되었습니다.';
            
            // 폼 초기화
            $_POST = array();
            
        } catch (Exception $e) {
            // 롤백
            sqlsrv_rollback($conn);
            $error_message = $e->getMessage();
        }
    }
}

// 거래 유형 한글명
$transaction_types = array(
    'OUT' => '출고',
    'IN' => '입고',
    'OUT_RETURN' => '출고반품',
    'IN_RETURN' => '입고반품'
);

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2 class="page-title">입출고 처리</h2>
        <a href="history.php" class="btn btn-outline">거래내역 보기</a>
    </div>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <?php echo escape($error_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?php echo escape($success_message); ?>
        </div>
    <?php endif; ?>
    
    <div class="section-box">
        <form method="POST" action="" id="transactionForm">
            <div class="form-grid">
                <!-- 좌측: 거래 정보 -->
                <div>
                    <h3 style="margin-bottom: 20px; color: #667eea;">거래 정보</h3>
                    
                    <div class="form-group">
                        <label for="transaction_type" class="required">거래 유형</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <?php foreach ($transaction_types as $type => $name): ?>
                                <label style="display: flex; align-items: center; padding: 12px; border: 2px solid #e0e0e0; border-radius: 5px; cursor: pointer; transition: all 0.3s;">
                                    <input 
                                        type="radio" 
                                        name="transaction_type" 
                                        value="<?php echo $type; ?>"
                                        <?php echo (isset($_POST['transaction_type']) && $_POST['transaction_type'] == $type) || (!isset($_POST['transaction_type']) && $type == 'OUT') ? 'checked' : ''; ?>
                                        style="margin-right: 8px;"
                                        required
                                    >
                                    <span style="font-weight: 600;"><?php echo $name; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="transaction_date" class="required">거래일자</label>
                        <input 
                            type="date" 
                            id="transaction_date" 
                            name="transaction_date" 
                            class="form-control"
                            value="<?php echo $_POST['transaction_date'] ?? date('Y-m-d'); ?>"
                            max="<?php echo date('Y-m-d'); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_code" class="required">거래처</label>
                        <select 
                            id="customer_code" 
                            name="customer_code" 
                            class="form-control"
                            required
                        >
                            <option value="">거래처 선택</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['customer_code']; ?>"
                                    <?php echo (isset($_POST['customer_code']) && $_POST['customer_code'] == $customer['customer_code']) ? 'selected' : ''; ?>>
                                    [<?php echo $customer['customer_code']; ?>] <?php echo escape($customer['customer_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">비고</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            class="form-control"
                            rows="4"
                            placeholder="거래 관련 메모를 입력하세요"
                        ><?php echo $_POST['notes'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>입력자</label>
                        <input 
                            type="text" 
                            class="form-control"
                            value="<?php echo escape($user['user_name']); ?> (<?php echo escape($user['user_code']); ?>)"
                            disabled
                        >
                    </div>
                </div>
                
                <!-- 우측: 상품 선택 -->
                <div>
                    <h3 style="margin-bottom: 20px; color: #667eea;">상품 선택</h3>
                    
                    <div class="form-group">
                        <label>상품 추가</label>
                        <select id="product_select" class="form-control">
                            <option value="">상품을 선택하세요</option>
                            <?php foreach ($products as $product): ?>
                                <option 
                                    value="<?php echo $product['product_code']; ?>"
                                    data-name="<?php echo escape($product['product_name']); ?>"
                                    data-spec="<?php echo escape($product['product_spec']); ?>"
                                    data-stock="<?php echo $product['stock_quantity']; ?>"
                                    data-price="<?php echo $product['standard_price']; ?>">
                                    [<?php echo $product['product_code']; ?>] <?php echo escape($product['product_name']); ?>
                                    <?php if ($product['product_spec']): ?>
                                        (<?php echo escape($product['product_spec']); ?>)
                                    <?php endif; ?>
                                    - 재고: <?php echo number_format($product['stock_quantity']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="button" class="btn btn-success" onclick="addProduct()" style="width: 100%; margin-bottom: 20px;">
                        ➕ 상품 추가
                    </button>
                </div>
            </div>
            
            <!-- 상품 목록 테이블 -->
            <div style="margin-top: 30px;">
                <h3 style="margin-bottom: 15px; color: #667eea;">상품 목록</h3>
                
                <div style="overflow-x: auto;">
                    <table class="table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 80px;">상품코드</th>
                                <th>상품명</th>
                                <th style="width: 120px;">규격</th>
                                <th style="width: 100px;">현재재고</th>
                                <th style="width: 100px;">수량</th>
                                <th style="width: 120px;">단가</th>
                                <th style="width: 150px;">금액</th>
                                <th style="width: 60px;">삭제</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr id="emptyRow">
                                <td colspan="8" class="text-center text-muted">
                                    상품을 추가해주세요
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td colspan="6" class="text-right">합계</td>
                                <td id="totalAmount" class="text-right">0원</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 2px solid #f0f0f0;">
                <button type="submit" class="btn btn-primary" style="min-width: 200px; font-size: 16px;">
                    ✅ 입출고 처리
                </button>
                <button type="button" class="btn btn-outline" onclick="resetForm()" style="min-width: 150px; margin-left: 10px;">
                    🔄 초기화
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = 0;

// 상품 추가
function addProduct() {
    const select = document.getElementById('product_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!selectedOption.value) {
        alert('상품을 선택해주세요.');
        return;
    }
    
    const productCode = selectedOption.value;
    const productName = selectedOption.dataset.name;
    const productSpec = selectedOption.dataset.spec;
    const stockQty = parseInt(selectedOption.dataset.stock);
    const standardPrice = parseFloat(selectedOption.dataset.price);
    
    // 중복 체크
    const existingRows = document.querySelectorAll('#itemsBody tr[data-product-code="' + productCode + '"]');
    if (existingRows.length > 0) {
        alert('이미 추가된 상품입니다.');
        return;
    }
    
    // 빈 행 제거
    const emptyRow = document.getElementById('emptyRow');
    if (emptyRow) {
        emptyRow.remove();
    }
    
    // 새 행 추가
    const tbody = document.getElementById('itemsBody');
    const row = tbody.insertRow();
    row.dataset.productCode = productCode;
    row.dataset.index = itemIndex;
    
    const defaultQty = 1;
    const defaultAmount = standardPrice * defaultQty;
    
    row.innerHTML = `
        <td><strong>${productCode}</strong></td>
        <td>${productName}</td>
        <td>${productSpec || '-'}</td>
        <td class="text-right">
            <span class="${stockQty < 0 ? 'badge badge-danger' : 'badge badge-success'}">
                ${numberFormat(stockQty)}
            </span>
        </td>
        <td>
            <input type="number" 
                   name="items[${itemIndex}][quantity]" 
                   class="form-control" 
                   value="${defaultQty}"
                   min="1"
                   onchange="calculateAmount(this)"
                   required
                   style="text-align: right;">
            <input type="hidden" name="items[${itemIndex}][product_code]" value="${productCode}">
        </td>
        <td>
            <input type="number" 
                   name="items[${itemIndex}][unit_price]" 
                   class="form-control" 
                   value="${standardPrice}"
                   min="0"
                   step="0.01"
                   onchange="calculateAmount(this)"
                   required
                   style="text-align: right;">
        </td>
        <td>
            <input type="number" 
                   name="items[${itemIndex}][amount]" 
                   class="form-control" 
                   value="${defaultAmount}"
                   min="0"
                   step="0.01"
                   onchange="calculateUnitPrice(this)"
                   required
                   style="text-align: right; font-weight: bold;">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="삭제">
                🗑️
            </button>
        </td>
    `;
    
    itemIndex++;
    
    // 합계 계산
    calculateTotal();
    
    // 선택 초기화
    select.selectedIndex = 0;
}

// 금액 = 수량 × 단가 계산
function calculateAmount(element) {
    const row = element.closest('tr');
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
    const amount = quantity * unitPrice;
    
    row.querySelector('input[name*="[amount]"]').value = amount.toFixed(2);
    calculateTotal();
}

// 단가 = 금액 ÷ 수량 계산
function calculateUnitPrice(element) {
    const row = element.closest('tr');
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 1;
    const amount = parseFloat(row.querySelector('input[name*="[amount]"]').value) || 0;
    const unitPrice = amount / quantity;
    
    row.querySelector('input[name*="[unit_price]"]').value = unitPrice.toFixed(2);
    calculateTotal();
}

// 합계 계산
function calculateTotal() {
    let total = 0;
    const amountInputs = document.querySelectorAll('input[name*="[amount]"]');
    
    amountInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    document.getElementById('totalAmount').textContent = numberFormat(Math.round(total)) + '원';
}

// 행 삭제
function removeRow(button) {
    const row = button.closest('tr');
    row.remove();
    
    // 모든 행이 삭제되면 빈 행 추가
    const tbody = document.getElementById('itemsBody');
    if (tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr id="emptyRow">
                <td colspan="8" class="text-center text-muted">
                    상품을 추가해주세요
                </td>
            </tr>
        `;
    }
    
    calculateTotal();
}

// 폼 초기화
function resetForm() {
    if (confirm('입력한 내용을 모두 초기화하시겠습니까?')) {
        document.getElementById('transactionForm').reset();
        document.getElementById('itemsBody').innerHTML = `
            <tr id="emptyRow">
                <td colspan="8" class="text-center text-muted">
                    상품을 추가해주세요
                </td>
            </tr>
        `;
        calculateTotal();
        itemIndex = 0;
    }
}

// 숫자 포맷 함수
function numberFormat(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// 라디오 버튼 스타일 변경
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="transaction_type"]');
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            radios.forEach(r => {
                const label = r.closest('label');
                if (r.checked) {
                    label.style.borderColor = '#667eea';
                    label.style.backgroundColor = '#f0f4ff';
                } else {
                    label.style.borderColor = '#e0e0e0';
                    label.style.backgroundColor = 'white';
                }
            });
        });
        
        // 초기 스타일 적용
        if (radio.checked) {
            radio.dispatchEvent(new Event('change'));
        }
    });
});
</script>

<?php
include '../includes/footer.php';
?>