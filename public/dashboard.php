<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userDbPath = __DIR__ . '/../database/user_' . $userId . '.sqlite';

// Function to get all accounts and their balances
function getAccountsWithBalances($dbPath) {
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
            SELECT
                a.id,
                a.name,
                (SELECT COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) FROM transactions WHERE account_id = a.id) as total_credit,
                (SELECT COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) FROM transactions WHERE account_id = a.id) as total_debit
            FROM
                accounts a
        ";

        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    }
}

$accounts = getAccountsWithBalances($userDbPath);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <style>
        body { font-family: sans-serif; direction: rtl; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 5px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header a { text-decoration: none; color: #007bff; }
        .account-list a { display: block; padding: 15px; margin-bottom: 10px; background-color: #e9ecef; border-radius: 5px; text-decoration: none; color: #333; }
        .account-list a:hover { background-color: #dee2e6; }
        .balance { float: left; font-weight: bold; }
        .balance.positive { color: green; }
        .balance.negative { color: red; }
        .form-container { margin-top: 30px; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        input[type="text"] { width: calc(100% - 80px); padding: 10px; }
        button { padding: 10px 15px; background-color: #28a745; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>مرحباً, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="../src/logout.php">تسجيل الخروج</a>
        </div>

        <h2>قائمة الحسابات</h2>
        <div class="account-list">
            <?php if (empty($accounts)): ?>
                <p>لم تقم بإضافة أي حسابات بعد.</p>
            <?php else: ?>
                <?php foreach ($accounts as $account): ?>
                    <?php $balance = $account['total_credit'] - $account['total_debit']; ?>
                    <a href="account.php?id=<?php echo $account['id']; ?>">
                        <?php echo htmlspecialchars($account['name']); ?>
                        <span class="balance <?php echo $balance >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo number_format($balance, 2); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-container">
            <h3>إضافة حساب جديد</h3>
            <form action="../src/add_account.php" method="post">
                <input type="text" name="account_name" placeholder="اسم الحساب الجديد" required>
                <button type="submit">إضافة</button>
            </form>
        </div>
    </div>
</body>
</html>
