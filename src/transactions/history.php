<?php
/**
 * MyShop - 거래내역 조회
 */

define('MYSHOP_APP', true);

require_once '../config/database.php';
require_once '../includes/session.php';

// 로그인 체크
requireLogin();

$page_title = '거래내역 조회';

// 검색 조건
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : date('Y-m-01'); // 이번 달 1일
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : date('Y-m-d'); // 오늘
$customer_code = isset($_GET['customer_code']) ? trim($_GET['customer_code']) : '';
$transaction_type = isset($_GET['transaction_type']) ? trim($_GET['transaction_type']) : '';

// 거래처 목록
$customers_query = "SELECT customer_code, customer_name FROM customers ORDER BY customer_name";
$customers_result = fetchAll($customers_query);
$customers = $customers_result['success'] ? $customers_result['data'] : array();

// 거래내역 조회
$query = "SELECT 
            t.transaction_id,
            t.transaction_date,
            t.transaction_type,
            c.customer_name,
            t.total_amount,
            t.notes,
            u.user_name,
            t.created_at
          FROM transactions t
          INNER JOIN customers c ON t.customer_code = c.customer_code
          INNER JOIN users u ON t.user_code = u.user_code
          WHERE t.transaction_date BETWEEN ? AND ?";

$params = array($start_date, $end_date);

if (!empty($customer_code)) {
    $query .= " AND t.customer_code = ?";
    $params[] = $customer_code;
}

if (!empty($transaction_type)) {
    $query .= " AND t.transaction_type = ?";
    $params[] = $transaction_type;
}

$query .= " ORDER BY t.transaction_date DESC, t.transaction_id DESC";

$result = fetchAll($query, $params);
$transactions = $result['success'] ? $result['data'] : array();

// 거래 유형 한글명
$transaction_types = array(
    'IN' => '입고',
    'OUT' => '출고',
    'IN_RETURN' => '입고반품',
    'OUT_RETURN' => '출고반품',
    'RECEIVE' => '수금',
    'PAYMENT' => '지급'
);

// 합계 계산
$total_in = 0;
$total_out = 0;
$total_receive = 0;
$total_payment = 0;

foreach ($transactions as $trans) {
    switch ($trans['transaction_type']) {
        case 'IN':
        case 'OUT_RETURN':
            $total_in += $trans['total_amount'];
            break;
        case 'OUT':
        case 'IN_RETURN':
            $total_out += $trans['total_amount'];
            break;
        case 'RECEIVE':
            $total_receive += $trans['total_amount'];
            break;
        case 'PAYMENT':
            $total_payment += $trans['total_amount'];
            break;
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2 class="page-title">거래내역 조회</h2>
        <a href="in_out.php" class="btn btn-primary">입출고 처리</a>
    </div>
    
    <div class="section-box">
        <!-- 검색 폼 -->
        <form method="GET" action="" style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <div class="form-grid">
                <div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="start_date">시작일자</label>
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            class="form-control"
                            value="<?php echo escape($start_date); ?>"
                        >
                    </div>
                </div>
                
                <div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="end_date">종료일자</label>
                        <input 
                            type="date" 
                            id="end_date" 
                            name="end_date" 
                            class="form-control"
                            value="<?php echo escape($end_date); ?>"
                        >
                    </div>
                </div>
            </div>
            
            <div class="form-grid" style="margin-top: 15px;">
                <div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="customer_code">거래처</label>
                        <select id="customer_code" name="customer_code" class="form-control">
                            <option value="">전체</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['customer_code']; ?>"
                                    <?php echo ($customer_code == $customer['customer_code']) ? 'selected' : ''; ?>>
                                    [<?php echo $customer['customer_code']; ?>] <?php echo escape($customer['customer_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="transaction_type">거래유형</label>
                        <select id="transaction_type" name="transaction_type" class="form-control">
                            <option value="">전체</option>
                            <?php foreach ($transaction_types as $type => $name): ?>
                                <option value="<?php echo $type; ?>"
                                    <?php echo ($transaction_type == $type) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 15px;">
                <button type="submit" class="btn btn-primary" style="min-width: 150px;">
                    🔍 조회
                </button>
                <a href="history.php" class="btn btn-outline" style="min-width: 150px; margin-left: 10px;">
                    🔄 초기화
                </a>
                <button type="button" onclick="window.print()" class="btn btn-success" style="min-width: 150px; margin-left: 10px;">
                    🖨️ 인쇄
                </button>
            </div>
        </form>
        
        <!-- 통계 요약 -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px;">
                <div style="font-size: 14px; opacity: 0.9;">입고 총액</div>
                <div style="font-size: 24px; font-weight: bold; margin-top: 5px;">
                    <?php echo number_format($total_in); ?>원
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px;">
                <div style="font-size: 14px; opacity: 0.9;">출고 총액</div>
                <div style="font-size: 24px; font-weight: bold; margin-top: 5px;">
                    <?php echo number_format($total_out); ?>원
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px;">
                <div style="font-size: 14px; opacity: 0.9;">수금 총액</div>
                <div style="font-size: 24px; font-weight: bold; margin-top: 5px;">
                    <?php echo number_format($total_receive); ?>원
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 10px;">
                <div style="font-size: 14px; opacity: 0.9;">지급 총액</div>
                <div style="font-size: 24px; font-weight: bold; margin-top: 5px;">
                    <?php echo number_format($total_payment); ?>원
                </div>
            </div>
        </div>
        
        <!-- 거래내역 목록 -->
        <?php if (count($transactions) > 0): ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>거래번호</th>
                            <th>거래일자</th>
                            <th>거래유형</th>
                            <th>거래처</th>
                            <th>금액</th>
                            <th>입력자</th>
                            <th>입력일시</th>
                            <th>비고</th>
                            <th>상세</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $trans): ?>
                            <tr>
                                <td><strong><?php echo $trans['transaction_id']; ?></strong></td>
                                <td><?php echo formatDate($trans['transaction_date']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        $badge_class = '';
                                        switch($trans['transaction_type']) {
                                            case 'IN':
                                            case 'OUT_RETURN':
                                                $badge_class = 'success';
                                                break;
                                            case 'OUT':
                                            case 'IN_RETURN':
                                                $badge_class = 'danger';
                                                break;
                                            case 'RECEIVE':
                                                $badge_class = 'info';
                                                break;
                                            case 'PAYMENT':
                                                $badge_class = 'warning';
                                                break;
                                        }
                                        echo $badge_class;
                                    ?>">
                                        <?php echo $transaction_types[$trans['transaction_type']]; ?>
                                    </span>
                                </td>
                                <td><?php echo escape($trans['customer_name']); ?></td>
                                <td class="text-right"><strong><?php echo number_format($trans['total_amount']); ?>원</strong></td>
                                <td><?php echo escape($trans['user_name']); ?></td>
                                <td><?php echo formatDateTime($trans['created_at']); ?></td>
                                <td><?php echo escape(mb_substr($trans['notes'], 0, 20)); ?><?php echo mb_strlen($trans['notes']) > 20 ? '...' : ''; ?></td>
                                <td>
                                    <a href="detail.php?id=<?php echo $trans['transaction_id']; ?>" 
                                       class="btn btn-sm btn-primary" 
                                       title="상세보기">
                                        📋
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <p class="text-center text-muted" style="margin-top: 20px;">
                총 <?php echo number_format(count($transactions)); ?>건의 거래내역
            </p>
        <?php else: ?>
            <p class="text-center text-muted" style="padding: 60px 20px;">
                조회된 거래내역이 없습니다.<br>
                조회 조건을 변경하거나 새로운 거래를 등록해주세요.
            </p>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .navbar, .page-header a, .btn, form, .table th:last-child, .table td:last-child {
        display: none !important;
    }
    
    .section-box {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    body {
        background: white;
    }
    
    .page-header {
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
}
</style>

<?php
include '../includes/footer.php';
?>