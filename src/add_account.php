<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountName = $_POST['account_name'];

    if (empty($accountName)) {
        die('اسم الحساب لا يمكن أن يكون فارغًا.');
    }

    $userId = $_SESSION['user_id'];
    $userDbPath = __DIR__ . '/../database/user_' . $userId . '.sqlite';

    try {
        $db = new PDO('sqlite:' . $userDbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("INSERT INTO accounts (name) VALUES (:name)");
        $stmt->bindParam(':name', $accountName);
        $stmt->execute();

        // Redirect back to dashboard
        header('Location: ../public/dashboard.php');
        exit;

    } catch (PDOException $e) {
        die("خطأ في قاعدة البيانات: " . $e->getMessage());
    }
} else {
    // Redirect if not a POST request
    header('Location: ../public/dashboard.php');
    exit;
}
?>
