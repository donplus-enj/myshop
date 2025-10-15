</div><!-- .container 닫기 -->
    
    <!-- 푸터 -->
    <footer style="background-color: #f8f9fa; padding: 20px 0; margin-top: 50px; border-top: 1px solid #dee2e6;">
        <div class="container">
            <div style="text-align: center; color: #6c757d; font-size: 14px;">
                <p style="margin: 0;">
                    &copy; 2025 MyShop. All rights reserved. | 재고관리 및 거래처 관리 시스템
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px;">
                    Version 1.0.0 | 
                    현재 시간: <?php echo date('Y-m-d H:i:s'); ?> |
                    사용자: <?php 
                        if (isLoggedIn()) {
                            $user = getLoginUser();
                            echo escape($user['user_name'] ?? '알 수 없음');
                        }
                    ?>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript 파일 로드 -->
    <script src="<?php echo $base_path; ?>assets/js/script.js"></script>
    
    <!-- 페이지별 추가 스크립트 -->
    <?php if (isset($extra_scripts)): ?>
        <?php foreach ($extra_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- 인라인 스크립트 (페이지에서 정의한 경우) -->
    <?php if (isset($inline_script)): ?>
        <script>
            <?php echo $inline_script; ?>
        </script>
    <?php endif; ?>
    
</body>
</html>
