<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد</title>
    <style>
        body { font-family: sans-serif; direction: rtl; }
        .container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        input { width: 100%; padding: 10px; margin-bottom: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .login-link { text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>إنشاء حساب جديد</h2>
        <form action="../src/register_handler.php" method="post">
            <label for="username">اسم المستخدم</label>
            <input type="text" id="username" name="username" required>

            <label for="password">كلمة المرور</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">إنشاء حساب</button>
        </form>
        <div class="login-link">
            <p>لديك حساب بالفعل؟ <a href="login.php">سجل الدخول</a></p>
        </div>
    </div>
</body>
</html>
