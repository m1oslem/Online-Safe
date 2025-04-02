<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: default.php");
    exit();
}

if (isset($_GET['debt_id'])) {
    $debt_id = $_GET['debt_id'];
    // حذف الدين من قاعدة البيانات
    $stmt = $conn->prepare("DELETE FROM debts WHERE id = ?");
    $stmt->bind_param("i", $debt_id);

    if ($stmt->execute()) {
        $message = "تم حذف الدين بنجاح";
    } else {
        $message = "حدث خطأ أثناء حذف الدين";
    }

    // إعادة التوجيه إلى قائمة الديون
    header("Location: pay_debt.php");
    exit();
} else {
    header("Location: pay_debt.php");
    exit();
}
?>
