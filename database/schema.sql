-- ============================================
-- MyShop 재고관리 시스템
-- MS SQL Server 데이터베이스 생성 스크립트
-- ============================================

-- 데이터베이스 생성
IF EXISTS (SELECT name FROM sys.databases WHERE name = 'myshop')
BEGIN
    ALTER DATABASE myshop SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
    DROP DATABASE myshop;
END
GO

CREATE DATABASE myshop;
GO

USE myshop;
GO

-- ============================================
-- 1. 사용자 테이블 (users)
-- ============================================
CREATE TABLE users (
    user_code CHAR(3) NOT NULL,
    user_name NVARCHAR(50) NOT NULL,
    mobile VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    password VARCHAR(255) NOT NULL,
    is_active BIT NOT NULL DEFAULT 1,
    last_login DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    
    CONSTRAINT PK_users PRIMARY KEY (user_code),
    CONSTRAINT CK_users_code CHECK (user_code LIKE '[0-9][0-9][0-9]')
);
GO

CREATE INDEX idx_users_name ON users(user_name);
CREATE INDEX idx_users_email ON users(email);
GO

-- ============================================
-- 2. 거래처 테이블 (customers)
-- ============================================
CREATE TABLE customers (
    customer_code CHAR(4) NOT NULL,
    customer_name NVARCHAR(100) NOT NULL,
    ceo_name NVARCHAR(50) NULL,
    business_number VARCHAR(12) NULL,
    business_type NVARCHAR(50) NULL,
    business_item NVARCHAR(50) NULL,
    address NVARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    fax VARCHAR(20) NULL,
    mobile VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    manager_name NVARCHAR(50) NULL,
    manager_contact VARCHAR(20) NULL,
    notes NVARCHAR(MAX) NULL,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME NOT NULL DEFAULT GETDATE(),
    
    CONSTRAINT PK_customers PRIMARY KEY (customer_code),
    CONSTRAINT CK_customers_code CHECK (customer_code LIKE '[0-9][0-9][0-9][0-9]')
);
GO

CREATE INDEX idx_customers_name ON customers(customer_name);
CREATE INDEX idx_customers_business_number ON customers(business_number);
CREATE INDEX idx_customers_ceo_name ON customers(ceo_name);
GO

-- ============================================
-- 3. 상품 테이블 (products)
-- ============================================
CREATE TABLE products (
    product_code CHAR(4) NOT NULL,
    product_name NVARCHAR(100) NOT NULL,
    product_spec NVARCHAR(100) NULL,
    description NVARCHAR(MAX) NULL,
    image_url VARCHAR(500) NULL,
    info_url VARCHAR(500) NULL,
    notes NVARCHAR(MAX) NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    standard_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME NOT NULL DEFAULT GETDATE(),
    
    CONSTRAINT PK_products PRIMARY KEY (product_code),
    CONSTRAINT CK_products_code CHECK (product_code LIKE '[0-9][0-9][0-9][0-9]')
);
GO

CREATE INDEX idx_products_name ON products(product_name);
GO

-- ============================================
-- 4. 거래내역 테이블 (transactions)
-- ============================================
CREATE TABLE transactions (
    transaction_id INT IDENTITY(1,1) NOT NULL,
    transaction_date DATE NOT NULL,
    transaction_type VARCHAR(20) NOT NULL,
    customer_code CHAR(4) NOT NULL,
    user_code CHAR(3) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    notes NVARCHAR(MAX) NULL,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME NOT NULL DEFAULT GETDATE(),
    
    CONSTRAINT PK_transactions PRIMARY KEY (transaction_id),
    CONSTRAINT FK_transactions_customer FOREIGN KEY (customer_code) 
        REFERENCES customers(customer_code),
    CONSTRAINT FK_transactions_user FOREIGN KEY (user_code) 
        REFERENCES users(user_code),
    CONSTRAINT CK_transactions_type CHECK (transaction_type IN 
        ('IN', 'OUT', 'IN_RETURN', 'OUT_RETURN', 'RECEIVE', 'PAYMENT'))
);
GO

CREATE INDEX idx_transactions_date ON transactions(transaction_date);
CREATE INDEX idx_transactions_customer ON transactions(customer_code);
CREATE INDEX idx_transactions_user ON transactions(user_code);
CREATE INDEX idx_transactions_type ON transactions(transaction_type);
GO

-- ============================================
-- 5. 거래 상세 테이블 (transaction_items)
-- ============================================
CREATE TABLE transaction_items (
    item_id INT IDENTITY(1,1) NOT NULL,
    transaction_id INT NOT NULL,
    product_code CHAR(4) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    
    CONSTRAINT PK_transaction_items PRIMARY KEY (item_id),
    CONSTRAINT FK_items_transaction FOREIGN KEY (transaction_id) 
        REFERENCES transactions(transaction_id) ON DELETE CASCADE,
    CONSTRAINT FK_items_product FOREIGN KEY (product_code) 
        REFERENCES products(product_code),
    CONSTRAINT CK_items_quantity CHECK (quantity > 0)
);
GO

CREATE INDEX idx_items_transaction ON transaction_items(transaction_id);
CREATE INDEX idx_items_product ON transaction_items(product_code);
GO

-- ============================================
-- 트리거: customers 수정일시 자동 업데이트
-- ============================================
CREATE TRIGGER trg_customers_update
ON customers
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE customers
    SET updated_at = GETDATE()
    FROM customers c
    INNER JOIN inserted i ON c.customer_code = i.customer_code;
END;
GO

-- ============================================
-- 트리거: products 수정일시 자동 업데이트
-- ============================================
CREATE TRIGGER trg_products_update
ON products
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE products
    SET updated_at = GETDATE()
    FROM products p
    INNER JOIN inserted i ON p.product_code = i.product_code;
END;
GO

-- ============================================
-- 트리거: transactions 수정일시 자동 업데이트
-- ============================================
CREATE TRIGGER trg_transactions_update
ON transactions
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE transactions
    SET updated_at = GETDATE()
    FROM transactions t
    INNER JOIN inserted i ON t.transaction_id = i.transaction_id;
END;
GO

-- ============================================
-- 트리거: 입고/출고 시 재고 자동 증감
-- ============================================
CREATE TRIGGER trg_stock_change
ON transaction_items
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @transaction_type VARCHAR(20);
    DECLARE @product_code CHAR(4);
    DECLARE @quantity INT;
    
    -- 삽입된 각 항목에 대해 처리
    DECLARE item_cursor CURSOR FOR
    SELECT i.product_code, i.quantity, t.transaction_type
    FROM inserted i
    INNER JOIN transactions t ON i.transaction_id = t.transaction_id;
    
    OPEN item_cursor;
    FETCH NEXT FROM item_cursor INTO @product_code, @quantity, @transaction_type;
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- 입고 또는 출고반품: 재고 증가
        IF @transaction_type IN ('IN', 'OUT_RETURN')
        BEGIN
            UPDATE products
            SET stock_quantity = stock_quantity + @quantity
            WHERE product_code = @product_code;
        END
        -- 출고 또는 입고반품: 재고 감소
        ELSE IF @transaction_type IN ('OUT', 'IN_RETURN')
        BEGIN
            UPDATE products
            SET stock_quantity = stock_quantity - @quantity
            WHERE product_code = @product_code;
        END
        
        FETCH NEXT FROM item_cursor INTO @product_code, @quantity, @transaction_type;
    END
    
    CLOSE item_cursor;
    DEALLOCATE item_cursor;
END;
GO

-- ============================================
-- 트리거: 거래 삭제 시 재고 복원
-- ============================================
CREATE TRIGGER trg_stock_restore
ON transaction_items
AFTER DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @transaction_type VARCHAR(20);
    DECLARE @product_code CHAR(4);
    DECLARE @quantity INT;
    
    -- 삭제된 각 항목에 대해 처리
    DECLARE item_cursor CURSOR FOR
    SELECT d.product_code, d.quantity, t.transaction_type
    FROM deleted d
    INNER JOIN transactions t ON d.transaction_id = t.transaction_id;
    
    OPEN item_cursor;
    FETCH NEXT FROM item_cursor INTO @product_code, @quantity, @transaction_type;
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- 입고 또는 출고반품이었으면: 재고 감소 (복원)
        IF @transaction_type IN ('IN', 'OUT_RETURN')
        BEGIN
            UPDATE products
            SET stock_quantity = stock_quantity - @quantity
            WHERE product_code = @product_code;
        END
        -- 출고 또는 입고반품이었으면: 재고 증가 (복원)
        ELSE IF @transaction_type IN ('OUT', 'IN_RETURN')
        BEGIN
            UPDATE products
            SET stock_quantity = stock_quantity + @quantity
            WHERE product_code = @product_code;
        END
        
        FETCH NEXT FROM item_cursor INTO @product_code, @quantity, @transaction_type;
    END
    
    CLOSE item_cursor;
    DEALLOCATE item_cursor;
END;
GO

-- ============================================
-- 저장 프로시저: 다음 사용자 코드 생성
-- ============================================
CREATE PROCEDURE sp_get_next_user_code
AS
BEGIN
    SELECT RIGHT('000' + CAST(ISNULL(MAX(CAST(user_code AS INT)), 0) + 1 AS VARCHAR), 3) AS next_code
    FROM users;
END;
GO

-- ============================================
-- 저장 프로시저: 다음 거래처 코드 생성
-- ============================================
CREATE PROCEDURE sp_get_next_customer_code
AS
BEGIN
    SELECT RIGHT('0000' + CAST(ISNULL(MAX(CAST(customer_code AS INT)), 0) + 1 AS VARCHAR), 4) AS next_code
    FROM customers;
END;
GO

-- ============================================
-- 저장 프로시저: 다음 상품 코드 생성
-- ============================================
CREATE PROCEDURE sp_get_next_product_code
AS
BEGIN
    SELECT RIGHT('0000' + CAST(ISNULL(MAX(CAST(product_code AS INT)), 0) + 1 AS VARCHAR), 4) AS next_code
    FROM products;
END;
GO

-- ============================================
-- 저장 프로시저: 코드 중복 체크
-- ============================================
CREATE PROCEDURE sp_check_customer_code
    @customer_code CHAR(4)
AS
BEGIN
    SELECT COUNT(*) AS count
    FROM customers
    WHERE customer_code = @customer_code;
END;
GO

CREATE PROCEDURE sp_check_product_code
    @product_code CHAR(4)
AS
BEGIN
    SELECT COUNT(*) AS count
    FROM products
    WHERE product_code = @product_code;
END;
GO

-- ============================================
-- 뷰: 거래내역 상세 조회
-- ============================================
CREATE VIEW v_transaction_details
AS
SELECT 
    t.transaction_id,
    t.transaction_date,
    t.transaction_type,
    CASE t.transaction_type
        WHEN 'IN' THEN N'입고'
        WHEN 'OUT' THEN N'출고'
        WHEN 'IN_RETURN' THEN N'입고반품'
        WHEN 'OUT_RETURN' THEN N'출고반품'
        WHEN 'RECEIVE' THEN N'수금'
        WHEN 'PAYMENT' THEN N'지급'
    END AS transaction_type_name,
    c.customer_code,
    c.customer_name,
    u.user_code,
    u.user_name AS input_user,
    t.total_amount,
    t.notes,
    t.created_at AS input_datetime,
    ti.item_id,
    ti.product_code,
    p.product_name,
    ti.quantity,
    ti.unit_price,
    ti.amount
FROM transactions t
INNER JOIN customers c ON t.customer_code = c.customer_code
INNER JOIN users u ON t.user_code = u.user_code
LEFT JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
LEFT JOIN products p ON ti.product_code = p.product_code;
GO

-- ============================================
-- 뷰: 상품 재고 현황
-- ============================================
CREATE VIEW v_product_stock
AS
SELECT 
    product_code,
    product_name,
    product_spec,
    stock_quantity,
    standard_price,
    (stock_quantity * standard_price) AS stock_value,
    CASE 
        WHEN stock_quantity < 0 THEN N'마이너스'
        WHEN stock_quantity = 0 THEN N'재고없음'
        ELSE N'정상'
    END AS stock_status
FROM products;
GO

-- ============================================
-- 샘플 데이터 삽입
-- ============================================

-- 1. 사용자 샘플 데이터
-- 비밀번호: admin123, user123 (실제로는 해시화 필요)
INSERT INTO users (user_code, user_name, mobile, email, password, is_active) VALUES
('001', N'관리자', '010-1234-5678', 'admin@myshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('002', N'홍길동', '010-2345-6789', 'hong@myshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('003', N'김철수', '010-3456-7890', 'kim@myshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
GO

-- 2. 거래처 샘플 데이터
INSERT INTO customers (customer_code, customer_name, ceo_name, business_number, business_type, business_item, address, phone, mobile, email, manager_name, manager_contact) VALUES
('0001', N'(주)테크상사', N'박대표', '123-45-67890', N'도소매업', N'전자제품', N'서울시 강남구 테헤란로 123', '02-1234-5678', '010-1111-2222', 'tech@example.com', N'이과장', '010-1111-3333'),
('0002', N'글로벌무역', N'최사장', '234-56-78901', N'제조업', N'의류', N'서울시 서초구 서초대로 456', '02-2345-6789', '010-2222-3333', 'global@example.com', N'정대리', '010-2222-4444'),
('0003', N'한국유통', N'강대표', '345-67-89012', N'도소매업', N'식품', N'경기도 성남시 분당구 판교로 789', '031-1234-5678', '010-3333-4444', 'korea@example.com', N'송과장', '010-3333-5555'),
('0004', N'동방전자', N'조사장', '456-78-90123', N'제조업', N'전자부품', N'인천시 남동구 논현로 321', '032-9876-5432', '010-4444-5555', 'dongbang@example.com', N'윤차장', '010-4444-6666');
GO

-- 3. 상품 샘플 데이터
INSERT INTO products (product_code, product_name, product_spec, description, image_url, info_url, standard_price, stock_quantity) VALUES
('0001', N'노트북 A100', N'15.6인치, i5, 8GB RAM', N'비즈니스용 노트북', 'https://via.placeholder.com/150', 'https://example.com/products/laptop-a100', 1200000, 15),
('0002', N'무선마우스', N'2.4GHz, 1600DPI', N'사무용 무선마우스', 'https://via.placeholder.com/150', 'https://example.com/products/mouse', 35000, 50),
('0003', N'기계식키보드', N'청축, 한영', N'게이밍용 키보드', 'https://via.placeholder.com/150', 'https://example.com/products/keyboard', 80000, 30),
('0004', N'모니터 27인치', N'27인치, FHD, IPS', N'사무용 모니터', 'https://via.placeholder.com/150', 'https://example.com/products/monitor', 350000, 20),
('0005', N'USB 메모리', N'64GB, USB 3.0', N'고속 USB 메모리', 'https://via.placeholder.com/150', 'https://example.com/products/usb', 25000, 100),
('0006', N'외장하드', N'1TB, USB 3.0', N'백업용 외장하드', 'https://via.placeholder.com/150', 'https://example.com/products/hdd', 75000, 40),
('0007', N'웹캠', N'1080P, 마이크 내장', N'화상회의용 웹캠', 'https://via.placeholder.com/150', 'https://example.com/products/webcam', 55000, 25),
('0008', N'헤드셋', N'노이즈캔슬링', N'업무용 헤드셋', 'https://via.placeholder.com/150', 'https://example.com/products/headset', 95000, 35);
GO

-- 4. 거래내역 샘플 데이터 (입고)
INSERT INTO transactions (transaction_date, transaction_type, customer_code, user_code, total_amount, notes)
VALUES ('2025-10-01', 'IN', '0001', '001', 25000000, N'10월 정기 입고');
GO

DECLARE @trans_id INT = SCOPE_IDENTITY();

INSERT INTO transaction_items (transaction_id, product_code, quantity, unit_price, amount) VALUES
(@trans_id, '0001', 20, 1000000, 20000000),
(@trans_id, '0002', 100, 25000, 2500000),
(@trans_id, '0003', 50, 50000, 2500000);
GO

-- 5. 거래내역 샘플 데이터 (출고)
INSERT INTO transactions (transaction_date, transaction_type, customer_code, user_code, total_amount, notes)
VALUES ('2025-10-05', 'OUT', '0002', '002', 6500000, N'글로벌무역 납품');
GO

DECLARE @trans_id2 INT = SCOPE_IDENTITY();

INSERT INTO transaction_items (transaction_id, product_code, quantity, unit_price, amount) VALUES
(@trans_id2, '0001', 5, 1200000, 6000000),
(@trans_id2, '0002', 10, 35000, 350000),
(@trans_id2, '0003', 2, 75000, 150000);
GO

-- 6. 수금 샘플 데이터
INSERT INTO transactions (transaction_date, transaction_type, customer_code, user_code, total_amount, notes)
VALUES ('2025-10-10', 'RECEIVE', '0002', '001', 3000000, N'글로벌무역 부분 수금');
GO

PRINT '============================================';
PRINT 'MyShop 데이터베이스 생성 완료!';
PRINT '============================================';
PRINT '';
PRINT '생성된 테이블:';
PRINT '- users (사용자)';
PRINT '- customers (거래처)';
PRINT '- products (상품)';
PRINT '- transactions (거래내역)';
PRINT '- transaction_items (거래 상세)';
PRINT '';
PRINT '샘플 데이터:';
PRINT '- 사용자: 3명';
PRINT '- 거래처: 4개';
PRINT '- 상품: 8개';
PRINT '- 거래내역: 3건 (입고 1, 출고 1, 수금 1)';
PRINT '';
PRINT '기본 로그인 정보:';
PRINT '- 사용자코드: 001, 비밀번호: admin123';
PRINT '- 사용자코드: 002, 비밀번호: user123';
PRINT '============================================';
GO