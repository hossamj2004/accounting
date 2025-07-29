<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['account_id']) || !is_numeric($_POST['account_id'])) {
        die('معرف حساب غير صالح.');
    }

    $accountId = (int)$_POST['account_id'];
    $userId = $_SESSION['user_id'];
    $userDbPath = __DIR__ . '/../database/user_' . $userId . '.sqlite';

    try {
        $db = new PDO('sqlite:' . $userDbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verify account ownership
        $stmt = $db->prepare("SELECT id FROM accounts WHERE id = :account_id");
        $stmt->bindParam(':account_id', $accountId);
        $stmt->execute();
        if (!$stmt->fetch()) {
            header('Location: ../public/dashboard.php');
            exit;
        }

        // Generate a unique token
        $token = bin2hex(random_bytes(16));

        // Store the token in the database
        $stmt = $db->prepare("UPDATE accounts SET share_token = :token WHERE id = :id");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $accountId);
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
