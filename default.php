<?php
session_start();
include 'db.php'; // ملف الاتصال بقاعدة البيانات

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $security_code = $_POST['security_code'];
    
    // نبحث عن أول مستخدم مع رمز الأمان المتطابق
    $stmt = $conn->prepare("SELECT id, security_code FROM users WHERE security_code = ?");
    $stmt->bind_param("s", $security_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "رمز الأمان غير صحيح";
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حسابات المنزل</title>
    
    <!-- ربط الملف styles.php هنا -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>تسجيل الدخول</h2>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <form method="POST">
        <input type="password" name="security_code" placeholder="رمز الأمان" required><br><br>
        <button type="submit">دخول</button>
    </form>
</body>
</html>
