<artifact identifier="myshop-login-page-updated" type="application/vnd.ant.code" language="php" title="login.php - 로그인 페이지">
<?php
/**
 * MyShop - 로그인 페이지
 */

require_once 'config/database.php';
require_once 'includes/session.php';

// 이미 로그인한 경우 메인으로 이동
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$success_message = '';

// 타임아웃 메시지
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $error_message = '세션이 만료되었습니다. 다시 로그인해주세요.';
}

// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_code = trim($_POST['user_code'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // 입력 검증 - 사용자 ID는 필수
    if (empty($user_code)) {
        $error_message = '사용자 ID를 입력해주세요.';
    } else {
        // 사용자 조회
        $query = "SELECT user_code, user_name, email, mobile, password, is_active 
                  FROM users 
                  WHERE user_code = ?";
        
        $result = fetchOne($query, array($user_code));
        
        if ($result['success'] && $result['data']) {
            $user = $result['data'];
            
            // 활성화 여부 확인
            if ($user['is_active'] != 1) {
                $error_message = '비활성화된 계정입니다. 관리자에게 문의하세요.';
            } else {
                $db_password = $user['password'];
                $login_allowed = false;
                
                // 비밀번호 검증 로직
                // 1. DB의 비밀번호가 NULL이거나 빈 문자열인 경우
                if ($db_password === null || trim($db_password) === '') {
                    // 사용자가 비밀번호를 입력하지 않은 경우 로그인 허용
                    if (empty($password)) {
                        $login_allowed = true;
                    } else {
                        $error_message = '이 계정은 비밀번호가 설정되어 있지 않습니다. 비밀번호 없이 로그인하세요.';
                    }
                }
                // 2. DB에 비밀번호가 설정되어 있는 경우
                else {
                    // 사용자가 비밀번호를 입력하지 않은 경우
                    if (empty($password)) {
                        $error_message = '비밀번호를 입력해주세요.';
                    }

                    // 암호화된 비밀번호와 비교
                    /*
					if (password_verify($password, $db_password)) {
						// 로그인 성공 (DB에 password가 암호화된 상태인 경우)
						$login_allowed = true;
					} else
					*/
					if ($password === $db_password) {
						// 로그인 성공 (DB에 password가 암호화되지 않은 상태인 경우)
                        $login_allowed = true;
					} else {
                        $error_message = '사용자 Code 또는 비밀번호가 올바르지 않습니다.';
                    }
                }
                
                // 로그인 허용된 경우
                if ($login_allowed) {
                    // 로그인 성공
                    setLoginSession($user);
                    
                    // 마지막 로그인 시간 업데이트
                    $updateQuery = "UPDATE users SET last_login = GETDATE() WHERE user_code = ?";
                    executeNonQuery($updateQuery, array($user_code));
                    
                    // 메인 페이지로 이동
                    header('Location: index.php');
                    exit;
                }
            }
        } else {
            $error_message = '사용자 ID 또는 비밀번호가 올바르지 않습니다.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 - MyShop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background-color: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 12px;
        }
        
        .sample-info {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 12px 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .sample-info strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
        
        .password-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🏪 MyShop</h1>
            <p>상품 재고 및 거래처 관리 시스템</p>
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
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="user_code">사용자 Code</label>
                <input 
                    type="text" 
                    id="user_code" 
                    name="user_code" 
                    placeholder="3자리 숫자 (예: 001)" 
                    maxlength="3"
                    value="<?php echo isset($_POST['user_code']) ? escape($_POST['user_code']) : ''; ?>"
                    required
                    autofocus
                >
            </div>
            
            <div class="form-group">
                <label for="password">비밀번호</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="비밀번호를 입력하세요"
                >
                <div class="password-hint">
                    * 비밀번호가 설정되지 않은 계정은 비밀번호 없이 로그인 가능
                </div>
            </div>
            
            <button type="submit" class="btn-login">로그인</button>
        </form>
       
        <div class="login-footer">
            &copy; 2025 MyShop. All rights reserved.
        </div>
    </div>
</body>
</html>