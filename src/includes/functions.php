<?php
/**
 * MyShop - 공통 함수
 * 여러 페이지에서 공통으로 사용하는 함수들
 */

// 이 파일이 직접 접근되는 것을 방지
if (!defined('MYSHOP_APP')) {
    die('Direct access not permitted');
}

/**
 * DateTime 안전 포맷 함수
 * MSSQL datetime을 문자열로 변환
 * 
 * @param mixed $datetime DateTime 객체, 문자열, 또는 null
 * @param string $format 날짜 포맷 (기본값: 'Y-m-d')
 * @return string 포맷된 날짜 문자열
 */
function formatDateTime($datetime, $format = 'Y-m-d') {
    if (empty($datetime)) {
        return '-';
    }
    
    // DateTime 객체인 경우
    if ($datetime instanceof DateTime) {
        return $datetime->format($format);
    }
    
    // 문자열인 경우
    if (is_string($datetime)) {
        $timestamp = strtotime($datetime);
        if ($timestamp !== false) {
            return date($format, $timestamp);
        }
    }
    
    return '-';
}

/**
 * 날짜+시간 포맷 (Y-m-d H:i)
 * 
 * @param mixed $datetime DateTime 객체 또는 문자열
 * @return string 포맷된 날짜시간 문자열
 */
function formatDateTimeWithTime($datetime) {
    return formatDateTime($datetime, 'Y-m-d H:i');
}

/**
 * 한글 날짜 포맷 (2025년 10월 14일)
 * 
 * @param mixed $datetime DateTime 객체 또는 문자열
 * @return string 한글 날짜 문자열
 */
function formatDateKorean($datetime) {
    if (empty($datetime)) {
        return '-';
    }
    
    $dateObj = ($datetime instanceof DateTime) ? $datetime : new DateTime($datetime);
    
    $year = $dateObj->format('Y');
    $month = $dateObj->format('n');
    $day = $dateObj->format('j');
    
    return "{$year}년 {$month}월 {$day}일";
}

/**
 * 값이 비어있으면 대체 텍스트 반환
 * 
 * @param mixed $value 체크할 값
 * @param string $default 기본값 (기본: '-')
 * @return string HTML 문자열
 */
function displayValue($value, $default = '-') {
    if (empty($value)) {
        return '<span class="text-muted">' . escape($default) . '</span>';
    }
    return escape($value);
}

/**
 * 숫자를 천단위 콤마 형식으로 변환
 * 
 * @param mixed $number 숫자 또는 문자열
 * @return string 포맷된 숫자
 */
function formatNumber($number) {
    if ($number === null || $number === '') {
        return '0';
    }
    
    $num = is_string($number) ? floatval(str_replace(',', '', $number)) : $number;
    
    if (!is_numeric($num)) {
        return '0';
    }
    
    return number_format($num);
}

/**
 * 통화 형식으로 변환 (원화)
 * 
 * @param mixed $number 숫자
 * @return string 포맷된 통화 문자열
 */
function formatCurrency($number) {
    return formatNumber($number) . '원';
}

/**
 * 전화번호 포맷팅 (하이픈 추가)
 * 
 * @param string $phone 전화번호
 * @return string 포맷된 전화번호
 */
function formatPhone($phone) {
    if (empty($phone)) {
        return '';
    }
    
    // 숫자만 추출
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // 길이에 따라 포맷팅
    $length = strlen($phone);
    
    if ($length == 10) {
        // 010-1234-5678
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    } elseif ($length == 11) {
        // 010-1234-5678
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7);
    } elseif ($length == 9) {
        // 02-1234-5678
        return substr($phone, 0, 2) . '-' . substr($phone, 2, 3) . '-' . substr($phone, 5);
    }
    
    return $phone;
}

/**
 * 사업자등록번호 포맷팅 (000-00-00000)
 * 
 * @param string $business_number 사업자등록번호
 * @return string 포맷된 사업자등록번호
 */
function formatBusinessNumber($business_number) {
    if (empty($business_number)) {
        return '';
    }
    
    // 숫자만 추출
    $number = preg_replace('/[^0-9]/', '', $business_number);
    
    if (strlen($number) == 10) {
        return substr($number, 0, 3) . '-' . substr($number, 3, 2) . '-' . substr($number, 5);
    }
    
    return $business_number;
}

/**
 * 거래 유형 한글명 반환
 * 
 * @param string $type 거래 유형 코드
 * @return string 한글 거래 유형명
 */
function getTransactionTypeName($type) {
    $types = array(
        'IN' => '입고',
        'OUT' => '출고',
        'IN_RETURN' => '입고반품',
        'OUT_RETURN' => '출고반품',
        'RECEIVE' => '수금',
        'PAYMENT' => '지급'
    );
    
    return isset($types[$type]) ? $types[$type] : $type;
}

/**
 * 거래 유형 배지 클래스 반환
 * 
 * @param string $type 거래 유형 코드
 * @return string 배지 CSS 클래스
 */
function getTransactionBadgeClass($type) {
    $classes = array(
        'IN' => 'badge-success',
        'OUT' => 'badge-primary',
        'IN_RETURN' => 'badge-warning',
        'OUT_RETURN' => 'badge-warning',
        'RECEIVE' => 'badge-info',
        'PAYMENT' => 'badge-danger'
    );
    
    return isset($classes[$type]) ? $classes[$type] : 'badge-secondary';
}

/**
 * 거래 유형 배지 HTML 생성
 * 
 * @param string $type 거래 유형 코드
 * @return string 배지 HTML
 */
function getTransactionBadge($type) {
    $class = getTransactionBadgeClass($type);
    $name = getTransactionTypeName($type);
    
    return '<span class="badge ' . $class . '">' . escape($name) . '</span>';
}

/**
 * 페이지네이션 생성
 * 
 * @param int $total_items 전체 항목 수
 * @param int $current_page 현재 페이지
 * @param int $per_page 페이지당 항목 수
 * @param string $base_url 기본 URL
 * @return string 페이지네이션 HTML
 */
function generatePagination($total_items, $current_page, $per_page, $base_url) {
    $total_pages = ceil($total_items / $per_page);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // 이전 페이지
    if ($current_page > 1) {
        $html .= '<a href="' . $base_url . '&page=' . ($current_page - 1) . '">‹ 이전</a>';
    } else {
        $html .= '<span class="disabled">‹ 이전</span>';
    }
    
    // 페이지 번호
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $html .= '<a href="' . $base_url . '&page=1">1</a>';
        if ($start > 2) {
            $html .= '<span>...</span>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $base_url . '&page=' . $i . '">' . $i . '</a>';
        }
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<span>...</span>';
        }
        $html .= '<a href="' . $base_url . '&page=' . $total_pages . '">' . $total_pages . '</a>';
    }
    
    // 다음 페이지
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $base_url . '&page=' . ($current_page + 1) . '">다음 ›</a>';
    } else {
        $html .= '<span class="disabled">다음 ›</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * 성공 메시지 HTML 생성
 * 
 * @param string $message 메시지 내용
 * @return string 알림 HTML
 */
function getSuccessAlert($message) {
    return '<div class="alert alert-success">' . escape($message) . '</div>';
}

/**
 * 에러 메시지 HTML 생성
 * 
 * @param string $message 메시지 내용
 * @return string 알림 HTML
 */
function getErrorAlert($message) {
    return '<div class="alert alert-error">' . escape($message) . '</div>';
}

/**
 * 경고 메시지 HTML 생성
 * 
 * @param string $message 메시지 내용
 * @return string 알림 HTML
 */
function getWarningAlert($message) {
    return '<div class="alert alert-warning">' . escape($message) . '</div>';
}
?>