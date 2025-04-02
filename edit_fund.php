<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: default.php");
    exit();
}

if (isset($_GET['fund_id'])) {
    $fund_id = $_GET['fund_id'];
    $stmt = $conn->prepare("SELECT * FROM funds WHERE id = ?");
    $stmt->bind_param("i", $fund_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fund = $result->fetch_assoc();
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_fund_name = $_POST['fund_name'];
        $update_stmt = $conn->prepare("UPDATE funds SET name = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_fund_name, $fund_id);
        if ($update_stmt->execute()) {
            $message = "تم تعديل اسم الصندوق بنجاح";
            header("Location: dashboard.php"); // إعادة التوجيه بعد التعديل
            exit();
        } else {
            $message = "حدث خطأ أثناء تعديل اسم الصندوق";
        }
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل اسم الصندوق</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h2>تعديل اسم الصندوق</h2>

        <?php if (isset($message)) echo "<p style='color: green;'>$message</p>"; ?>

        <form method="POST">
            <input type="text" name="fund_name" value="<?php echo htmlspecialchars($fund['name']); ?>" required>
            <button type="submit">تعديل</button>
        </form>
        <br>
        <a href="dashboard.php">عودة إلى قائمة الصناديق</a>
    </div>
</body>

</html>
