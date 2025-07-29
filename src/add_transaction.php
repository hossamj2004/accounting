<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    if (!isset($_POST['account_id'], $_POST['type'], $_POST['amount']) || !is_numeric($_POST['account_id']) || !is_numeric($_POST['amount'])) {
        die('بيانات غير صالحة.');
    }

    $accountId = (int)$_POST['account_id'];
    $type = $_POST['type'];
    $amount = (float)$_POST['amount'];
    $description = $_POST['description'] ?? ''; // Optional description

    // Validate type
    if ($type !== 'credit' && $type !== 'debit') {
        die('نوع معاملة غير صالح.');
    }

    if ($amount <= 0) {
        die('المبلغ يجب أن يكون أكبر من صفر.');
    }

    $userId = $_SESSION['user_id'];
    $userDbPath = __DIR__ . '/../database/user_' . $userId . '.sqlite';

    try {
        $db = new PDO('sqlite:' . $userDbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Security Check: Verify the account belongs to the user
        $stmt = $db->prepare("SELECT id FROM accounts WHERE id = :account_id");
        $stmt->bindParam(':account_id', $accountId);
        $stmt->execute();
        if (!$stmt->fetch()) {
            // Account does not exist or belong to this user
            header('Location: ../public/dashboard.php');
            exit;
        }

        // Insert the new transaction
        $stmt = $db->prepare(
            "INSERT INTO transactions (account_id, type, amount, description) VALUES (:account_id, :type, :amount, :description)"
        );
        $stmt->bindParam(':account_id', $accountId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->execute();

        // Redirect back to the account page
        header('Location: ../public/account.php?id=' . $accountId);
        exit;

    } catch (PDOException $e) {
        die("خطأ في قاعدة البيانات: " . $e->getMessage());
    }
} else {
    header('Location: ../public/dashboard.php');
    exit;
}
?>
