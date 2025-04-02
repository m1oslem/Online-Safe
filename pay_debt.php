<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: default.php");
    exit();
}

// تسديد الدين
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_debt'])) {
    $debt_id = $_POST['debt_id'];
    $amount = floatval(str_replace(',', '', $_POST['amount']));
    $description = !empty($_POST['description']) ? $_POST['description'] : NULL;

    if ($amount > 0) {
        // جلب بيانات الدين
        $stmt = $conn->prepare("SELECT fund_id, remaining_amount FROM debts WHERE id = ?");
        $stmt->bind_param("i", $debt_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $debt = $result->fetch_assoc();

        if ($debt && $debt['remaining_amount'] >= $amount) {
            // بدء المعاملة المالية
            $conn->begin_transaction();
            try {
                // تحديث المبلغ المتبقي في الدين
                $update_debt = $conn->prepare("UPDATE debts SET remaining_amount = remaining_amount - ? WHERE id = ?");
                $update_debt->bind_param("di", $amount, $debt_id);
                $update_debt->execute();

                // إضافة المبلغ إلى الصندوق
                $update_fund = $conn->prepare("UPDATE funds SET balance = balance + ? WHERE id = ?");
                $update_fund->bind_param("di", $amount, $debt['fund_id']);
                $update_fund->execute();

                // تسجيل العملية في transactions
                $add_transaction = $conn->prepare("INSERT INTO transactions (fund_id, type, amount, description) VALUES (?, 'deposit', ?, ?)");
                $add_transaction->bind_param("ids", $debt['fund_id'], $amount, $description);
                $add_transaction->execute();

                $conn->commit();
                $message = "<p style='color: green;'>تم تسديد المبلغ بنجاح</p>";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "<p style='color: red;'>حدث خطأ أثناء تسديد الدين</p>";
            }
        } else {
            $message = "<p style='color: red;'>المبلغ المدفوع أكبر من المتبقي</p>";
        }
    } else {
        $message = "<p style='color: red;'>يرجى إدخال مبلغ صحيح</p>";
    }
}

// جلب قائمة الديون الغير مسددة بالكامل
$debts = $conn->query("SELECT debts.*, funds.name AS fund_name FROM debts JOIN funds ON debts.fund_id = funds.id WHERE debts.remaining_amount > 0 ORDER BY debts.created_at DESC");
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسديد الديون</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function formatNumber(input) {
            let value = input.value.replace(/,/g, '');
            let formattedValue = new Intl.NumberFormat().format(value);
            input.value = formattedValue;
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>تسديد الديون</h2>

        <?php if (isset($message)) echo $message; ?>

        <h3>اختيار دين للتسديد</h3>
        <form method="POST">
            <select name="debt_id" required>
                <option value="">اختر الدين</option>
                <?php while ($debt = $debts->fetch_assoc()) { ?>
                    <option value="<?php echo $debt['id']; ?>">
                        <?php echo htmlspecialchars($debt['person_name']); ?> - 
                        <?php echo number_format($debt['remaining_amount'], 2); ?> دينار (من صندوق: <?php echo htmlspecialchars($debt['fund_name']); ?>)
                    </option>
                <?php } ?>
            </select>

            <input type="text" name="amount" placeholder="المبلغ المدفوع" required oninput="formatNumber(this)">
            <textarea name="description" placeholder="بيان التسديد (اختياري)"></textarea>
            <button type="submit" name="pay_debt">تسديد الدين</button>
        </form>

        <h3>قائمة الديون الغير مسددة بالكامل</h3>
        <table>
            <tr>
                <th>اسم الشخص</th>
                <th>الصندوق</th>
                <th>المبلغ الكلي</th>
                <th>المتبقي</th>
                <th>التاريخ</th>
                <th>البيان</th>
                <th>الاجرائات</th>
            </tr>
            <?php
            $debts->data_seek(0);
            while ($debt = $debts->fetch_assoc()) { ?>
            <tr>
            <td><?php echo htmlspecialchars($debt['person_name']); ?></td>
            <td><?php echo htmlspecialchars($debt['fund_name']); ?></td>
           <td><?php echo number_format($debt['total_amount'], 2); ?> دينار</td>
           <td><?php echo number_format($debt['remaining_amount'], 2); ?> دينار</td>
           <td><?php echo $debt['created_at']; ?></td>
           <td><?php echo !empty($debt['description']) ? htmlspecialchars($debt['description']) : '—'; ?></td>
          <td>
        <a href="edit_debt.php?debt_id=<?php echo $debt['id']; ?>">تعديل</a> |
        <a href="delete_debt.php?debt_id=<?php echo $debt['id']; ?>" onclick="return confirm('هل أنت متأكد من حذف هذا الدين؟');" style="color: red;">حذف</a>
    </td>
</tr>

            <?php } ?>
        </table>

        <br>
        <a href="dashboard.php">العودة للوحة التحكم</a> | 
        <a href="logout.php">تسجيل الخروج</a>
    </div>
</body>
</html>
