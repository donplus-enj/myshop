<?php
/**
 * MyShop - 데이터베이스 연결 설정
 * MSSQL Server 연결
 */

// 데이터베이스 설정
define('DB_SERVER', '192.168.1.100');
define('DB_PORT', '1433');
define('DB_DATABASE', 'myshop');
define('DB_USERNAME', 'my_login');
define('DB_PASSWORD', 'my_login'); // 실제 비밀번호로 변경 필요
define('DB_CHARSET', 'UTF-8');

// 연결 문자열
$connectionInfo = array(
    "Database" => DB_DATABASE,
    "UID" => DB_USERNAME,
    "PWD" => DB_PASSWORD,
    "CharacterSet" => DB_CHARSET
);

// MSSQL 연결
$conn = sqlsrv_connect(DB_SERVER.",".DB_PORT, $connectionInfo);

// 연결 확인
if ($conn === false) {
    die(json_encode(array(
        'success' => false,
        'message' => '데이터베이스 연결 실패',
        'errors' => sqlsrv_errors()
    )));
}

/**
 * 안전한 쿼리 실행 함수
 */
function executeQuery($query, $params = array()) {
    global $conn;
    
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        return array(
            'success' => false,
            'message' => '쿼리 실행 실패',
            'errors' => sqlsrv_errors()
        );
    }
    
    return array(
        'success' => true,
        'statement' => $stmt
    );
}

/**
 * SELECT 쿼리 실행 및 결과 반환
 */
function fetchAll($query, $params = array()) {
    global $conn;
    
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        return array(
            'success' => false,
            'message' => '쿼리 실행 실패',
            'errors' => sqlsrv_errors()
        );
    }
    
    $results = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }
    
    return array(
        'success' => true,
        'data' => $results
    );
}

/**
 * 단일 행 조회
 */
function fetchOne($query, $params = array()) {
    global $conn;
    
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        return array(
            'success' => false,
            'message' => '쿼리 실행 실패',
            'errors' => sqlsrv_errors()
        );
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    return array(
        'success' => true,
        'data' => $row
    );
}

/**
 * INSERT/UPDATE/DELETE 쿼리 실행
 */
function executeNonQuery($query, $params = array()) {
    global $conn;
    
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        return array(
            'success' => false,
            'message' => '쿼리 실행 실패',
            'errors' => sqlsrv_errors()
        );
    }
    
    $rows_affected = sqlsrv_rows_affected($stmt);
    
    return array(
        'success' => true,
        'rows_affected' => $rows_affected
    );
}

/**
 * XSS 방어 - HTML 특수문자 이스케이프
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * 연결 종료 함수
 */
function closeConnection() {
    global $conn;
    if ($conn) {
        sqlsrv_close($conn);
    }
}
?>