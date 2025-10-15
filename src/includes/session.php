<?php
/**
 * MyShop - 세션 관리
 * 로그인 체크 및 세션 유지
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 로그인 여부 확인
 */
function isLoggedIn() {
    return isset($_SESSION['user_code']) && !empty($_SESSION['user_code']);
}

/**
 * 로그인 필수 체크 (로그인하지 않으면 login.php로 리다이렉트)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * 로그인 사용자 정보 가져오기
 */
function getLoginUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return array(
        'user_code' => $_SESSION['user_code'],
        'user_name' => $_SESSION['user_name'],
        'email' => $_SESSION['email'],
        'mobile' => $_SESSION['mobile']
    );
}

/**
 * 세션에 사용자 정보 저장
 */
function setLoginSession($user) {
    $_SESSION['user_code'] = $user['user_code'];
    $_SESSION['user_name'] = $user['user_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['mobile'] = $user['mobile'];
    $_SESSION['last_activity'] = time();
}

/**
 * 로그아웃 (세션 파괴)
 */
function logout() {
    session_unset();
    session_destroy();
}

/**
 * 세션 완전 파괴 (쿠키 포함)
 */
function destroySession() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();
}

/**
 * 세션 타임아웃 체크 (30분)
 */
function checkSessionTimeout() {
    $timeout = 1800; // 30분 (초 단위)
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        logout();
        header('Location: login.php?timeout=1');
        exit;
    }
    
    $_SESSION['last_activity'] = time();
}

// 로그인한 페이지에서는 자동으로 타임아웃 체크
if (isLoggedIn()) {
    checkSessionTimeout();
}
?>