<?php
// A simple routing and request handling mechanism
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate input (simple validation)
    if (empty($username) || empty($password)) {
        die('الرجاء إدخال اسم مستخدم وكلمة مرور.');
    }

    // Path to the main users database
    $mainDbPath = __DIR__ . '/../database/users.sqlite';
    $userDbDir = __DIR__ . '/../database/';

    try {
        // Connect to the main database
        $mainDb = new PDO('sqlite:' . $mainDbPath);
        $mainDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create users table if it doesn't exist
        $mainDb->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL
        )");

        // Check if username already exists
        $stmt = $mainDb->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->fetch()) {
            die('اسم المستخدم موجود بالفعل. الرجاء اختيار اسم آخر.');
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $mainDb->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();

        // Get the new user's ID
        $userId = $mainDb->lastInsertId();

        // Create a dedicated database for the user
        $userDbPath = $userDbDir . 'user_' . $userId . '.sqlite';
        $userDb = new PDO('sqlite:' . $userDbPath);
        $userDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create tables in the user's database
        $userDb->exec("CREATE TABLE IF NOT EXISTS accounts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            share_token TEXT
        )");

        $userDb->exec("CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_id INTEGER NOT NULL,
            type TEXT NOT NULL, -- 'debit' or 'credit'
            amount REAL NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES accounts(id)
        )");

        // Redirect to login page
        header('Location: ../public/login.php?registration=success');
        exit;

    } catch (PDOException $e) {
        die("خطأ في قاعدة البيانات: " . $e->getMessage());
    }
}
?>
