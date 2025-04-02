<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: default.php");
    exit();
}

if (!isset($_GET['fund_id'])) {
    die("صندوق غير محدد.");
}

$fund_id = intval($_GET['fund_id']);
$result = $conn->query("SELECT * FROM funds WHERE id = $fund_id");
$fund = $result->fetch_assoc();

if (!$fund) {
    die("الصندوق غير موجود.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval(str_replace(',', '', $_POST['amount'])); // إزالة الفواصل قبل المعالجة
    $type = $_POST['type'];
    $description = $_POST['description'];

    if ($type == "withdraw" && $amount > $fund['balance']) {
        $message = "الرصيد غير كافٍ للسحب";
    } else {
        $new_balance = ($type == "deposit") ? $fund['balance'] + $amount : $fund['balance'] - $amount;
        
        // تحديث الرصيد في الجدول funds
        $stmt = $conn->prepare("UPDATE funds SET balance = ? WHERE id = ?");
        $stmt->bind_param("di", $new_balance, $fund_id);
        
        if ($stmt->execute()) {
            // إضافة سجل المعاملة
            $stmt = $conn->prepare("INSERT INTO transactions (fund_id, type, amount, description, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("isss", $fund_id, $type, $amount, $description);
            
            if ($stmt->execute()) {
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "حدث خطأ أثناء تسجيل العملية: " . $stmt->error;
                error_log("خطأ في إدخال المعاملة: " . $stmt->error);
            }
        } else {
            $message = "حدث خطأ أثناء تحديث الرصيد: " . $stmt->error;
            error_log("خطأ في تحديث الرصيد: " . $stmt->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيداع/سحب</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>إجراء عملية على صندوق: <?php echo htmlspecialchars($fund['name']); ?></h2>
        <p>الرصيد الحالي: <?php echo number_format($fund['balance'], 2); ?> دينار</p>
        
        <?php if (isset($message)) echo "<p style='color: red;'>$message</p>"; ?>
        
        <form method="POST">
            <input type="text" id="amount" name="amount" placeholder="المبلغ" required>
            <select name="type">
                <option value="deposit">إيداع</option>
                <option value="withdraw">سحب</option>
            </select>
            <textarea name="description" rows="4" placeholder="البيان (اختياري)"></textarea>
            <button type="submit">تنفيذ</button>
        </form>
        <br>
        <a href="dashboard.php">العودة إلى لوحة التحكم</a>
    </div>

    <script>
    document.getElementById("amount").addEventListener("input", function (e) {
        let value = e.target.value.replace(/,/g, ""); // إزالة الفواصل السابقة
        if (!isNaN(value) && value.length > 0) {
            e.target.value = parseFloat(value).toLocaleString("en-US"); // إعادة تنسيق الأرقام بفواصل كل 3 خانات
        }
    });
    </script>
</body>
</html>
