<?php
/**
 * MyShop - 입출고 처리 (개선버전)
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
$products_query = "SELECT product_code, product_name, product_spec, stock_quantity, standard_price, image_url FROM products ORDER BY product_name";
$products_result = fetchAll($products_query);
$products = $products_result['success'] ? $products_result['data'] : array();

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_type = trim($_POST['transaction_type'] ?? '');
    $transaction_date = trim($_POST['transaction_date'] ?? '');
    $customer_code = trim($_POST['customer_code'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $product_codes = $_POST['product_code'] ?? array();
    $quantities = $_POST['quantity'] ?? array();
    $unit_prices = $_POST['unit_price'] ?? array();
    
    // 유효성 검사
    if (empty($transaction_type)) {
        $error_message = '거래 유형을 선택해주세요.';
    } elseif (empty($transaction_date)) {
        $error_message = '거래일자를 입력해주세요.';
    } elseif (empty($customer_code)) {
        $error_message = '거래처를 선택해주세요.';
    } elseif (empty($product_codes) || count($product_codes) == 0) {
        $error_message = '최소 1개 이상의 상품을 추가해주세요.';
    } else {
        // 트랜잭션 시작
        sqlsrv_begin_transaction($conn);
        
        try {
            // 총액 계산
            $total_amount = 0;
            $items_data = array();
            
            for ($i = 0; $i < count($product_codes); $i++) {
                $product_code = trim($product_codes[$i]);
                $quantity = intval($quantities[$i]);
                $unit_price = floatval(str_replace(',', '', $unit_prices[$i]));
                $amount = $quantity * $unit_price;
                
                if ($quantity > 0 && $unit_price >= 0) {
                    $items_data[] = array(
                        'product_code' => $product_code,
                        'quantity' => $quantity,
                        'unit_price' => $unit_price,
                        'amount' => $amount
                    );
                    $total_amount += $amount;
                }
            }
            
            if (count($items_data) == 0) {
                throw new Exception('유효한 상품이 없습니다.');
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
                throw new Exception('거래 등록 실패: ' . print_r(sqlsrv_errors(), true));
            }
            
            // transaction_id 가져오기
            sqlsrv_next_result($trans_stmt);
            sqlsrv_fetch($trans_stmt);
            $transaction_id = sqlsrv_get_field($trans_stmt, 0);
            
            if (!$transaction_id) {
                throw new Exception('거래 ID 생성 실패');
            }
            
            // 거래 상세 등록
            foreach ($items_data as $item) {
                // 거래 상세 등록
                $insert_item_query = "INSERT INTO transaction_items (
                    transaction_id, product_code, quantity, unit_price, amount
                ) VALUES (?, ?, ?, ?, ?)";
                
                $item_params = array(
                    $transaction_id, 
                    $item['product_code'], 
                    $item['quantity'], 
                    $item['unit_price'], 
                    $item['amount']
                );
                
                $item_stmt = sqlsrv_query($conn, $insert_item_query, $item_params);
                
                if ($item_stmt === false) {
                    throw new Exception('거래 상세 등록 실패: ' . print_r(sqlsrv_errors(), true));
                }
            }
            
            // 트리거가 재고를 자동으로 업데이트하므로 별도 업데이트 불필요
            // 하지만 트리거가 없는 경우를 대비하여 수동 업데이트 코드 추가
            
            foreach ($items_data as $item) {
                // 재고 업데이트
                // IN(입고), OUT_RETURN(출고반품): 재고 증가
                // OUT(출고), IN_RETURN(입고반품): 재고 감소
                if ($transaction_type == 'IN' || $transaction_type == 'OUT_RETURN') {
                    $update_stock_query = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_code = ?";
                } elseif ($transaction_type == 'OUT' || $transaction_type == 'IN_RETURN') {
                    $update_stock_query = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_code = ?";
                } else {
                    // RECEIVE, PAYMENT는 재고 변동 없음
                    continue;
                }
                
                $stock_params = array($item['quantity'], $item['product_code']);
                $stock_stmt = sqlsrv_query($conn, $update_stock_query, $stock_params);
                
                if ($stock_stmt === false) {
                    throw new Exception('재고 업데이트 실패: ' . print_r(sqlsrv_errors(), true));
                }
            }
            
            // 커밋
            sqlsrv_commit($conn);
            
            // 성공 메시지와 함께 상세 페이지로 리다이렉트
            header("Location: detail.php?id={$transaction_id}&success=1");
            exit;
            
        } catch (Exception $e) {
            // 롤백
            sqlsrv_rollback($conn);
            $error_message = $e->getMessage();
        }
    }
}

// 성공 메시지 (리다이렉트에서 돌아온 경우)
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = '✅ 입출고 처리가 완료되었습니다!';
}

include '../includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">🚚 입출고 처리</h2>
    <div style="display: flex; gap: 10px;">
        <a href="history.php" class="btn btn-outline">📋 거래내역</a>
    </div>
</div>

<?php if ($error_message): ?>
    <div class="alert alert-error alert-dismissible">
        ❌ <?php echo escape($error_message); ?>
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible">
        <?php echo $success_message; ?>
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
<?php endif; ?>

<div class="section-box">
    <form method="POST" action="" id="transactionForm">
        <div class="form-grid">
            <!-- 좌측: 거래 정보 -->
            <div>
                <h3 style="margin-bottom: 20px; color: #667eea; display: flex; align-items: center; gap: 10px;">
                    📝 거래 정보
                </h3>
                
                <div class="form-group">
                    <label for="transaction_type" class="required">거래 유형</label>
                    <select 
                        id="transaction_type" 
                        name="transaction_type" 
                        class="form-control"
                        required
                        style="font-size: 16px; padding: 12px; font-weight: 600;"
                    >
                        <option value="IN" <?php echo (!isset($_POST['transaction_type']) || $_POST['transaction_type'] == 'IN') ? 'selected' : ''; ?>>
                            📥 입고
                        </option>
                        <option value="OUT" <?php echo (isset($_POST['transaction_type']) && $_POST['transaction_type'] == 'OUT') ? 'selected' : ''; ?>>
                            📤 출고
                        </option>
                        <option value="IN_RETURN" <?php echo (isset($_POST['transaction_type']) && $_POST['transaction_type'] == 'IN_RETURN') ? 'selected' : ''; ?>>
                            ↩️ 입고반품
                        </option>
                        <option value="OUT_RETURN" <?php echo (isset($_POST['transaction_type']) && $_POST['transaction_type'] == 'OUT_RETURN') ? 'selected' : ''; ?>>
                            ↪️ 출고반품
                        </option>
                    </select>
                    <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                        💡 입고/출고반품은 재고 증가, 출고/입고반품은 재고 감소
                    </small>
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
                        <option value="">거래처를 선택하세요</option>
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
                        value="👤 <?php echo escape($user['user_name']); ?> (<?php echo escape($user['user_code']); ?>)"
                        disabled
                        style="background-color: #f8f9fa;"
                    >
                </div>
            </div>
            
            <!-- 우측: 상품 선택 -->
            <div>
                <h3 style="margin-bottom: 20px; color: #667eea; display: flex; align-items: center; gap: 10px;">
                    📦 상품 선택
                </h3>
                
                <div class="form-group">
                    <label>상품 검색 및 추가</label>
                    <div class="input-group">
                        <input 
                            type="text" 
                            id="product_search" 
                            class="form-control" 
                            placeholder="상품명, 코드로 검색..."
                            onkeyup="filterProducts()"
                        >
                    </div>
                </div>
                
                <div style="max-height: 400px; overflow-y: auto; border: 2px solid #e0e0e0; border-radius: 10px; padding: 10px;">
                    <?php foreach ($products as $product): ?>
                        <div class="product-item" 
                             data-code="<?php echo $product['product_code']; ?>"
                             data-name="<?php echo escape($product['product_name']); ?>"
                             data-spec="<?php echo escape($product['product_spec']); ?>"
                             data-stock="<?php echo $product['stock_quantity']; ?>"
                             data-price="<?php echo $product['standard_price']; ?>"
                             onclick="addProductFromCard(this)"
                             style="padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 12px;">
                            
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?php echo escape($product['image_url']); ?>" 
                                     alt="<?php echo escape($product['product_name']); ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;"
                                     onerror="this.style.display='none';">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 20px;">📦</div>
                            <?php endif; ?>
                            
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 4px;">
                                    [<?php echo $product['product_code']; ?>] <?php echo escape($product['product_name']); ?>
                                </div>
                                <div style="font-size: 12px; color: #666;">
                                    <?php if ($product['product_spec']): ?>
                                        <?php echo escape($product['product_spec']); ?> | 
                                    <?php endif; ?>
                                    재고: <span class="<?php echo $product['stock_quantity'] < 0 ? 'text-danger' : ''; ?>" style="font-weight: 600;">
                                        <?php echo formatNumber($product['stock_quantity']); ?>
                                    </span> | 
                                    단가: <?php echo formatCurrency($product['standard_price']); ?>
                                </div>
                            </div>
                            <div style="color: #667eea; font-size: 20px;">➕</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- 상품 목록 테이블 -->
        <div style="margin-top: 30px;">
            <h3 style="margin-bottom: 15px; color: #667eea; display: flex; align-items: center; justify-content: space-between;">
                <span>📋 선택된 상품</span>
                <span id="itemCount" style="font-size: 14px; color: #666;">0개 선택</span>
            </h3>
            
            <div class="table-responsive">
                <table class="table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">번호</th>
                            <th style="width: 100px;">상품코드</th>
                            <th>상품명</th>
                            <th style="width: 120px;">규격</th>
                            <th style="width: 100px;">현재재고</th>
                            <th style="width: 100px;">수량</th>
                            <th style="width: 130px;">단가</th>
                            <th style="width: 150px;">금액</th>
                            <th style="width: 80px;">삭제</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr id="emptyRow">
                            <td colspan="9" class="text-center text-muted" style="padding: 40px;">
                                <div style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;">📦</div>
                                상품을 선택해주세요
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <td colspan="7" class="text-right" style="font-size: 16px; font-weight: bold; padding: 15px;">
                                합계
                            </td>
                            <td id="totalAmount" class="text-right" style="font-size: 18px; font-weight: bold; padding: 15px;">
                                0원
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 3px solid #f0f0f0;">
            <button type="submit" class="btn btn-primary btn-lg" style="min-width: 220px; font-size: 18px; padding: 15px 30px;">
                ✅ 입출고 처리
            </button>
            <button type="button" class="btn btn-outline btn-lg" onclick="resetForm()" style="min-width: 180px; margin-left: 15px; font-size: 18px; padding: 15px 30px;">
                🔄 초기화
            </button>
        </div>
    </form>
</div>

<style>
.product-item:hover {
    border-color: #667eea !important;
    background-color: #f8f9ff;
    transform: translateX(5px);
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.2);
}

.product-item.added {
    opacity: 0.5;
    pointer-events: none;
}

/* 거래 유형 선택 박스 강조 스타일 */
#transaction_type {
    border: 2px solid #667eea;
    background: linear-gradient(to right, #ffffff 0%, #f8f9ff 100%);
    transition: all 0.3s;
}

#transaction_type:hover {
    border-color: #764ba2;
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.2);
}

#transaction_type:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let itemIndex = 0;
let addedProducts = new Set();

// 상품 검색 필터
function filterProducts() {
    const searchText = document.getElementById('product_search').value.toLowerCase();
    const productItems = document.querySelectorAll('.product-item');
    
    productItems.forEach(item => {
        const code = item.dataset.code.toLowerCase();
        const name = item.dataset.name.toLowerCase();
        const spec = item.dataset.spec ? item.dataset.spec.toLowerCase() : '';
        
        if (code.includes(searchText) || name.includes(searchText) || spec.includes(searchText)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// 상품 카드에서 추가
function addProductFromCard(element) {
    if (element.classList.contains('added')) {
        alert('이미 추가된 상품입니다.');
        return;
    }
    
    const productCode = element.dataset.code;
    const productName = element.dataset.name;
    const productSpec = element.dataset.spec;
    const stockQty = parseInt(element.dataset.stock);
    const standardPrice = parseFloat(element.dataset.price);
    
    addProduct(productCode, productName, productSpec, stockQty, standardPrice);
    
    // 추가된 상품 표시
    element.classList.add('added');
    addedProducts.add(productCode);
}

// 상품 추가
function addProduct(productCode, productName, productSpec, stockQty, standardPrice) {
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
    const rowNum = tbody.children.length;
    
    row.innerHTML = `
        <td class="text-center"><strong>${rowNum}</strong></td>
        <td><strong>${productCode}</strong></td>
        <td>${productName}</td>
        <td>${productSpec || '-'}</td>
        <td class="text-center">
            <span class="badge ${stockQty < 0 ? 'badge-danger' : 'badge-success'}">
                ${numberFormat(stockQty)}
            </span>
        </td>
        <td>
            <input type="number" 
                   name="quantity[]" 
                   class="form-control" 
                   value="${defaultQty}"
                   min="1"
                   onchange="calculateAmount(this)"
                   required
                   style="text-align: right; font-weight: 600;">
            <input type="hidden" name="product_code[]" value="${productCode}">
        </td>
        <td>
            <input type="text" 
                   name="unit_price[]" 
                   class="form-control price-input" 
                   value="${numberFormat(standardPrice)}"
                   onblur="formatPriceInput(this); calculateAmount(this)"
                   onfocus="this.select()"
                   required
                   style="text-align: right;">
        </td>
        <td>
            <input type="text" 
                   class="form-control amount-display" 
                   value="${numberFormat(defaultAmount)}"
                   readonly
                   style="text-align: right; font-weight: bold; background-color: #f8f9fa;">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="삭제">
                🗑️
            </button>
        </td>
    `;
    
    itemIndex++;
    updateItemCount();
    calculateTotal();
}

// 금액 = 수량 × 단가 계산
function calculateAmount(element) {
    const row = element.closest('tr');
    const quantityInput = row.querySelector('input[name="quantity[]"]');
    const unitPriceInput = row.querySelector('input[name="unit_price[]"]');
    const amountDisplay = row.querySelector('.amount-display');
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = removeComma(unitPriceInput.value);
    const amount = quantity * unitPrice;
    
    amountDisplay.value = numberFormat(Math.round(amount));
    calculateTotal();
}

// 합계 계산
function calculateTotal() {
    let total = 0;
    const amountDisplays = document.querySelectorAll('.amount-display');
    
    amountDisplays.forEach(display => {
        total += removeComma(display.value);
    });
    
    document.getElementById('totalAmount').textContent = numberFormat(Math.round(total)) + '원';
}

// 항목 수 업데이트
function updateItemCount() {
    const tbody = document.getElementById('itemsBody');
    const count = tbody.children.length;
    
    if (count === 0 || document.getElementById('emptyRow')) {
        document.getElementById('itemCount').textContent = '0개 선택';
    } else {
        document.getElementById('itemCount').textContent = count + '개 선택';
    }
}

// 행 삭제
function removeRow(button) {
    const row = button.closest('tr');
    const productCode = row.dataset.productCode;
    
    row.remove();
    
    // 상품 카드 활성화
    const productCard = document.querySelector(`.product-item[data-code="${productCode}"]`);
    if (productCard) {
        productCard.classList.remove('added');
    }
    addedProducts.delete(productCode);
    
    // 번호 재정렬
    renumberRows();
    
    // 모든 행이 삭제되면 빈 행 추가
    const tbody = document.getElementById('itemsBody');
    if (tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr id="emptyRow">
                <td colspan="9" class="text-center text-muted" style="padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;">📦</div>
                    상품을 선택해주세요
                </td>
            </tr>
        `;
    }
    
    updateItemCount();
    calculateTotal();
}

// 행 번호 재정렬
function renumberRows() {
    const tbody = document.getElementById('itemsBody');
    const rows = tbody.querySelectorAll('tr:not(#emptyRow)');
    
    rows.forEach((row, index) => {
        row.cells[0].querySelector('strong').textContent = index + 1;
    });
}

// 폼 초기화
function resetForm() {
    if (confirm('⚠️ 입력한 내용을 모두 초기화하시겠습니까?')) {
        document.getElementById('transactionForm').reset();
        document.getElementById('itemsBody').innerHTML = `
            <tr id="emptyRow">
                <td colspan="9" class="text-center text-muted" style="padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;">📦</div>
                    상품을 선택해주세요
                </td>
            </tr>
        `;
        
        // 모든 상품 카드 활성화
        document.querySelectorAll('.product-item').forEach(item => {
            item.classList.remove('added');
        });
        
        addedProducts.clear();
        updateItemCount();
        calculateTotal();
        itemIndex = 0;
        
        // 검색 초기화
        document.getElementById('product_search').value = '';
        filterProducts();
    }
}

// 숫자 포맷 함수
function numberFormat(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// 콤마 제거 함수
function removeComma(str) {
    if (typeof str === 'number') return str;
    return parseFloat(str.replace(/,/g, '')) || 0;
}

// 가격 입력 포맷
function formatPriceInput(input) {
    let value = input.value.replace(/,/g, '');
    if (value && !isNaN(value)) {
        input.value = numberFormat(value);
    }
}

// 라디오 버튼 스타일 변경
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="transaction_type"]');
    
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            radios.forEach(r => {
                const card = r.closest('.radio-card');
                if (r.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
        });
        
        // 초기 스타일 적용
        if (radio.checked) {
            radio.dispatchEvent(new Event('change'));
        }
    });
    
    // 폼 제출 시 확인
    document.getElementById('transactionForm').addEventListener('submit', function(e) {
        const tbody = document.getElementById('itemsBody');
        const hasItems = tbody.children.length > 0 && !document.getElementById('emptyRow');
        
        if (!hasItems) {
            e.preventDefault();
            alert('⚠️ 최소 1개 이상의 상품을 추가해주세요.');
            return false;
        }
        
        const transactionType = document.querySelector('input[name="transaction_type"]:checked');
        const typeName = transactionType.nextElementSibling.querySelector('.radio-text').textContent;
        
        return confirm(`✅ ${typeName} 처리를 진행하시겠습니까?`);
    });
});
</script>

<?php
include '../includes/footer.php';
?>