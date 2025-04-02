<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: default.php");
    exit();
}

// إضافة دين جديد
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_debt'])) {
    $person_name = $_POST['person_name'];
    $fund_id = $_POST['fund_id'];
    $amount = floatval(str_replace(',', '', $_POST['amount']));
    $description = !empty($_POST['description']) ? $_POST['description'] : NULL;

    if ($amount > 0) {
        // التحقق من رصيد الصندوق
        $stmt = $conn->prepare("SELECT balance FROM funds WHERE id = ?");
        $stmt->bind_param("i", $fund_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $fund = $result->fetch_assoc();

        if ($fund && $fund['balance'] >= $amount) {
            $conn->begin_transaction();
            try {
                $update_fund = $conn->prepare("UPDATE funds SET balance = balance - ? WHERE id = ?");
                $update_fund->bind_param("di", $amount, $fund_id);
                $update_fund->execute();

                $add_debt = $conn->prepare("INSERT INTO debts (person_name, fund_id, total_amount, remaining_amount, description) VALUES (?, ?, ?, ?, ?)");
                $add_debt->bind_param("siids", $person_name, $fund_id, $amount, $amount, $description);
                $add_debt->execute();

                $add_transaction = $conn->prepare("INSERT INTO transactions (fund_id, type, amount, description) VALUES (?, 'withdrawal', ?, ?)");
                $add_transaction->bind_param("ids", $fund_id, $amount, $description);
                $add_transaction->execute();

                $conn->commit();
                $message = "<p style='color: green;'>تم تسجيل الدين بنجاح</p>";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "<p style='color: red;'>حدث خطأ أثناء تسجيل الدين</p>";
            }
        } else {
            $message = "<p style='color: red;'>الرصيد غير كافٍ في الصندوق</p>";
        }
    } else {
        $message = "<p style='color: red;'>يرجى إدخال مبلغ صحيح</p>";
    }
}

$funds = $conn->query("SELECT * FROM funds");
$debts = $conn->query("SELECT debts.*, funds.name AS fund_name FROM debts JOIN funds ON debts.fund_id = funds.id ORDER BY debts.created_at DESC");
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الديون</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function formatNumber(input) {
            let value = input.value.replace(/,/g, '');
            if (!isNaN(value) && value.length > 0) {
                input.value = parseFloat(value).toLocaleString('en-US');
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>إدارة الديون</h2>
        <?php if (isset($message)) echo $message; ?>

        <h3>إضافة دين جديد</h3>
        <form method="POST">
            <input type="text" name="person_name" placeholder="اسم الشخص" required>
            <select name="fund_id" required>
                <option value="">اختر الصندوق</option>
                <?php while ($fund = $funds->fetch_assoc()) { ?>
                    <option value="<?php echo $fund['id']; ?>">
                        <?php echo htmlspecialchars($fund['name']); ?> - الرصيد: <?php echo number_format($fund['balance'], 2); ?> دينار
                    </option>
                <?php } ?>
            </select>
            <input type="text" name="amount" placeholder="المبلغ" required oninput="formatNumber(this)">
            <textarea name="description" placeholder="بيان الدين (اختياري)"></textarea>
            <button type="submit" name="add_debt">إضافة الدين</button>
        </form>

        <h3>قائمة الديون</h3>
        <table>
            <tr>
                <th>اسم الشخص</th>
                <th>الصندوق</th>
                <th>المبلغ الكلي</th>
                <th>المتبقي</th>
                <th>التاريخ</th>
                <th>البيان</th>
            </tr>
            <?php while ($debt = $debts->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($debt['person_name']); ?></td>
                <td><?php echo htmlspecialchars($debt['fund_name']); ?></td>
                <td><?php echo number_format($debt['total_amount'], 2); ?> دينار</td>
                <td><?php echo number_format($debt['remaining_amount'], 2); ?> دينار</td>
                <td><?php echo $debt['created_at']; ?></td>
                <td><?php echo !empty($debt['description']) ? htmlspecialchars($debt['description']) : '—'; ?></td>
            </tr>
            <?php } ?>
        </table>

        <br>
        <a href="dashboard.php">العودة للوحة التحكم</a> | 
        <a href="logout.php">تسجيل الخروج</a>
    </div>
</body>
</html>
