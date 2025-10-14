# MyShop 데이터베이스

## 스키마 파일
- `schema.sql`: MSSQL 데이터베이스 생성 스크립트

## 테이블 구조
1. **users** - 사용자 (3자리 코드)
2. **customers** - 거래처 (4자리 코드)
3. **products** - 상품 (4자리 코드)
4. **transactions** - 거래내역
5. **transaction_items** - 거래 상세

## 실행 방법

### SQL Server Management Studio (SSMS)
1. SSMS 실행
2. 새 쿼리 창 열기
3. schema.sql 파일 열기 (Ctrl+O)
4. F5 키로 실행

### sqlcmd (명령줄)
```bash
sqlcmd -S localhost -U sa -P YourPassword -i schema.sql
```

## 샘플 데이터
스크립트에 포함된 샘플 데이터:
- 사용자: 3명 (001, 002, 003)
- 거래처: 4개 (0001~0004)
- 상품: 8개 (0001~0008)
- 거래내역: 3건

## 초기 로그인
- **사용자코드**: 001
- **비밀번호**: admin123

## 주요 기능
- 자동 트리거 (재고 증감, 수정일시 갱신)
- 저장 프로시저 (코드 자동생성, 중복 체크)
- 뷰 (거래내역 상세, 재고 현황)

## 특징
- 음수 재고 허용
- CASCADE DELETE 설정
- CHECK 제약조건 포함
- 트랜잭션 안전성 보장

## 테이블 관계
```
users (1) → (N) transactions
customers (1) → (N) transactions
transactions (1) → (N) transaction_items
products (1) → (N) transaction_items
```