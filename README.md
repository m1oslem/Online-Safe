# Online-Safe
This is online save


---------------------------------------------------------------------------------------  SQL  -----------------------------------------------------------------------------------------------
1. إنشاء قاعدة البيانات
CREATE DATABASE funds_management;
USE funds_management;
2. إنشاء جدول المستخدمين (لحماية الدخول)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    security_code VARCHAR(10) NOT NULL
);
3. إنشاء جدول الصناديق
CREATE TABLE funds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    balance DECIMAL(15,2) NOT NULL DEFAULT 0
);
4. إنشاء جدول العمليات (إيداع/سحب)
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fund_id INT NOT NULL,
    type ENUM('deposit', 'withdraw') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fund_id) REFERENCES funds(id) ON DELETE CASCADE
);
5. إنشاء جدول الديون
CREATE TABLE debts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_name VARCHAR(100) NOT NULL,
    fund_id INT NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    remaining_amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fund_id) REFERENCES funds(id) ON DELETE CASCADE
);
6. إنشاء جدول تسديد الديون
CREATE TABLE debt_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    debt_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (debt_id) REFERENCES debts(id) ON DELETE CASCADE
);
7. إضافة مستخدم تجريبي
INSERT INTO users (username, password, security_code) 
VALUES ('admin', SHA2('admin123', 256), '123456');
🔹 كلمة المرور مشفرة بـ SHA-256 للأمان.
🔹 رمز الدخول = 123456 (يمكن تغييره لاحقًا).
