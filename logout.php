<?php
session_start();

// تدمير جميع الجلسات
session_unset();
session_destroy();

// إعادة التوجيه إلى صفحة الدخول
header("Location: default.php");
exit();
?>
