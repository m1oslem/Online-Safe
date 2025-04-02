<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: default.php");
    exit();
}

if (isset($_GET['fund_id'])) {
    $fund_id = intval($_GET['fund_id']);

    // حذف الصندوق من قاعدة البيانات
    $stmt = $conn->prepare("DELETE FROM funds WHERE id = ?");
    $stmt->bind_param("i", $fund_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?message=تم حذف الصندوق بنجاح");
    } else {
        header("Location: dashboard.php?error=حدث خطأ أثناء الحذف");
    }
} else {
    header("Location: dashboard.php");
}
exit();
