/**
 * MyShop - 공통 JavaScript
 */

// 숫자 포맷 (천단위 콤마)
function numberFormat(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// 숫자만 입력 허용
function onlyNumber(event) {
    const charCode = (event.which) ? event.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
        event.preventDefault();
        return false;
    }
    return true;
}

// 가격 입력 필드 포맷팅
function formatPrice(input) {
    let value = input.value.replace(/,/g, '');
    if (value && !isNaN(value)) {
        input.value = numberFormat(value);
    }
}

// 확인 다이얼로그
function confirmDelete(message) {
    return confirm(message || '정말 삭제하시겠습니까?');
}

// 페이지 로드 완료 시
document.addEventListener('DOMContentLoaded', function() {
    console.log('MyShop 시스템 로드 완료');
});