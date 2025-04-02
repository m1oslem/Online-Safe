<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: default.php");
    exit();
}

if (!isset($_GET['transaction_id']) || !isset($_GET['fund_id'])) {
    die("معاملة غير محددة.");
}

$transaction_id = intval($_GET['transaction_id']);
$fund_id = intval($_GET['fund_id']);

// استرجاع تفاصيل المعاملة
$result = $conn->query("SELECT * FROM transactions WHERE id = $transaction_id");
$transaction = $result->fetch_assoc();

if (!$transaction) {
    die("المعاملة غير موجودة.");
}

// تحديث الرصيد عند الحذف
$amount = $transaction['amount'];
$type = $transaction['type'];

if ($type == 'deposit') {
    $conn->query("UPDATE funds SET balance = balance - $amount WHERE id = $fund_id");
} else {
    $conn->query("UPDATE funds SET balance = balance + $amount WHERE id = $fund_id");
}

// حذف المعاملة من قاعدة البيانات
$stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
$stmt->bind_param("i", $transaction_id);

if ($stmt->execute()) {
    header("Location: view_transactions.php?fund_id=$fund_id&message=تم حذف المعاملة بنجاح");
} else {
    header("Location: view_transactions.php?fund_id=$fund_id&error=حدث خطأ أثناء الحذف");
}
exit();
