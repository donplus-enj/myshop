@echo off
chcp 65001 >nul
echo ====================================
echo MyShop 프로젝트 파일 생성 스크립트
echo ====================================
echo.

REM 현재 위치 확인
echo 현재 위치: %CD%
echo.

REM src 폴더 생성
echo [1/4] 폴더 구조 생성 중...
if not exist "src" mkdir "src"
cd src

REM 하위 폴더 생성
mkdir "config" 2>nul
mkdir "includes" 2>nul
mkdir "assets" 2>nul
mkdir "assets\css" 2>nul
mkdir "assets\js" 2>nul
mkdir "customers" 2>nul
mkdir "products" 2>nul
mkdir "transactions" 2>nul

echo    ✓ 폴더 생성 완료
echo.

REM 빈 파일 생성
echo [2/4] 빈 파일 생성 중...

REM config 폴더
echo. > "config\database.php"

REM includes 폴더
echo. > "includes\session.php"
echo. > "includes\header.php"
echo. > "includes\footer.php"

REM assets 폴더
echo. > "assets\css\style.css"
echo. > "assets\js\script.js"

REM 루트 파일
echo. > "login.php"
echo. > "logout.php"
echo. > "index.php"

REM customers 폴더
echo. > "customers\list.php"
echo. > "customers\add.php"
echo. > "customers\edit.php"
echo. > "customers\delete.php"
echo. > "customers\view.php"

REM products 폴더
echo. > "products\list.php"
echo. > "products\add.php"
echo. > "products\edit.php"
echo. > "products\delete.php"
echo. > "products\view.php"

REM transactions 폴더
echo. > "transactions\in_out.php"
echo. > "transactions\history.php"
echo. > "transactions\detail.php"

echo    ✓ 23개 파일 생성 완료
echo.

REM 파일 목록 생성
echo [3/4] 파일 목록 생성 중...

(
echo # MyShop 파일 생성 완료
echo.
echo ## 생성된 파일 목록
echo.
echo ### config/
echo - database.php
echo.
echo ### includes/
echo - session.php
echo - header.php
echo - footer.php
echo.
echo ### assets/css/
echo - style.css
echo.
echo ### assets/js/
echo - script.js
echo.
echo ### 루트 ^(src/^)
echo - login.php
echo - logout.php
echo - index.php
echo.
echo ### customers/
echo - list.php
echo - add.php
echo - edit.php
echo - delete.php
echo - view.php
echo.
echo ### products/
echo - list.php
echo - add.php
echo - edit.php
echo - delete.php
echo - view.php
echo.
echo ### transactions/
echo - in_out.php
echo - history.php
echo - detail.php
echo.
echo ## 다음 단계
echo.
echo 1. Claude AI가 제공한 각 파일의 코드를 복사하여 붙여넣기
echo 2. database.php 파일에서 DB 연결 정보 수정
echo 3. XAMPP 실행 및 데이터베이스 생성
echo 4. 브라우저에서 http://localhost/myshop/src/login.php 접속
echo.
echo ## 파일별 코드 복사 순서
echo.
echo 1. config/database.php
echo 2. includes/session.php
echo 3. includes/header.php
echo 4. includes/footer.php
echo 5. assets/css/style.css
echo 6. assets/js/script.js
echo 7. login.php
echo 8. logout.php
echo 9. index.php
echo 10. customers/list.php
echo 11. customers/add.php
echo 12. customers/edit.php
echo 13. customers/delete.php
echo 14. products/list.php
echo 15. products/add.php
echo 16. products/edit.php
echo 17. products/delete.php
echo 18. transactions/in_out.php
echo 19. transactions/history.php
echo 20. transactions/detail.php
) > "FILE_LIST.md"

echo    ✓ FILE_LIST.md 생성 완료
echo.

REM 트리 구조 출력
echo [4/4] 폴더 구조 확인
echo.
tree /F /A
echo.

echo ====================================
echo 파일 생성 완료!
echo ====================================
echo.
echo 📁 생성 위치: %CD%
echo 📝 총 23개 파일 생성됨
echo.
echo 다음 단계:
echo 1. 각 파일을 텍스트 에디터로 열기
echo 2. Claude AI가 제공한 코드 복사-붙여넣기
echo 3. database.php에서 DB 정보 수정
echo    - DB_PASSWORD를 실제 비밀번호로 변경
echo.
echo FILE_LIST.md 파일을 참고하세요!
echo.
pause