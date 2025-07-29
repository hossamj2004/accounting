<?php
require_once __DIR__ . '/../src/db_helpers.php';

$token = $_GET['token'] ?? '';
$accountInfo = findAccountByToken($token);

if (!$accountInfo) {
    die('الرابط غير صالح أو منتهي الصلاحية.');
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة معاملة</title>
    <style>
        body { font-family: sans-serif; direction: rtl; background-color: #f4f4f4; }
        .container { max-width: 500px; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 5px; }
        h2 { text-align: center; }
        .form-row { margin-bottom: 10px; }
        input, button { width: 100%; padding: 10px; box-sizing: border-box; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>إضافة معاملة لحساب: <?php echo htmlspecialchars($accountInfo['name']); ?></h2>
        <p>يمكنك استخدام هذا النموذج لإعلام صاحب الحساب بالمدفوعات التي أرسلتها له.</p>
        <form action="../src/client_transaction_handler.php" method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-row">
                <label for="amount">المبلغ الذي دفعته</label>
                <input type="number" id="amount" name="amount" step="0.01" required>
            </div>
            <div class="form-row">
                <label for="description">وصف (مثال: دفعة من حساب شهر 5)</label>
                <input type="text" id="description" name="description">
            </div>
            <button type="submit">إضافة المعاملة</button>
        </form>
    </div>
</body>
</html>
