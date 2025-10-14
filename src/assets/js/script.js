/**
 * MyShop - 공통 JavaScript
 * 재고관리 및 거래처 관리 시스템
 */

// ============================================
// 1. 전역 변수 및 설정
// ============================================
const MyShop = {
    version: '1.0.0',
    apiTimeout: 10000, // 10초
    sessionCheckInterval: 60000, // 1분마다 세션 체크
};

// ============================================
// 2. 숫자 및 통화 포맷팅
// ============================================

/**
 * 숫자를 천단위 콤마 형식으로 변환
 * @param {number|string} num - 변환할 숫자
 * @returns {string} 포맷된 숫자 문자열
 */
function numberFormat(num) {
    if (num === null || num === undefined || num === '') {
        return '0';
    }
    
    const number = typeof num === 'string' ? parseFloat(num.replace(/,/g, '')) : num;
    
    if (isNaN(number)) {
        return '0';
    }
    
    return number.toLocaleString('ko-KR');
}

/**
 * 통화 형식으로 변환 (원화)
 * @param {number|string} num - 변환할 숫자
 * @returns {string} 포맷된 통화 문자열
 */
function currencyFormat(num) {
    return numberFormat(num) + '원';
}

/**
 * 숫자에서 콤마 제거
 * @param {string} str - 콤마가 포함된 문자열
 * @returns {number} 숫자
 */
function removeComma(str) {
    if (typeof str === 'number') {
        return str;
    }
    return parseFloat(str.replace(/,/g, '')) || 0;
}

/**
 * 입력 필드에 자동으로 천단위 콤마 추가
 * @param {HTMLElement} input - 입력 요소
 */
function formatPriceInput(input) {
    let value = input.value.replace(/,/g, '');
    
    if (value && !isNaN(value)) {
        input.value = numberFormat(value);
    }
}

/**
 * 입력 필드에서 숫자만 입력 허용
 * @param {Event} event - 키보드 이벤트
 * @returns {boolean} 허용 여부
 */
function onlyNumber(event) {
    const charCode = event.which ? event.which : event.keyCode;
    
    // 숫자(0-9), 백스페이스, 탭, 화살표, 삭제 키 허용
    if (
        (charCode >= 48 && charCode <= 57) || // 0-9
        (charCode >= 96 && charCode <= 105) || // 숫자패드 0-9
        charCode === 8 || // 백스페이스
        charCode === 9 || // 탭
        charCode === 46 || // 삭제
        (charCode >= 37 && charCode <= 40) // 화살표
    ) {
        return true;
    }
    
    event.preventDefault();
    return false;
}

/**
 * 숫자와 마이너스(-) 기호만 입력 허용
 * @param {Event} event - 키보드 이벤트
 * @returns {boolean} 허용 여부
 */
function allowNegativeNumber(event) {
    const charCode = event.which ? event.which : event.keyCode;
    const input = event.target;
    const value = input.value;
    
    // 마이너스는 맨 앞에만 허용
    if (charCode === 45) { // - 기호
        if (value.indexOf('-') !== -1 || input.selectionStart !== 0) {
            event.preventDefault();
            return false;
        }
        return true;
    }
    
    return onlyNumber(event);
}

// ============================================
// 3. 날짜 관련 함수
// ============================================

/**
 * 날짜를 YYYY-MM-DD 형식으로 변환
 * @param {Date} date - 날짜 객체
 * @returns {string} 포맷된 날짜 문자열
 */
function formatDate(date) {
    if (!date) {
        date = new Date();
    }
    
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
}

/**
 * 오늘 날짜를 YYYY-MM-DD 형식으로 반환
 * @returns {string} 오늘 날짜
 */
function getTodayDate() {
    return formatDate(new Date());
}

/**
 * 날짜를 한글 형식으로 변환 (예: 2025년 10월 14일)
 * @param {string|Date} date - 날짜
 * @returns {string} 포맷된 날짜 문자열
 */
function formatDateKorean(date) {
    const dateObj = typeof date === 'string' ? new Date(date) : date;
    
    const year = dateObj.getFullYear();
    const month = dateObj.getMonth() + 1;
    const day = dateObj.getDate();
    
    return `${year}년 ${month}월 ${day}일`;
}

/**
 * 날짜 입력 필드 초기화 (오늘 날짜)
 * @param {string} elementId - 입력 요소 ID
 */
function initDateInput(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.value = getTodayDate();
    }
}

// ============================================
// 4. 폼 검증
// ============================================

/**
 * 필수 입력 필드 검증
 * @param {string} elementId - 입력 요소 ID
 * @param {string} fieldName - 필드 이름
 * @returns {boolean} 검증 결과
 */
function validateRequired(elementId, fieldName) {
    const element = document.getElementById(elementId);
    const value = element.value.trim();
    
    if (!value) {
        showError(`${fieldName}을(를) 입력해주세요.`);
        element.focus();
        return false;
    }
    
    return true;
}

/**
 * 이메일 형식 검증
 * @param {string} email - 이메일 주소
 * @returns {boolean} 검증 결과
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * 전화번호 형식 검증 (하이픈 포함/미포함 모두 허용)
 * @param {string} phone - 전화번호
 * @returns {boolean} 검증 결과
 */
function validatePhone(phone) {
    const regex = /^[0-9]{2,3}-?[0-9]{3,4}-?[0-9]{4}$/;
    return regex.test(phone);
}

/**
 * 사업자등록번호 형식 검증
 * @param {string} businessNumber - 사업자등록번호
 * @returns {boolean} 검증 결과
 */
function validateBusinessNumber(businessNumber) {
    const regex = /^[0-9]{3}-?[0-9]{2}-?[0-9]{5}$/;
    return regex.test(businessNumber);
}

// ============================================
// 5. 알림 및 확인 대화상자
// ============================================

/**
 * 성공 메시지 표시
 * @param {string} message - 메시지 내용
 */
function showSuccess(message) {
    showAlert(message, 'success');
}

/**
 * 에러 메시지 표시
 * @param {string} message - 메시지 내용
 */
function showError(message) {
    showAlert(message, 'error');
}

/**
 * 경고 메시지 표시
 * @param {string} message - 메시지 내용
 */
function showWarning(message) {
    showAlert(message, 'warning');
}

/**
 * 정보 메시지 표시
 * @param {string} message - 메시지 내용
 */
function showInfo(message) {
    showAlert(message, 'info');
}

/**
 * 알림 메시지 표시
 * @param {string} message - 메시지 내용
 * @param {string} type - 메시지 타입 (success, error, warning, info)
 */
function showAlert(message, type = 'info') {
    // 기존 알림 제거
    const existingAlert = document.querySelector('.alert-floating');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // 새 알림 생성
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-floating fade-in`;
    alert.innerHTML = message;
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        max-width: 500px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(alert);
    
    // 3초 후 자동 제거
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

/**
 * 확인 대화상자
 * @param {string} message - 메시지 내용
 * @returns {boolean} 확인 여부
 */
function confirmAction(message) {
    return confirm(message || '정말 진행하시겠습니까?');
}

/**
 * 삭제 확인 대화상자
 * @param {string} itemName - 삭제할 항목 이름
 * @returns {boolean} 확인 여부
 */
function confirmDelete(itemName) {
    const message = itemName 
        ? `'${itemName}'을(를) 정말 삭제하시겠습니까?`
        : '정말 삭제하시겠습니까?';
    
    return confirm(message + '\n\n이 작업은 되돌릴 수 없습니다.');
}

// ============================================
// 6. AJAX 요청
// ============================================

/**
 * AJAX GET 요청
 * @param {string} url - 요청 URL
 * @param {Function} successCallback - 성공 콜백
 * @param {Function} errorCallback - 에러 콜백
 */
function ajaxGet(url, successCallback, errorCallback) {
    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (successCallback) {
            successCallback(data);
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        if (errorCallback) {
            errorCallback(error);
        } else {
            showError('요청 처리 중 오류가 발생했습니다.');
        }
    });
}

/**
 * AJAX POST 요청
 * @param {string} url - 요청 URL
 * @param {Object} data - 전송 데이터
 * @param {Function} successCallback - 성공 콜백
 * @param {Function} errorCallback - 에러 콜백
 */
function ajaxPost(url, data, successCallback, errorCallback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (successCallback) {
            successCallback(data);
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        if (errorCallback) {
            errorCallback(error);
        } else {
            showError('요청 처리 중 오류가 발생했습니다.');
        }
    });
}

/**
 * 폼 데이터를 AJAX로 전송
 * @param {string} formId - 폼 요소 ID
 * @param {string} url - 요청 URL
 * @param {Function} successCallback - 성공 콜백
 */
function submitFormAjax(formId, url, successCallback) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    fetch(url, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (successCallback) {
                successCallback(data);
            }
        } else {
            showError(data.message || '처리 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Form Submit Error:', error);
        showError('폼 전송 중 오류가 발생했습니다.');
    });
}

// ============================================
// 7. 코드 중복 체크 (실시간)
// ============================================

/**
 * 거래처 코드 중복 체크
 * @param {string} customerCode - 거래처 코드
 * @param {Function} callback - 결과 콜백 (isDuplicate)
 */
function checkCustomerCode(customerCode, callback) {
    if (!customerCode || customerCode.length !== 4) {
        return;
    }
    
    ajaxGet(
        `customers/check_code.php?code=${customerCode}`,
        (data) => {
            if (callback) {
                callback(data.isDuplicate || false);
            }
        },
        () => {
            console.error('코드 중복 체크 실패');
        }
    );
}

/**
 * 상품 코드 중복 체크
 * @param {string} productCode - 상품 코드
 * @param {Function} callback - 결과 콜백 (isDuplicate)
 */
function checkProductCode(productCode, callback) {
    if (!productCode || productCode.length !== 4) {
        return;
    }
    
    ajaxGet(
        `products/check_code.php?code=${productCode}`,
        (data) => {
            if (callback) {
                callback(data.isDuplicate || false);
            }
        },
        () => {
            console.error('코드 중복 체크 실패');
        }
    );
}

/**
 * 입력 필드에 실시간 코드 중복 체크 연결
 * @param {string} inputId - 입력 요소 ID
 * @param {Function} checkFunction - 체크 함수
 */
function attachCodeCheck(inputId, checkFunction) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    let debounceTimer;
    
    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        
        debounceTimer = setTimeout(() => {
            const code = this.value.trim();
            
            if (code.length === 4) {
                checkFunction(code, (isDuplicate) => {
                    if (isDuplicate) {
                        input.classList.add('is-invalid');
                        input.classList.remove('is-valid');
                        showFeedback(inputId, '이미 사용 중인 코드입니다.', 'invalid');
                    } else {
                        input.classList.add('is-valid');
                        input.classList.remove('is-invalid');
                        showFeedback(inputId, '사용 가능한 코드입니다.', 'valid');
                    }
                });
            } else {
                input.classList.remove('is-valid', 'is-invalid');
                removeFeedback(inputId);
            }
        }, 500);
    });
}

/**
 * 입력 필드에 피드백 메시지 표시
 * @param {string} inputId - 입력 요소 ID
 * @param {string} message - 메시지
 * @param {string} type - 타입 (valid, invalid)
 */
function showFeedback(inputId, message, type) {
    removeFeedback(inputId);
    
    const input = document.getElementById(inputId);
    const feedback = document.createElement('div');
    feedback.className = `${type}-feedback`;
    feedback.id = `${inputId}-feedback`;
    feedback.textContent = message;
    
    input.parentNode.appendChild(feedback);
}

/**
 * 입력 필드의 피드백 메시지 제거
 * @param {string} inputId - 입력 요소 ID
 */
function removeFeedback(inputId) {
    const feedback = document.getElementById(`${inputId}-feedback`);
    if (feedback) {
        feedback.remove();
    }
}

// ============================================
// 8. 테이블 관련 함수
// ============================================

/**
 * 테이블 행 삭제
 * @param {HTMLElement} button - 삭제 버튼 요소
 */
function deleteTableRow(button) {
    const row = button.closest('tr');
    if (row) {
        row.remove();
        updateTableTotal();
    }
}

/**
 * 테이블에 새 행 추가
 * @param {string} tableId - 테이블 ID
 * @param {string} rowHtml - 행 HTML
 */
function addTableRow(tableId, rowHtml) {
    const tbody = document.querySelector(`#${tableId} tbody`);
    if (tbody) {
        tbody.insertAdjacentHTML('beforeend', rowHtml);
    }
}

/**
 * 테이블 합계 업데이트 (금액 컬럼)
 * @param {string} tableId - 테이블 ID
 * @param {string} totalElementId - 합계 표시 요소 ID
 */
function updateTableTotal(tableId = 'itemsTable', totalElementId = 'totalAmount') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    let total = 0;
    
    rows.forEach(row => {
        const amountCell = row.querySelector('.amount-cell');
        if (amountCell) {
            const amount = removeComma(amountCell.textContent);
            total += amount;
        }
    });
    
    const totalElement = document.getElementById(totalElementId);
    if (totalElement) {
        totalElement.textContent = currencyFormat(total);
    }
}

// ============================================
// 9. 이미지 미리보기
// ============================================

/**
 * 이미지 URL 미리보기
 * @param {string} inputId - URL 입력 요소 ID
 * @param {string} previewId - 미리보기 이미지 요소 ID
 */
function previewImageUrl(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    if (!input || !preview) return;
    
    input.addEventListener('input', function() {
        const url = this.value.trim();
        
        if (url) {
            preview.src = url;
            preview.style.display = 'block';
            
            // 이미지 로드 실패 시
            preview.onerror = function() {
                this.style.display = 'none';
                showWarning('이미지를 불러올 수 없습니다. URL을 확인해주세요.');
            };
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    });
}

// ============================================
// 10. 모달 관련 함수
// ============================================

/**
 * 모달 열기
 * @param {string} modalId - 모달 요소 ID
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * 모달 닫기
 * @param {string} modalId - 모달 요소 ID
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

/**
 * 모달 외부 클릭 시 닫기
 */
function initModalClose() {
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
}

// ============================================
// 11. 로딩 스피너
// ============================================

/**
 * 로딩 스피너 표시
 */
function showLoading() {
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 99999;
    `;
    
    overlay.innerHTML = '<div class="spinner spinner-lg"></div>';
    document.body.appendChild(overlay);
}

/**
 * 로딩 스피너 숨김
 */
function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// ============================================
// 12. 인쇄 기능
// ============================================

/**
 * 페이지 인쇄
 */
function printPage() {
    window.print();
}

/**
 * 특정 요소만 인쇄
 * @param {string} elementId - 인쇄할 요소 ID
 */
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>인쇄</title>');
    printWindow.document.write('<link rel="stylesheet" href="assets/css/style.css">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
}

// ============================================
// 13. 세션 관리
// ============================================

/**
 * 세션 유지 체크 (주기적으로 호출)
 */
function keepSessionAlive() {
    fetch('includes/keep_alive.php', {
        method: 'GET',
        cache: 'no-cache',
    }).catch(error => {
        console.error('Session keep-alive failed:', error);
    });
}

/**
 * 세션 타임아웃 경고
 */
function startSessionTimer() {
    // 25분 후 경고 (세션 타임아웃 30분 기준)
    setTimeout(() => {
        if (confirm('5분 후 세션이 만료됩니다. 계속 작업하시겠습니까?')) {
            keepSessionAlive();
            startSessionTimer(); // 타이머 재시작
        }
    }, 25 * 60 * 1000);
}

// ============================================
// 14. 로컬 스토리지 헬퍼
// ============================================

/**
 * 로컬 스토리지에 저장
 * @param {string} key - 키
 * @param {*} value - 값
 */
function saveToStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        console.error('LocalStorage save failed:', e);
    }
}

/**
 * 로컬 스토리지에서 가져오기
 * @param {string} key - 키
 * @returns {*} 저장된 값
 */
function getFromStorage(key) {
    try {
        const value = localStorage.getItem(key);
        return value ? JSON.parse(value) : null;
    } catch (e) {
        console.error('LocalStorage get failed:', e);
        return null;
    }
}

/**
 * 로컬 스토리지에서 삭제
 * @param {string} key - 키
 */
function removeFromStorage(key) {
    try {
        localStorage.removeItem(key);
    } catch (e) {
        console.error('LocalStorage remove failed:', e);
    }
}

// ============================================
// 15. 유틸리티 함수
// ============================================

/**
 * 디바운스 함수 생성
 * @param {Function} func - 실행할 함수
 * @param {number} wait - 대기 시간 (ms)
 * @returns {Function} 디바운스된 함수
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * 쿼리 문자열 파싱
 * @param {string} url - URL 문자열
 * @returns {Object} 파라미터 객체
 */
function parseQueryString(url = window.location.search) {
    const params = {};
    const queryString = url.split('?')[1];
    
    if (queryString) {
        queryString.split('&').forEach(param => {
            const [key, value] = param.split('=');
            params[decodeURIComponent(key)] = decodeURIComponent(value || '');
        });
    }
    
    return params;
}

/**
 * 요소 표시/숨김 토글
 * @param {string} elementId - 요소 ID
 */
function toggleElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

/**
 * 스크롤을 맨 위로
 */
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

/**
 * 특정 요소로 스크롤
 * @param {string} elementId - 요소 ID
 */
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// ============================================
// 16. 초기화
// ============================================

/**
 * DOM 로드 완료 시 실행
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log(`MyShop v${MyShop.version} 로드 완료`);
    
    // 모달 외부 클릭 이벤트 초기화
    initModalClose();
    
    // 알림 자동 닫기
    initAutoCloseAlerts();
    
    // 로그인 페이지가 아닌 경우 세션 타이머 시작
    if (!window.location.pathname.includes('login.php')) {
        startSessionTimer();
        
        // 1분마다 세션 유지
        setInterval(keepSessionAlive, MyShop.sessionCheckInterval);
    }
    
    // 가격 입력 필드 자동 포맷팅
    document.querySelectorAll('.price-input').forEach(input => {
        input.addEventListener('blur', function() {
            formatPriceInput(this);
        });
    });
    
    // 숫자만 입력 필드
    document.querySelectorAll('.number-only').forEach(input => {
        input.addEventListener('keypress', onlyNumber);
    });
    
    console.log('모든 초기화 완료');
});

/**
 * 알림 자동 닫기 초기화
 */
function initAutoCloseAlerts() {
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // 닫기 버튼 이벤트
    document.querySelectorAll('.alert-close').forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(() => alert.remove(), 300);
        });
    });
}

// ============================================
// 17. 거래 유형 관련 함수
// ============================================

/**
 * 거래 유형 한글명 반환
 * @param {string} type - 거래 유형 코드
 * @returns {string} 한글 거래 유형명
 */
function getTransactionTypeName(type) {
    const types = {
        'IN': '입고',
        'OUT': '출고',
        'IN_RETURN': '입고반품',
        'OUT_RETURN': '출고반품',
        'RECEIVE': '수금',
        'PAYMENT': '지급'
    };
    return types[type] || type;
}

/**
 * 거래 유형에 따른 배지 클래스 반환
 * @param {string} type - 거래 유형 코드
 * @returns {string} 배지 클래스명
 */
function getTransactionBadgeClass(type) {
    const classes = {
        'IN': 'badge-success',
        'OUT': 'badge-primary',
        'IN_RETURN': 'badge-warning',
        'OUT_RETURN': 'badge-warning',
        'RECEIVE': 'badge-info',
        'PAYMENT': 'badge-danger'
    };
    return classes[type] || 'badge-secondary';
}

// ============================================
// 18. 입출고 관리 전용 함수
// ============================================

/**
 * 상품 행 추가 (입출고 화면용)
 * @param {Object} product - 상품 정보
 */
function addProductRow(product) {
    const tableBody = document.querySelector('#itemsTable tbody');
    if (!tableBody) return;
    
    const rowCount = tableBody.querySelectorAll('tr').length + 1;
    
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="text-center">${rowCount}</td>
        <td>
            <input type="hidden" name="product_code[]" value="${product.code}">
            ${product.name}
        </td>
        <td class="text-center">${product.spec || '-'}</td>
        <td>
            <input type="number" 
                   class="form-control form-control-sm quantity-input" 
                   name="quantity[]" 
                   value="1" 
                   min="1" 
                   required 
                   onchange="calculateAmount(this)">
        </td>
        <td>
            <input type="text" 
                   class="form-control form-control-sm text-right unit-price-input" 
                   name="unit_price[]" 
                   value="${numberFormat(product.standard_price)}" 
                   onblur="formatPriceInput(this); calculateAmount(this)">
        </td>
        <td class="text-right amount-cell">
            ${currencyFormat(product.standard_price)}
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteTableRow(this)">
                삭제
            </button>
        </td>
    `;
    
    tableBody.appendChild(row);
    updateTableTotal();
}

/**
 * 금액 계산 (수량 × 단가)
 * @param {HTMLElement} element - 변경된 입력 요소
 */
function calculateAmount(element) {
    const row = element.closest('tr');
    const quantityInput = row.querySelector('.quantity-input');
    const unitPriceInput = row.querySelector('.unit-price-input');
    const amountCell = row.querySelector('.amount-cell');
    
    const quantity = parseInt(quantityInput.value) || 0;
    const unitPrice = removeComma(unitPriceInput.value);
    const amount = quantity * unitPrice;
    
    amountCell.textContent = currencyFormat(amount);
    updateTableTotal();
}

/**
 * 금액으로 단가 역계산
 * @param {HTMLElement} amountInput - 금액 입력 요소
 */
function calculateUnitPrice(amountInput) {
    const row = amountInput.closest('tr');
    const quantityInput = row.querySelector('.quantity-input');
    const unitPriceInput = row.querySelector('.unit-price-input');
    
    const quantity = parseInt(quantityInput.value) || 1;
    const amount = removeComma(amountInput.value);
    const unitPrice = Math.round(amount / quantity);
    
    unitPriceInput.value = numberFormat(unitPrice);
}

// ============================================
// 19. 검색 필터링
// ============================================

/**
 * 테이블 검색 필터링
 * @param {string} inputId - 검색 입력 요소 ID
 * @param {string} tableId - 테이블 ID
 * @param {number} columnIndex - 검색할 컬럼 인덱스
 */
function filterTable(inputId, tableId, columnIndex = 1) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    const filter = input.value.toLowerCase();
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const cell = row.cells[columnIndex];
        if (cell) {
            const text = cell.textContent || cell.innerText;
            row.style.display = text.toLowerCase().includes(filter) ? '' : 'none';
        }
    });
}

/**
 * 검색 입력에 실시간 필터링 연결
 * @param {string} inputId - 검색 입력 요소 ID
 * @param {string} tableId - 테이블 ID
 * @param {number} columnIndex - 검색할 컬럼 인덱스
 */
function attachTableFilter(inputId, tableId, columnIndex = 1) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.addEventListener('input', debounce(function() {
        filterTable(inputId, tableId, columnIndex);
    }, 300));
}

// ============================================
// 20. 데이터 내보내기
// ============================================

/**
 * 테이블을 CSV로 내보내기
 * @param {string} tableId - 테이블 ID
 * @param {string} filename - 파일명
 */
function exportTableToCSV(tableId, filename = 'data.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        
        cols.forEach(col => {
            let data = col.textContent.trim();
            // 콤마가 포함된 경우 따옴표로 감싸기
            if (data.includes(',')) {
                data = `"${data}"`;
            }
            rowData.push(data);
        });
        
        csv.push(rowData.join(','));
    });
    
    // BOM 추가 (엑셀에서 한글 깨짐 방지)
    const csvContent = '\uFEFF' + csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    
    // 다운로드
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ============================================
// 21. 차트/그래프 헬퍼 (향후 확장용)
// ============================================

/**
 * 배열에서 최대값 찾기
 * @param {Array} arr - 숫자 배열
 * @returns {number} 최대값
 */
function getMaxValue(arr) {
    return Math.max(...arr);
}

/**
 * 배열에서 최소값 찾기
 * @param {Array} arr - 숫자 배열
 * @returns {number} 최소값
 */
function getMinValue(arr) {
    return Math.min(...arr);
}

/**
 * 배열의 합계
 * @param {Array} arr - 숫자 배열
 * @returns {number} 합계
 */
function getSum(arr) {
    return arr.reduce((sum, val) => sum + val, 0);
}

/**
 * 배열의 평균
 * @param {Array} arr - 숫자 배열
 * @returns {number} 평균
 */
function getAverage(arr) {
    return arr.length > 0 ? getSum(arr) / arr.length : 0;
}

// ============================================
// 22. 복사 기능
// ============================================

/**
 * 텍스트 클립보드에 복사
 * @param {string} text - 복사할 텍스트
 */
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
            .then(() => {
                showSuccess('클립보드에 복사되었습니다.');
            })
            .catch(err => {
                console.error('복사 실패:', err);
                showError('복사에 실패했습니다.');
            });
    } else {
        // 구형 브라우저 대응
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showSuccess('클립보드에 복사되었습니다.');
        } catch (err) {
            console.error('복사 실패:', err);
            showError('복사에 실패했습니다.');
        }
        
        document.body.removeChild(textarea);
    }
}

// ============================================
// 23. 폼 리셋 및 초기화
// ============================================

/**
 * 폼 초기화
 * @param {string} formId - 폼 요소 ID
 */
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        
        // 검증 클래스 제거
        form.querySelectorAll('.is-valid, .is-invalid').forEach(element => {
            element.classList.remove('is-valid', 'is-invalid');
        });
        
        // 피드백 메시지 제거
        form.querySelectorAll('.valid-feedback, .invalid-feedback').forEach(element => {
            element.remove();
        });
    }
}

/**
 * 동적 테이블 초기화
 * @param {string} tableId - 테이블 ID
 */
function clearTable(tableId) {
    const tbody = document.querySelector(`#${tableId} tbody`);
    if (tbody) {
        tbody.innerHTML = '';
        updateTableTotal();
    }
}

// ============================================
// 24. 브라우저 호환성 체크
// ============================================

/**
 * 로컬 스토리지 지원 여부
 * @returns {boolean} 지원 여부
 */
function isLocalStorageSupported() {
    try {
        const test = '__test__';
        localStorage.setItem(test, test);
        localStorage.removeItem(test);
        return true;
    } catch (e) {
        return false;
    }
}

/**
 * Fetch API 지원 여부
 * @returns {boolean} 지원 여부
 */
function isFetchSupported() {
    return typeof fetch !== 'undefined';
}

// ============================================
// 25. 디버깅 헬퍼
// ============================================

/**
 * 개발 모드 로그
 * @param {*} message - 로그 메시지
 */
function devLog(message) {
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log('[MyShop Dev]', message);
    }
}

/**
 * 객체를 보기 좋게 출력
 * @param {Object} obj - 출력할 객체
 */
function devPrint(obj) {
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.table(obj);
    }
}

// ============================================
// 26. 전역 객체로 내보내기
// ============================================

// MyShop 네임스페이스에 주요 함수들 추가
MyShop.utils = {
    // 숫자/통화
    numberFormat,
    currencyFormat,
    removeComma,
    formatPriceInput,
    onlyNumber,
    allowNegativeNumber,
    
    // 날짜
    formatDate,
    getTodayDate,
    formatDateKorean,
    initDateInput,
    
    // 검증
    validateRequired,
    validateEmail,
    validatePhone,
    validateBusinessNumber,
    
    // 알림
    showSuccess,
    showError,
    showWarning,
    showInfo,
    confirmAction,
    confirmDelete,
    
    // AJAX
    ajaxGet,
    ajaxPost,
    submitFormAjax,
    
    // 코드 체크
    checkCustomerCode,
    checkProductCode,
    attachCodeCheck,
    
    // 테이블
    deleteTableRow,
    addTableRow,
    updateTableTotal,
    filterTable,
    exportTableToCSV,
    
    // 이미지
    previewImageUrl,
    
    // 모달
    openModal,
    closeModal,
    
    // 로딩
    showLoading,
    hideLoading,
    
    // 인쇄
    printPage,
    printElement,
    
    // 세션
    keepSessionAlive,
    
    // 스토리지
    saveToStorage,
    getFromStorage,
    removeFromStorage,
    
    // 거래
    getTransactionTypeName,
    getTransactionBadgeClass,
    addProductRow,
    calculateAmount,
    
    // 유틸리티
    debounce,
    parseQueryString,
    toggleElement,
    scrollToTop,
    scrollToElement,
    copyToClipboard,
    resetForm,
    clearTable,
    
    // 디버깅
    devLog,
    devPrint
};

// 전역 스코프로 내보내기 (하위 호환성)
window.MyShop = MyShop;

// 콘솔에 버전 정보 출력
console.log(`%c MyShop v${MyShop.version} `, 'background: #667eea; color: white; padding: 5px 10px; border-radius: 3px;');
console.log('MyShop.utils에서 유틸리티 함수를 사용할 수 있습니다.');

// ============================================
// END OF SCRIPT
// ============================================
