<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die('الرجاء إدخال اسم مستخدم وكلمة مرور.');
    }

    $mainDbPath = __DIR__ . '/../database/users.sqlite';

    try {
        $mainDb = new PDO('sqlite:' . $mainDbPath);
        $mainDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Find the user
        $stmt = $mainDb->prepare("SELECT id, password FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;

            // Redirect to dashboard
            header('Location: ../public/dashboard.php');
            exit;
        } else {
            // Invalid credentials
            header('Location: ../public/login.php?error=invalid_credentials');
            exit;
        }

    } catch (PDOException $e) {
        die("خطأ في قاعدة البيانات: " . $e->getMessage());
    }
}
?>
