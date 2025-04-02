<?php
$servername = "localhost";
$username = "u493451227_cash"; // اسم المستخدم الخاص بقاعدة البيانات
$password = "Moslem_m1"; // كلمة المرور الخاصة بقاعدة البيانات
$database = "u493451227_cash";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $database);

// فحص الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
?>
