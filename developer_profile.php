<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'developer') { header("Location: dev_auth.php"); exit(); }
$user_id = $_SESSION['user_id'];
$developer_id = $_SESSION['developer_id'];

$stmt_dev = $conn->prepare("SELECT u.name, u.email, u.created_at, d.bio, d.portfolio_link FROM users u JOIN developers d ON u.id = d.user_id WHERE u.id = ?");
$stmt_dev->bind_param("i", $user_id);
$stmt_dev->execute();
$developer = $stmt_dev->get_result()->fetch_assoc();
$stmt_dev->close();

$platform_cut = 0.25;

$sql_total_revenue = "SELECT SUM(p.price_paid) as total FROM purchases p JOIN games g ON p.game_id = g.id WHERE g.developer_id = ?";
$stmt_total = $conn->prepare($sql_total_revenue);
$stmt_total->bind_param("i", $developer_id);
$stmt_total->execute();
$total_revenue = $stmt_total->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_total->close();


$sql_current_balance = "SELECT SUM(p.price_paid) as balance FROM purchases p JOIN games g ON p.game_id = g.id WHERE g.developer_id = ? AND p.payout_id IS NULL";
$stmt_balance = $conn->prepare($sql_current_balance);
$stmt_balance->bind_param("i", $developer_id);
$stmt_balance->execute();
$current_balance_raw = $stmt_balance->get_result()->fetch_assoc()['balance'] ?? 0;
$current_balance = $current_balance_raw * (1 - $platform_cut); 
$stmt_balance->close();

$sql_payouts = "SELECT SUM(amount) as total_paid FROM payouts WHERE developer_id = ?";
$stmt_payouts = $conn->prepare($sql_payouts);
$stmt_payouts->bind_param("i", $developer_id);
$stmt_payouts->execute();
$total_paid_out = $stmt_payouts->get_result()->fetch_assoc()['total_paid'] ?? 0;
$stmt_payouts->close();


$sql_payout_history = "SELECT amount, payout_date FROM payouts WHERE developer_id = ? ORDER BY payout_date DESC";
$stmt_payout_history = $conn->prepare($sql_payout_history);
$stmt_payout_history->bind_param("i", $developer_id);
$stmt_payout_history->execute();
$payout_history = $stmt_payout_history->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_payout_history->close();

$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Profile - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="dev_profile.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <!-- Dev Profile Page Starting -->
    <div class="profile-container">
        <header class="profile-header">
            <h1>Developer Hub</h1>
            <p>Manage your public identity, finances, and account settings.</p>
        </header>
        <div class="profile-content">
            <nav class="profile-tabs">
                <button class="tab-link active" data-tab="payouts">Finances</button>
                <button class="tab-link" data-tab="profile-details">Public Profile</button>
                <button class="tab-link" data-tab="settings">Account Settings</button>
            </nav>
            <div class="tab-content-area">
                <div id="payouts" class="tab-pane active">
                    <div class="stats-grid">
                        <div class="stat-card"><h3>Lifetime Revenue</h3><span class="stat-value">$<?php echo number_format($total_revenue, 2); ?></span><small>Total sales value before fees</small></div>
                        <div class="stat-card"><h3>Total Paid Out</h3><span class="stat-value">$<?php echo number_format($total_paid_out, 2); ?></span><small>All previous withdrawals</small></div>
                        <div class="stat-card primary"><h3>Current Balance</h3><span class="stat-value">$<?php echo number_format($current_balance, 2); ?></span><small>Available for withdrawal</small></div>
                    </div>
                    <div class="profile-section">
                        <h3>Withdraw Funds</h3>
                        <form id="withdraw-form">
                            <div id="withdraw-form-message" class="form-message"></div>
                            <input type="hidden" name="amount" value="<?php echo $current_balance; ?>">
                            <div class="form-group"><label>Bank Account (IBAN)</label><input type="text" placeholder="PK00 XXXX 0000 0000 0000 0000" required></div>
                            <button type="submit" class="action-button" <?php if ($current_balance < 1) echo 'disabled'; ?>>Withdraw $<?php echo number_format($current_balance, 2); ?></button>
                        </form>
                    </div>
                    <div class="profile-section">
                        <h3>Payout History</h3>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead><tr><th>Date</th><th>Amount</th></tr></thead>
                                <tbody>
                                    <?php if(!empty($payout_history)): foreach($payout_history as $payout): ?>
                                    <tr><td><?php echo date("M j, Y", strtotime($payout['payout_date'])); ?></td><td><strong>$<?php echo number_format($payout['amount'], 2); ?></strong></td></tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="2" class="no-items-message">You have no payout history.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="profile-details" class="tab-pane">
                    <div class="profile-section">
                        <h3>Public Developer Profile</h3>
                        <p class="section-description">This information is visible to players on your game pages.</p>
                        <form id="edit-profile-form">
                            <div id="profile-form-message" class="form-message"></div>
                            <div class="form-group"><label for="studio_name">Studio Name</label><input type="text" id="studio_name" name="studio_name" value="<?php echo htmlspecialchars($developer['name']); ?>" required></div>
                            <div class="form-group"><label for="portfolio_link">Portfolio / Website Link</label><input type="url" id="portfolio_link" name="portfolio_link" value="<?php echo htmlspecialchars($developer['portfolio_link']); ?>" placeholder="https://yourstudio.com"></div>
                            <div class="form-group"><label for="bio">Public Bio</label><textarea id="bio" name="bio" rows="6" required><?php echo htmlspecialchars($developer['bio']); ?></textarea></div>
                            <button type="submit" class="action-button">Save Profile Changes</button>
                        </form>
                    </div>
                </div>
                <div id="settings" class="tab-pane">
                    <div class="profile-section">
                        <h3>Account Settings</h3>
                        <div class="info-grid">
                            <div class="info-item"><label>Registered Email</label><span><?php echo htmlspecialchars($developer['email']); ?></span><small>Email cannot be changed for security reasons.</small></div>
                        </div>
                    </div>
                    <div class="profile-section">
                        <h3>Change Password</h3>
                        <form id="change-password-form">
                            <div id="password-form-message" class="form-message"></div>
                            <div class="form-group"><label for="current_password">Current Password</label><input type="password" id="current_password" name="current_password" required></div>
                            <div class="form-group"><label for="new_password">New Password</label><input type="password" id="new_password" name="new_password" required></div>
                            <div class="form-group"><label for="confirm_password">Confirm New Password</label><input type="password" id="confirm_password" name="confirm_password" required></div>
                            <button type="submit" class="action-button">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Dev Profile Page Ending -->
    <script src="dev_profile.js"></script>
    <?php include "footer.php" ?>
</body>
</html>