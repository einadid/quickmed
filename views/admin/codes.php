<?php
/**
 * Admin - Generate Staff Signup Codes
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Staff Signup Codes - Admin';
$user = getCurrentUser();

// Handle code generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $roleId = intval($_POST['role_id']);
    $shopId = !empty($_POST['shop_id']) ? intval($_POST['shop_id']) : null;
    $expiryDays = intval($_POST['expiry_days']);
    
    $code = generateVerificationCode(10); // Ensure function exists in config.php
    $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiryDays days"));
    $createdBy = $_SESSION['user_id'];
    
    $query = "INSERT INTO signup_codes (code, role_id, shop_id, expires_at, created_by) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("siisi", $code, $roleId, $shopId, $expiresAt, $createdBy);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Code generated: <strong>$code</strong>";
    } else {
        $_SESSION['error'] = 'Failed to generate code: ' . $stmt->error;
    }
    
    header("Location: codes.php");
    exit();
}

// Get all codes
$codesQuery = "SELECT sc.*, r.display_name as role_name, s.name as shop_name, u.username as used_by_user
               FROM signup_codes sc
               JOIN roles r ON sc.role_id = r.id
               LEFT JOIN shops s ON sc.shop_id = s.id
               LEFT JOIN users u ON sc.used_by = u.id
               ORDER BY sc.created_at DESC";
$codes = $conn->query($codesQuery);

// Get roles and shops for dropdown
$roles = $conn->query("SELECT * FROM roles WHERE name != 'customer' ORDER BY id");
$shops = $conn->query("SELECT * FROM shops WHERE is_active = 1 ORDER BY name");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">🎫 Staff Signup Codes</h1>
            <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <!-- Generate Code Form -->
        <div class="card bg-lime-accent border-4 border-deep-green mb-8" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase">Generate New Code</h2>
            
            <form method="POST" class="grid md:grid-cols-4 gap-4">
                <div>
                    <label class="block font-bold mb-2 text-deep-green">Role *</label>
                    <select name="role_id" class="input border-4 border-deep-green" required>
                        <?php while ($role = $roles->fetch_assoc()): ?>
                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['display_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-2 text-deep-green">Shop (Optional)</label>
                    <select name="shop_id" class="input border-4 border-deep-green">
                        <option value="">No Shop</option>
                        <?php while ($shop = $shops->fetch_assoc()): ?>
                            <option value="<?= $shop['id'] ?>"><?= htmlspecialchars($shop['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-2 text-deep-green">Expires In *</label>
                    <select name="expiry_days" class="input border-4 border-deep-green" required>
                        <option value="7">7 Days</option>
                        <option value="14">14 Days</option>
                        <option value="30" selected>30 Days</option>
                        <option value="60">60 Days</option>
                        <option value="90">90 Days</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" name="generate" class="btn btn-primary w-full">
                        ✨ Generate Code
                    </button>
                </div>
            </form>
        </div>

        <!-- Codes Table -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                All Generated Codes
            </h2>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Role</th>
                            <th>Shop</th>
                            <th>Status</th>
                            <th>Used By</th>
                            <th>Expires</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($code = $codes->fetch_assoc()): ?>
                            <tr>
                                <td class="font-mono font-bold text-lg"><?= htmlspecialchars($code['code']) ?></td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($code['role_name']) ?></span></td>
                                <td><?= $code['shop_name'] ? htmlspecialchars($code['shop_name']) : '-' ?></td>
                                <td>
                                    <?php if ($code['is_used']): ?>
                                        <span class="badge badge-success">✅ Used</span>
                                    <?php elseif (strtotime($code['expires_at']) < time()): ?>
                                        <span class="badge badge-danger">❌ Expired</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">⏳ Active</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $code['used_by_user'] ?? '-' ?></td>
                                <td><?= date('M d, Y', strtotime($code['expires_at'])) ?></td>
                                <td><?= timeAgo($code['created_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>