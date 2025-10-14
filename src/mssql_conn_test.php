<?php
/**
 * MyShop - MSSQL 연결 테스트 페이지
 * 파일명: mssql_conn_test.php
 * 위치: src/
 */

// 에러 표시 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 페이지 시작 시간
$start_time = microtime(true);

// config/database.php 파일 로드 시도 (연결 테스트를 위해 주석 처리)
$config_loaded = false;
$config_path = '';
$connection_error = null;
$connection_success = false;

// 가능한 경로들 시도
$possible_paths = [
    __DIR__ . '/config/database.php',
    __DIR__ . '/../config/database.php',
    dirname(__DIR__) . '/config/database.php'
];

// 설정 파일에서 변수 추출
$db_server = null;
$db_port = null;
$db_database = null;
$db_username = null;
$db_password = null;
$db_charset = null;

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        // 파일 내용 읽기 (연결하지 않고)
        $file_content = file_get_contents($path);
        
        // 설정값 추출
        if (preg_match("/define\('DB_SERVER',\s*'([^']+)'\)/", $file_content, $matches)) {
            $db_server = $matches[1];
        }
        if (preg_match("/define\('DB_PORT',\s*'([^']+)'\)/", $file_content, $matches)) {
            $db_port = $matches[1];
        }
        if (preg_match("/define\('DB_DATABASE',\s*'([^']+)'\)/", $file_content, $matches)) {
            $db_database = $matches[1];
        }
        if (preg_match("/define\('DB_USERNAME',\s*'([^']+)'\)/", $file_content, $matches)) {
            $db_username = $matches[1];
        }
        if (preg_match("/define\('DB_PASSWORD',\s*'([^']+)'\)/", $file_content, $matches)) {
            $db_password = $matches[1];
        }
        if (preg_match("/define\('DB_CHARSET',\s*'([^']+)'\)/", $file_content, $matches)) {
            $db_charset = $matches[1];
        }
        
        $config_loaded = true;
        $config_path = $path;
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSSQL 연결 테스트 - MyShop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .test-section {
            margin-bottom: 30px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .test-header {
            background: #f5f5f5;
            padding: 15px 20px;
            font-weight: bold;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }
        
        .test-body {
            padding: 20px;
        }
        
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .info-table th,
        .info-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-table th {
            background: #f8f9fa;
            font-weight: bold;
            width: 220px;
        }
        
        .info-table tr:last-child th,
        .info-table tr:last-child td {
            border-bottom: none;
        }
        
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            line-height: 1.6;
        }
        
        .alert.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert.warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .button:hover {
            background: #5568d3;
        }
        
        .button.danger {
            background: #dc3545;
        }
        
        .button.danger:hover {
            background: #c82333;
        }
        
        .error-detail {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            font-size: 13px;
        }
        
        .success-icon {
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }
        
        ul {
            margin-left: 20px;
            line-height: 1.8;
        }
        
        .footer-note {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 2px solid #e0e0e0;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 MSSQL 연결 테스트</h1>
            <p>MyShop 데이터베이스 연결 진단 도구 (SQLSRV)</p>
        </div>
        
        <div class="content">
            <!-- PHP 환경 체크 -->
            <div class="test-section">
                <div class="test-header">
                    <span>1️⃣ PHP 환경 체크</span>
                </div>
                <div class="test-body">
                    <table class="info-table">
                        <tr>
                            <th>PHP 버전</th>
                            <td>
                                <?php 
                                echo phpversion(); 
                                $php_ok = version_compare(PHP_VERSION, '7.4.0', '>=');
                                ?>
                                <?php if ($php_ok): ?>
                                    <span class="status success">✓ 권장</span>
                                <?php else: ?>
                                    <span class="status warning">⚠ PHP 7.4+ 권장</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>운영체제</th>
                            <td><?php echo PHP_OS; ?></td>
                        </tr>
                        <tr>
                            <th>서버 소프트웨어</th>
                            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                        </tr>
                        <tr>
                            <th>현재 시간</th>
                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- SQLSRV 드라이버 체크 -->
            <div class="test-section">
                <div class="test-header">
                    <span>2️⃣ SQLSRV 드라이버 체크</span>
                </div>
                <div class="test-body">
                    <?php
                    $sqlsrv_loaded = extension_loaded('sqlsrv');
                    $pdo_sqlsrv_loaded = extension_loaded('pdo_sqlsrv');
                    ?>
                    
                    <table class="info-table">
                        <tr>
                            <th>SQLSRV 확장</th>
                            <td>
                                <span class="status <?php echo $sqlsrv_loaded ? 'success' : 'error'; ?>">
                                    <?php echo $sqlsrv_loaded ? '✓ 설치됨' : '✗ 미설치'; ?>
                                </span>
                                <?php if ($sqlsrv_loaded): ?>
                                    <span style="color: #666; font-size: 12px; margin-left: 10px;">
                                        (버전: <?php echo phpversion('sqlsrv'); ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>PDO_SQLSRV 확장</th>
                            <td>
                                <span class="status <?php echo $pdo_sqlsrv_loaded ? 'success' : 'warning'; ?>">
                                    <?php echo $pdo_sqlsrv_loaded ? '✓ 설치됨' : '○ 선택사항'; ?>
                                </span>
                                <?php if ($pdo_sqlsrv_loaded): ?>
                                    <span style="color: #666; font-size: 12px; margin-left: 10px;">
                                        (버전: <?php echo phpversion('pdo_sqlsrv'); ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    
                    <?php if (!$sqlsrv_loaded): ?>
                        <div class="alert error">
                            <strong>⚠️ SQLSRV 드라이버가 설치되어 있지 않습니다!</strong><br><br>
                            <strong>해결 방법:</strong><br>
                            <ul>
                                <li><strong>1단계:</strong> Microsoft ODBC Driver 17 for SQL Server 다운로드 및 설치
                                    <br>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://learn.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server" target="_blank">다운로드 링크</a>
                                </li>
                                <li><strong>2단계:</strong> PHP SQLSRV 확장 다운로드
                                    <br>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server" target="_blank">다운로드 링크</a>
                                </li>
                                <li><strong>3단계:</strong> php.ini 파일에 다음 라인 추가:
                                    <div class="code-block" style="margin-top: 10px;">extension=php_sqlsrv_<?php echo substr(PHP_VERSION, 0, 3); ?>_ts.dll
extension=php_pdo_sqlsrv_<?php echo substr(PHP_VERSION, 0, 3); ?>_ts.dll</div>
                                </li>
                                <li><strong>4단계:</strong> Apache/웹서버 재시작</li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="alert success">
                            ✓ SQLSRV 드라이버가 정상적으로 설치되어 있습니다!
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Config 파일 체크 -->
            <div class="test-section">
                <div class="test-header">
                    <span>3️⃣ 설정 파일 체크</span>
                </div>
                <div class="test-body">
                    <?php if ($config_loaded): ?>
                        <div class="alert success">
                            ✓ 설정 파일을 찾았습니다: <br>
                            <code style="font-size: 12px;"><?php echo $config_path; ?></code>
                        </div>
                        
                        <table class="info-table">
                            <tr>
                                <th>DB_SERVER</th>
                                <td>
                                    <?php echo $db_server ? htmlspecialchars($db_server) : '<span class="status error">미정의</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>DB_PORT</th>
                                <td>
                                    <?php echo $db_port ? htmlspecialchars($db_port) : '<span class="status warning">기본값(1433)</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>DB_DATABASE</th>
                                <td>
                                    <?php echo $db_database ? htmlspecialchars($db_database) : '<span class="status error">미정의</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>DB_USERNAME</th>
                                <td>
                                    <?php echo $db_username ? htmlspecialchars($db_username) : '<span class="status error">미정의</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>DB_PASSWORD</th>
                                <td>
                                    <?php echo $db_password ? '••••••••' : '<span class="status error">미정의</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>DB_CHARSET</th>
                                <td>
                                    <?php echo $db_charset ? htmlspecialchars($db_charset) : '<span class="status warning">미정의</span>'; ?>
                                </td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <div class="alert error">
                            ✗ 설정 파일(config/database.php)을 찾을 수 없습니다.<br><br>
                            <strong>시도한 경로:</strong>
                            <ul>
                                <?php foreach ($possible_paths as $path): ?>
                                    <li><code><?php echo $path; ?></code></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 실제 연결 테스트 -->
            <div class="test-section">
                <div class="test-header">
                    <span>4️⃣ 데이터베이스 연결 테스트</span>
                </div>
                <div class="test-body">
                    <?php
                    if (!$config_loaded) {
                        echo '<div class="alert warning">⚠️ 설정 파일을 먼저 로드해야 합니다.</div>';
                    } elseif (!$sqlsrv_loaded) {
                        echo '<div class="alert warning">⚠️ SQLSRV 드라이버를 먼저 설치해야 합니다.</div>';
                    } else {
                        // 연결 정보 표시
                        echo '<h4>📌 연결 정보</h4>';
                        echo '<div class="code-block">';
                        echo 'Server: ' . htmlspecialchars($db_server) . '<br>';
                        echo 'Port: ' . htmlspecialchars($db_port) . '<br>';
                        echo 'Database: ' . htmlspecialchars($db_database) . '<br>';
                        echo 'Username: ' . htmlspecialchars($db_username) . '<br>';
                        echo 'CharacterSet: ' . htmlspecialchars($db_charset);
                        echo '</div>';
                        
                        // 연결 시도
                        $connectionInfo = array(
                            "Database" => $db_database,
                            "UID" => $db_username,
                            "PWD" => $db_password,
                            "CharacterSet" => $db_charset
                        );
                        
                        echo '<h4 style="margin-top: 20px;">📌 연결 시도 중...</h4>';
                        
                        $conn = @sqlsrv_connect($db_server, $connectionInfo);
                        
                        if ($conn === false) {
                            $errors = sqlsrv_errors();
                            echo '<div class="alert error">';
                            echo '<strong>✗ 연결 실패</strong><br><br>';
                            
                            if ($errors) {
                                echo '<strong>에러 세부사항:</strong><br>';
                                foreach ($errors as $error) {
                                    echo '<div class="error-detail">';
                                    echo '<strong>SQLSTATE:</strong> ' . htmlspecialchars($error['SQLSTATE']) . '<br>';
                                    echo '<strong>Code:</strong> ' . htmlspecialchars($error['code']) . '<br>';
                                    echo '<strong>Message:</strong> ' . htmlspecialchars($error['message']);
                                    echo '</div>';
                                }
                            }
                            
                            echo '</div>';
                            
                            // 해결 방법 제시
                            echo '<div class="alert warning">';
                            echo '<strong>💡 문제 해결 가이드:</strong><br>';
                            echo '<ul>';
                            echo '<li><strong>서버 연결 문제:</strong> SQL Server가 실행 중인지 확인 (192.168.1.100:1433)</li>';
                            echo '<li><strong>네트워크 문제:</strong> 방화벽에서 포트 1433 허용 확인</li>';
                            echo '<li><strong>인증 문제:</strong> 사용자 계정(' . htmlspecialchars($db_username) . ')과 비밀번호 확인</li>';
                            echo '<li><strong>데이터베이스 없음:</strong> \'' . htmlspecialchars($db_database) . '\' 데이터베이스 존재 확인</li>';
                            echo '<li><strong>SQL Server 설정:</strong> TCP/IP 프로토콜 활성화 확인 (SQL Server Configuration Manager)</li>';
                            echo '<li><strong>인증 모드:</strong> SQL Server 인증 모드 활성화 확인 (혼합 모드)</li>';
                            echo '</ul>';
                            echo '</div>';
                            
                        } else {
                            $connection_success = true;
                            echo '<div class="alert success">';
                            echo '<div class="success-icon">🎉</div>';
                            echo '<strong style="font-size: 18px;">✓ 연결 성공!</strong><br>';
                            echo '데이터베이스에 정상적으로 연결되었습니다.';
                            echo '</div>';
                            
                            // 서버 정보 조회
                            echo '<h4 style="margin-top: 20px;">📊 서버 정보</h4>';
                            
                            $query = "SELECT 
                                        @@VERSION as version,
                                        DB_NAME() as current_db,
                                        SYSTEM_USER as system_user,
                                        GETDATE() as server_time,
                                        @@SERVERNAME as server_name";
                            
                            $stmt = sqlsrv_query($conn, $query);
                            
                            if ($stmt) {
                                $info = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                                
                                echo '<table class="info-table">';
                                echo '<tr><th>서버 이름</th><td>' . htmlspecialchars($info['server_name']) . '</td></tr>';
                                echo '<tr><th>현재 데이터베이스</th><td>' . htmlspecialchars($info['current_db']) . '</td></tr>';
                                echo '<tr><th>시스템 사용자</th><td>' . htmlspecialchars($info['system_user']) . '</td></tr>';
                                echo '<tr><th>서버 시간</th><td>' . $info['server_time']->format('Y-m-d H:i:s') . '</td></tr>';
                                echo '<tr><th>SQL Server 버전</th><td style="font-size: 11px;">' . htmlspecialchars(substr($info['version'], 0, 200)) . '...</td></tr>';
                                echo '</table>';
                                
                                sqlsrv_free_stmt($stmt);
                            }
                            
                            // 테이블 목록 조회
                            echo '<h4 style="margin-top: 20px;">📋 데이터베이스 테이블</h4>';
                            
                            $tables_query = "SELECT TABLE_NAME 
                                           FROM INFORMATION_SCHEMA.TABLES 
                                           WHERE TABLE_TYPE = 'BASE TABLE' 
                                           ORDER BY TABLE_NAME";
                            
                            $tables_stmt = sqlsrv_query($conn, $tables_query);
                            
                            if ($tables_stmt) {
                                $tables = array();
                                while ($row = sqlsrv_fetch_array($tables_stmt, SQLSRV_FETCH_ASSOC)) {
                                    $tables[] = $row['TABLE_NAME'];
                                }
                                
                                echo '<div class="code-block">';
                                if (count($tables) > 0) {
                                    echo '<strong>총 ' . count($tables) . '개 테이블:</strong><br><br>';
                                    foreach ($tables as $table) {
                                        echo '• ' . htmlspecialchars($table) . '<br>';
                                    }
                                } else {
                                    echo '(테이블(들)이 존재하지 않거나, 엑세스할 권한이 부여되지 않았습니다)';
                                }
                                echo '</div>';
                                
                                sqlsrv_free_stmt($tables_stmt);
                            }
                            
                            // 간단한 쿼리 테스트
                            echo '<h4 style="margin-top: 20px;">🧪 쿼리 테스트</h4>';
                            
                            $test_query = "SELECT 1 as test_value, 'Hello MyShop!' as test_message";
                            $test_stmt = sqlsrv_query($conn, $test_query);
                            
                            if ($test_stmt) {
                                $test_result = sqlsrv_fetch_array($test_stmt, SQLSRV_FETCH_ASSOC);
                                echo '<div class="alert success">';
                                echo '✓ SELECT 쿼리 실행 성공<br>';
                                echo '결과: ' . htmlspecialchars($test_result['test_message']);
                                echo '</div>';
                                sqlsrv_free_stmt($test_stmt);
                            }
                            
                            // 연결 종료
                            sqlsrv_close($conn);
                        }
                    }
                    
                    $end_time = microtime(true);
                    $execution_time = round(($end_time - $start_time) * 1000, 2);
                    ?>
                    
                    <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; text-align: center; color: #666; font-size: 13px;">
                        ⏱️ 테스트 실행 시간: <?php echo $execution_time; ?>ms
                    </div>
                </div>
            </div>

            <!-- 권장 사항 -->
            <?php if ($connection_success): ?>
            <div class="test-section">
                <div class="test-header">
                    <span>✅ 다음 단계</span>
                </div>
                <div class="test-body">
                    <div class="alert success">
                        <strong>연결이 정상적으로 작동합니다!</strong><br><br>
                        이제 다음 작업을 진행할 수 있습니다:
                    </div>
                    <ul>
                        <li>MyShop 애플리케이션 페이지 접속</li>
                        <li>로그인 및 기능 테스트</li>
                        <li><strong style="color: red;">보안: 이 테스트 파일을 삭제하거나 접근 제한</strong></li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="javascript:location.reload()" class="button">🔄 다시 테스트</a>
                <?php if ($connection_success): ?>
                    <a href="index.php" class="button" style="margin-left: 10px; background: #28a745;">🏠 메인으로</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer-note">
            <strong>⚠️ 보안 경고</strong><br>
            이 파일은 테스트 용도로만 사용하세요. 테스트 완료 후 반드시 삭제하시기 바랍니다.<br>
            민감한 데이터베이스 정보가 노출될 수 있습니다.
        </div>
    </div>
</body>
</html>