<?php
require_once __DIR__ . '/db_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    if (!isset($_POST['token'], $_POST['amount']) || !is_numeric($_POST['amount'])) {
        die('بيانات غير صالحة.');
    }

    $token = $_POST['token'];
    $amount = (float)$_POST['amount'];
    $description = "معاملة من العميل: " . ($_POST['description'] ?? 'بدون وصف');

    if ($amount <= 0) {
        die('المبلغ يجب أن يكون أكبر من صفر.');
    }

    // Find the account using the token
    $accountInfo = findAccountByToken($token);

    if (!$accountInfo) {
        die('الرابط غير صالح أو منتهي الصلاحية.');
    }

    $userId = $accountInfo['user_id'];
    $accountId = $accountInfo['id'];
    $userDbPath = __DIR__ . '/../database/user_' . $userId . '.sqlite';

    try {
        $db = new PDO('sqlite:' . $userDbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert the transaction as 'credit'
        $stmt = $db->prepare(
            "INSERT INTO transactions (account_id, type, amount, description) VALUES (:account_id, 'credit', :amount, :description)"
        );
        $stmt->bindParam(':account_id', $accountId);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->execute();

        // Display a success message
        echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><title>تم بنجاح</title><style>body { font-family: sans-serif; text-align: center; margin-top: 50px; }</style></head><body>';
        echo '<h2>تمت إضافة المعاملة بنجاح!</h2>';
        echo '<p>شكراً لك.</p>';
        echo '</body></html>';
        exit;

    } catch (PDOException $e) {
        die("حدث خطأ ما. الرجاء المحاولة مرة أخرى.");
    }

} else {
    // Redirect if not a POST request
    header('Location: ../public/index.php');
    exit;
}
?>
