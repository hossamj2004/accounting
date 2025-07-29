<?php

function findAccountByToken($token) {
    if (empty($token)) {
        return null;
    }

    $mainDbPath = __DIR__ . '/../database/users.sqlite';
    $userDbDir = __DIR__ . '/../database/';

    try {
        if (!file_exists($mainDbPath)) {
            return null;
        }
        $mainDb = new PDO('sqlite:' . $mainDbPath);
        $mainDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $users = $mainDb->query("SELECT id FROM users")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $user) {
            $userId = $user['id'];
            $userDbPath = $userDbDir . 'user_' . $userId . '.sqlite';

            if (file_exists($userDbPath)) {
                $userDb = new PDO('sqlite:' . $userDbPath);
                $userDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $userDb->prepare("SELECT id, name FROM accounts WHERE share_token = :token");
                $stmt->bindParam(':token', $token);
                $stmt->execute();
                $account = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($account) {
                    $account['user_id'] = $userId; // Manually add user_id to the result array
                    return $account;
                }
            }
        }
    } catch (PDOException $e) {
        return null;
    }

    return null;
}
?>
