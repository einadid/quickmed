<?php
/**
 * Admin - Manage Users
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Manage Users - Admin';

// Handle Ban/Unban
if (isset($_GET['toggle_ban'])) {
    $userId = intval($_GET['toggle_ban']);
    $query = "UPDATE users SET is_banned = NOT is_banned WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User status updated';
        logAudit('USER_BAN_TOGGLE', 'users', $userId);
    }
    
    redirect('users.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    $query = "DELETE FROM users WHERE id = ? AND id != ?";
    $stmt = $conn->prepare($query);
    $adminId = $_SESSION['user_id'];
    $stmt->bind_param("ii", $userId, $adminId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User deleted';
        logAudit('USER_DELETE', 'users', $userId);
    }
    
    redirect('users.php');
}

// Filters
$roleFilter = intval($_GET['role'] ?? 0);
$search = clean($_GET['search'] ?? '');

// Build query
$whereConditions = ["1=1"];
$params = [];
$types = "";

if ($roleFilter > 0) {
    $whereConditions[] = "u.role_id = ?";
    $params[] = $roleFilter;
    $types .= "i";
}

if ($search) {
    $whereConditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

$whereClause = implode(" AND ", $whereConditions);

$query = "SELECT u.*, r.display_name as role_name, s.name as shop_name
          FROM users u
          JOIN roles r ON u.role_id = r.id
          LEFT JOIN shops s ON u.shop_id = s.id
          WHERE $whereClause
          ORDER BY u.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();

// Get roles
$roles = $conn->query("SELECT * FROM roles ORDER BY id");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">👥 Manage Users</h1>
            <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <div class="card bg-white border-4 border-deep-green mb-8" data-aos="fade-up">
            <form method="GET" class="grid md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <input 
                        type="text" 
                        name="search" 
                        class="input border-4 border-deep-green" 
                        placeholder="Search by name, email, or phone..."
                        value="<?= htmlspecialchars($search) ?>"
                    >
                </div>
                
                <div>
                    <select name="role" class="input border-4 border-deep-green">
                        <option value="0">All Roles</option>
                        <?php 
                        $roles->data_seek(0);
                        while ($role = $roles->fetch_assoc()): 
                        ?>
                            <option value="<?= $role['id'] ?>" <?= $roleFilter == $role['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['display_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1">🔍 Filter</button>
                    <a href="<?= SITE_URL ?>/views/admin/users.php" class="btn btn-outline">Clear</a>
                </div>
            </form>
        </div>

        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Shop</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr class="<?= $user['is_banned'] ? 'bg-red-50' : '' ?>">
                                <td class="font-mono">#<?= $user['id'] ?></td>
                                
                                <td class="font-bold">
                                    <a href="user-details.php?id=<?= $user['id'] ?>" class="hover:text-lime-600 hover:underline flex items-center gap-2">
                                        <?= htmlspecialchars($user['full_name']) ?> 
                                        <span class="text-xs text-gray-400">↗</span>
                                    </a>
                                </td>

                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['phone']) ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= htmlspecialchars($user['role_name']) ?>
                                    </span>
                                </td>
                                <td><?= $user['shop_name'] ? htmlspecialchars($user['shop_name']) : '-' ?></td>
                                <td class="font-bold text-lime-accent">⭐ <?= $user['points'] ?></td>
                                <td>
                                    <?php if ($user['is_banned']): ?>
                                        <span class="badge badge-danger">Banned</span>
                                    <?php elseif ($user['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-sm"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="flex gap-2">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="?toggle_ban=<?= $user['id'] ?>" class="btn btn-outline btn-sm border-yellow-500 text-yellow-600">
                                                <?= $user['is_banned'] ? '✅ Unban' : '🚫 Ban' ?>
                                            </a>
                                            <button onclick="deleteUser(<?= $user['id'] ?>)" class="btn btn-outline btn-sm border-red-500 text-red-600">
                                                🗑️
                                            </button>
                                        <?php else: ?>
                                            <span class="badge badge-success">You</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
async function deleteUser(id) {
    const result = await Swal.fire({
        title: 'Delete User?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete!'
    });

    if (result.isConfirmed) {
        window.location.href = '?delete=' + id;
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>