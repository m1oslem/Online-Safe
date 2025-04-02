<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: default.php");
    exit();
}

// إضافة صندوق جديد
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fund_name = $_POST['fund_name'];
    $stmt = $conn->prepare("INSERT INTO funds (name, balance) VALUES (?, 0)");
    $stmt->bind_param("s", $fund_name);
    if ($stmt->execute()) {
        $message = "تمت إضافة الصندوق بنجاح";
    } else {
        $message = "حدث خطأ أثناء إضافة الصندوق";
    }
}

// جلب بيانات الصناديق
$funds = $conn->query("SELECT * FROM funds");
?>
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <!--<style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
        .container { background: #f4f4f4; padding: 20px; border-radius: 8px; display: inline-block; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 10px; text-align: center; }
        a { text-decoration: none; color: #007BFF; font-weight: bold; }
    </style> -->
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h2>مرحبًا، <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <h3>إدارة الحسابات والصناديق</h3>

        <?php if (isset($message))
            echo "<p style='color: green;'>$message</p>"; ?>

        <form method="POST">
            <input type="text" name="fund_name" placeholder="اسم الصندوق الجديد" required>
            <button type="submit">إضافة صندوق</button>
        </form>
        <a href="debts.php">
            <button type="button">اضافة دين</button>
        </a>
        <a href="pay_debt.php">
            <button type="button">تسديد دين</button>
        </a>
        <table>
            <tr>
                <th>اسم الصندوق</th>
                <th>الرصيد</th>
                <th>إجراءات</th>
            </tr>
            <?php while ($fund = $funds->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($fund['name']); ?></td>
                    <td><?php echo number_format($fund['balance'], 2); ?> دينار</td>
                <td>
                      <a href="add_transaction.php?fund_id=<?php echo $fund['id']; ?>">إيداع و سحب</a> |
                     <a href="view_transactions.php?fund_id=<?php echo $fund['id']; ?>">عرض العمليات</a> |
                     <a href="edit_fund.php?fund_id=<?php echo $fund['id']; ?>">تعديل</a> |
                      <a href="delete_fund.php?fund_id=<?php echo $fund['id']; ?>" onclick="return confirm('هل أنت متأكد من حذف هذا الصندوق؟');" style="color: red;">حذف</a>
                </td>
                </tr>
            <?php } ?>
        </table>
        <br>
        <a href="logout.php">تسجيل الخروج</a>
    </div>
</body>

</html>