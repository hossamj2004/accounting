<?php
session_start();

// If user is logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دفتر اليومية</title>
    <style>
        body { font-family: sans-serif; text-align: center; margin-top: 100px; }
        .container { max-width: 600px; margin: auto; }
        h1 { font-size: 2.5em; }
        p { font-size: 1.2em; color: #555; }
        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            margin-top: 20px;
            font-size: 1.2em;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .login-link { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>دفتر اليومية الرقمي</h1>
        <p>أدر حساباتك وديونك بكل سهولة وأمان. أنشئ دفترك الخاص مجانًا.</p>
        <a href="register.php" class="cta-button">ابدأ الآن</a>
        <div class="login-link">
            <p>لديك حساب بالفعل؟ <a href="login.php">سجل الدخول</a></p>
        </div>
    </div>
</body>
</html>
