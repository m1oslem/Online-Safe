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

$result = $conn->query("SELECT * FROM transactions WHERE id = $transaction_id");
$transaction = $result->fetch_assoc();

if (!$transaction) {
    die("المعاملة غير موجودة.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_amount = floatval($_POST['amount']);
    $new_description = !empty($_POST['description']) ? $_POST['description'] : NULL;

    // تحديث المعاملة
    $stmt = $conn->prepare("UPDATE transactions SET amount = ?, description = ? WHERE id = ?");
    $stmt->bind_param("dsi", $new_amount, $new_description, $transaction_id);

    if ($stmt->execute()) {
        header("Location: view_transactions.php?fund_id=$fund_id&message=تم تعديل المعاملة بنجاح");
        exit();
    } else {
        $error = "حدث خطأ أثناء تعديل المعاملة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المعاملة</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>تعديل المعاملة</h2>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <form method="POST">
            <label>المبلغ الجديد:</label>
            <input type="number" step="0.01" name="amount" value="<?php echo $transaction['amount']; ?>" required>
            
            <label>الوصف الجديد (اختياري):</label>
            <input type="text" name="description" value="<?php echo htmlspecialchars($transaction['description'] ?? ''); ?>">
            
            <button type="submit">حفظ التعديلات</button>
        </form>
        <br>
        <a href="view_transactions.php?fund_id=<?php echo $fund_id; ?>">العودة</a>
    </div>
</body>
</html>
