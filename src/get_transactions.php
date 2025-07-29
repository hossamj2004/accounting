<?php
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Authentication required.']);
    http_response_code(401);
    exit;
}

// Validate input parameters
if (!isset($_GET['account_id'], $_GET['limit'], $_GET['offset']) || !is_numeric($_GET['account_id']) || !is_numeric($_GET['limit']) || !is_numeric($_GET['offset'])) {
    echo json_encode(['error' => 'Invalid parameters.']);
    http_response_code(400);
    exit;
}

$accountId = (int)$_GET['account_id'];
$limit = (int)$_GET['limit'];
$offset = (int)$_GET['offset'];
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
        echo json_encode(['error' => 'Account not found or access denied.']);
        http_response_code(404);
        exit;
    }

    // Fetch paginated transactions
    $stmt = $db->prepare("SELECT * FROM transactions WHERE account_id = :account_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Also get total transaction count to calculate running balance correctly on the client
    $totalStmt = $db->prepare("SELECT type, amount FROM transactions WHERE account_id = :account_id");
    $totalStmt->bindParam(':account_id', $accountId);
    $totalStmt->execute();
    $allTransactions = $totalStmt->fetchAll(PDO::FETCH_ASSOC);

    $totalBalance = 0;
    foreach ($allTransactions as $tx) {
        $totalBalance += ($tx['type'] === 'credit' ? (float)$tx['amount'] : -(float)$tx['amount']);
    }

    echo json_encode(['transactions' => $transactions, 'total_balance' => $totalBalance]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error.']);
    http_response_code(500);
    // In a real app, you would log the error message, not expose it.
    // error_log($e->getMessage());
}
?>
