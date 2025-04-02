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

$transactions = $conn->query("SELECT * FROM transactions WHERE fund_id = $fund_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل المعاملات</title>
   <!-- <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
        .container { background: #f4f4f4; padding: 20px; border-radius: 8px; display: inline-block; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #007bff; color: white; }
    </style> -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>سجل المعاملات لصندوق: <?php echo htmlspecialchars($fund['name']); ?></h2>
        <p>الرصيد الحالي: <?php echo number_format($fund['balance'], 2); ?> دينار</p>
        <table>
<tr>
    <th>الرقم</th>
    <th>المبلغ</th>
    <th>النوع</th>
    <th>البيان</th>
    <th>التاريخ</th>
    <th>إجراءات</th> <!-- عمود جديد للإجراءات -->
</tr>
<?php while ($row = $transactions->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo number_format($row['amount'], 2); ?></td>
        <td><?php echo ($row['type'] == 'deposit') ? 'إيداع' : 'سحب'; ?></td>
        <td><?php echo htmlspecialchars($row['description']); ?></td>
        <td><?php echo $row['created_at']; ?></td>
        <td>
            <a href="edit_transaction.php?transaction_id=<?php echo $row['id']; ?>&fund_id=<?php echo $fund_id; ?>" 
               style="color: blue;">تعديل</a> |
            <a href="delete_transaction.php?transaction_id=<?php echo $row['id']; ?>&fund_id=<?php echo $fund_id; ?>" 
               onclick="return confirm('هل أنت متأكد من حذف هذه العملية؟');" 
               style="color: red;">حذف</a>
        </td>
    </tr>
<?php endwhile; ?>


        </table>
        <br>
        <a href="dashboard.php">العودة إلى لوحة التحكم</a>
    </div>
</body>
</html>