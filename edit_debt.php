<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: default.php");
    exit();
}

if (isset($_GET['debt_id'])) {
    $debt_id = $_GET['debt_id'];
    // جلب بيانات الدين
    $stmt = $conn->prepare("SELECT * FROM debts WHERE id = ?");
    $stmt->bind_param("i", $debt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $debt = $result->fetch_assoc();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $remaining_amount = floatval(str_replace(',', '', $_POST['remaining_amount']));
        $description = !empty($_POST['description']) ? $_POST['description'] : NULL;

        if ($remaining_amount >= 0) {
            // تحديث الدين
            $update_debt = $conn->prepare("UPDATE debts SET remaining_amount = ?, description = ? WHERE id = ?");
            $update_debt->bind_param("dsi", $remaining_amount, $description, $debt_id);
            if ($update_debt->execute()) {
                $message = "تم تعديل الدين بنجاح";
                header("Location: pay_debt.php"); // إعادة التوجيه بعد التعديل
                exit();
            } else {
                $message = "حدث خطأ أثناء تعديل الدين";
            }
        } else {
            $message = "يرجى إدخال مبلغ صحيح";
        }
    }
} else {
    header("Location: dashbaord.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الدين</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>تعديل الدين</h2>

        <?php if (isset($message)) echo "<p style='color: green;'>$message</p>"; ?>

        <form method="POST">
            <label for="remaining_amount">المبلغ المتبقي:</label>
            <input type="text" name="remaining_amount" value="<?php echo number_format($debt['remaining_amount'], 2); ?>" required oninput="formatNumber(this)">

            <label for="description">البيان:</label>
            <textarea name="description"><?php echo htmlspecialchars($debt['description']); ?></textarea>

            <button type="submit">تعديل الدين</button>
        </form>
        <br>
        <a href="pay_debt.php">العودة لقائمة الديون</a>
    </div>
</body>
</html>
