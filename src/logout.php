<artifact identifier="myshop-logout-page" type="application/vnd.ant.code" language="php" title="logout.php - 로그아웃">
<?php
/**
 * MyShop - 로그아웃 처리
 */

require_once 'includes/session.php';

// 세션 종료
destroySession();
// 로그인 페이지로 리다이렉션

header('Location: login.php');
exit;
?>
</artifact>