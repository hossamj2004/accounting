<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check for account ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$accountId = $_GET['id'];
$userId = $_SESSION['user_id'];
$userDbPath = __DIR__ . '/../database/user_' . $userId . '.sqlite';

// Function to get account details and transactions
function getAccountDetails($dbPath, $accountId) {
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get account details
        $stmt = $db->prepare("SELECT name, share_token FROM accounts WHERE id = :id");
        $stmt->bindParam(':id', $accountId);
        $stmt->execute();
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$account) {
            return null; // Account not found
        }

        // Get initial page of transactions
        $limit = 20; // Number of transactions per page
        $stmt = $db->prepare("SELECT * FROM transactions WHERE account_id = :id ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindParam(':id', $accountId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['account' => $account, 'transactions' => $transactions];

    } catch (PDOException $e) {
        die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    }
}

$data = getAccountDetails($userDbPath, $accountId);

if ($data === null) {
    // Redirect if account doesn't belong to the user or doesn't exist
    header('Location: dashboard.php');
    exit;
}

$accountName = $data['account']['name'];
$transactions = $data['transactions'];

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل حساب: <?php echo htmlspecialchars($accountName); ?></title>
    <style>
        body { font-family: sans-serif; direction: rtl; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 5px; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: right; }
        th { background-color: #f2f2f2; }
        .credit { color: green; }
        .debit { color: red; }
        .form-container { margin-top: 30px; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .form-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .form-row > * { flex: 1; }
        select, input, button { padding: 10px; }
        button { background-color: #007bff; color: white; border: none; cursor: pointer; }
        .share-container { margin-top: 20px; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f8f9fa; }
        .share-container input { width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>حساب: <?php echo htmlspecialchars($accountName); ?></h1>
            <a href="dashboard.php">العودة للوحة التحكم</a>
        </div>

        <div class="share-container">
            <h3>مشاركة الحساب مع العميل</h3>
            <?php if (!empty($data['account']['share_token'])): ?>
                <?php
                    // Construct the full URL
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                    $shareUrl = "{$protocol}://{$host}{$uri}/client_transaction.php?token=" . $data['account']['share_token'];
                ?>
                <p>رابط المشاركة:</p>
                <input type="text" value="<?php echo htmlspecialchars($shareUrl); ?>" readonly>
            <?php else: ?>
                <form action="../src/generate_share_link.php" method="post" style="display:inline;">
                    <input type="hidden" name="account_id" value="<?php echo $accountId; ?>">
                    <button type="submit">إنشاء رابط مشاركة</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="form-container">
            <h3>إضافة معاملة جديدة</h3>
            <form action="../src/add_transaction.php" method="post">
                <input type="hidden" name="account_id" value="<?php echo $accountId; ?>">
                <div class="form-row">
                    <select name="type" required>
                        <option value="credit">له (دائن)</option>
                        <option value="debit">عليه (مدين)</option>
                    </select>
                    <input type="number" name="amount" placeholder="المبلغ" step="0.01" required>
                </div>
                <div class="form-row">
                    <input type="text" name="description" placeholder="الوصف (اختياري)">
                </div>
                <button type="submit">إضافة المعاملة</button>
            </form>
        </div>

        <h3>كشف الحساب</h3>
        <table>
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>الوصف</th>
                    <th>الرصيد</th>
                </tr>
            </thead>
            <tbody id="transactions-tbody">
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="5">لا توجد معاملات لعرضها.</td></tr>
                <?php endif; ?>
                <!-- Transactions will be loaded here by PHP initially, and then by JS -->
            </tbody>
        </table>
        <div id="loader" style="display: none; text-align: center; padding: 20px;">
            <p>يتم تحميل المزيد من المعاملات...</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tbody = document.getElementById('transactions-tbody');
            const loader = document.getElementById('loader');
            const accountId = <?php echo $accountId; ?>;

            let transactions = <?php echo json_encode($transactions); ?>;
            let totalBalance = 0; // Will be fetched
            let offset = transactions.length;
            const limit = 20;
            let isLoading = false;
            let allLoaded = transactions.length < limit;

            function createTransactionRow(tx, balance) {
                const row = document.createElement('tr');
                const typeClass = tx.type === 'credit' ? 'credit' : 'debit';
                const typeText = tx.type === 'credit' ? 'له' : 'عليه';

                const descCell = document.createElement('td');
                descCell.textContent = tx.description || ''; // Use textContent to prevent XSS

                row.innerHTML = `
                    <td>${new Date(tx.created_at).toLocaleString('ar-EG')}</td>
                    <td class="${typeClass}">${typeText}</td>
                    <td>${parseFloat(tx.amount).toFixed(2)}</td>
                    <td></td>
                    <td class="balance-cell">${balance.toFixed(2)}</td>
                `;
                row.children[3].replaceWith(descCell);
                return row;
            }

            function renderTransactions() {
                tbody.innerHTML = '';
                let currentBalance = totalBalance;
                transactions.forEach(tx => {
                    const row = createTransactionRow(tx, currentBalance);
                    tbody.appendChild(row);
                    currentBalance -= (tx.type === 'credit' ? parseFloat(tx.amount) : -parseFloat(tx.amount));
                });
                updateBalanceColors();
            }

            function updateBalanceColors() {
                document.querySelectorAll('.balance-cell').forEach(cell => {
                    const balanceValue = parseFloat(cell.textContent);
                    cell.classList.toggle('positive', balanceValue >= 0);
                    cell.classList.toggle('negative', balanceValue < 0);
                });
            }

            async function initialize() {
                if (transactions.length === 0) {
                    if(allLoaded) tbody.innerHTML = '<tr><td colspan="5">لا توجد معاملات لعرضها.</td></tr>';
                    return;
                };

                try {
                    const response = await fetch(`../src/get_transactions.php?account_id=${accountId}&limit=0&offset=0`);
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);

                    totalBalance = data.total_balance;
                    renderTransactions();

                } catch (error) {
                    console.error('Error fetching initial balance:', error);
                    loader.innerHTML = 'فشل تحميل البيانات.';
                }
            }

            async function loadMoreTransactions() {
                if (isLoading || allLoaded) return;

                isLoading = true;
                loader.style.display = 'block';

                try {
                    const response = await fetch(`../src/get_transactions.php?account_id=${accountId}&limit=${limit}&offset=${offset}`);
                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.error);
                    }

                    if (data.transactions.length > 0) {
                        transactions.push(...data.transactions);
                        offset += data.transactions.length;
                        renderTransactions();
                    } else {
                        allLoaded = true;
                    }

                } catch (error) {
                    console.error('Error fetching transactions:', error);
                    loader.innerHTML = 'فشل تحميل المزيد من المعاملات.';
                } finally {
                    isLoading = false;
                    loader.style.display = 'none';
                }
            }

            window.addEventListener('scroll', () => {
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 100) {
                    loadMoreTransactions();
                }
            });

            initialize();
        });
    </script>
</body>
</html>
