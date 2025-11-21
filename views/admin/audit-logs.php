<?php
/**
 * Admin - Audit Logs (System Activity)
 * Features: Search, Role Filter, Date Filter
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Audit Logs - System Activity';

// --- 1. CAPTURE FILTERS ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? intval($_GET['role']) : '';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : ''; // 'today' or empty

// --- 2. PAGINATION SETUP ---
$page = intval($_GET['page'] ?? 1);
$perPage = 50;
$offset = ($page - 1) * $perPage;

// --- 3. BUILD QUERY DYNAMICALLY ---
$whereClauses = ["1=1"]; // Default true condition
$params = [];
$types = "";

// Search Condition
if (!empty($search)) {
    $whereClauses[] = "(u.username LIKE ? OR al.action LIKE ? OR al.table_name LIKE ? OR u.full_name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ssss";
}

// Role Filter
if (!empty($roleFilter)) {
    $whereClauses[] = "u.role_id = ?";
    $params[] = $roleFilter;
    $types .= "i";
}

// Date Filter (Today)
if ($dateFilter === 'today') {
    $whereClauses[] = "DATE(al.created_at) = CURDATE()";
}

$whereSql = implode(" AND ", $whereClauses);

// --- 4. FETCH DATA ---
$query = "SELECT al.*, u.username, u.full_name, u.role_id, r.display_name as role_name
          FROM audit_logs al
          LEFT JOIN users u ON al.user_id = u.id
          LEFT JOIN roles r ON u.role_id = r.id
          WHERE $whereSql
          ORDER BY al.created_at DESC
          LIMIT ? OFFSET ?";

// Add Limit & Offset to params
$params[] = $perPage;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result();

// --- 5. COUNT TOTAL (For Pagination) ---
// We need to remove LIMIT/OFFSET from params for count query
$countParams = array_slice($params, 0, -2); 
$countTypes = substr($types, 0, -2);

$countQuery = "SELECT COUNT(*) as total 
               FROM audit_logs al
               LEFT JOIN users u ON al.user_id = u.id 
               WHERE $whereSql";

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalLogs = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalLogs / $perPage);

// --- 6. FETCH ROLES (For Dropdown) ---
$roles = $conn->query("SELECT * FROM roles ORDER BY id ASC");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4" data-aos="fade-down">
            <div>
                <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">🛡️ Audit Logs</h1>
                <p class="text-gray-600">Track system activities & security</p>
            </div>
            <div class="flex gap-2">
                <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
            </div>
        </div>

        <div class="card bg-white border-4 border-deep-green mb-8 p-4" data-aos="fade-up">
            <form method="GET" class="flex flex-col md:flex-row gap-4 items-center">
                
                <div class="relative flex-1 w-full">
                    <span class="absolute left-3 top-3 text-gray-400">🔍</span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           class="w-full pl-10 p-3 border-2 border-gray-200 rounded focus:border-deep-green outline-none font-mono text-sm transition-colors" 
                           placeholder="Search by user, action, or table...">
                </div>

                <div class="w-full md:w-48">
                    <select name="role" class="w-full p-3 border-2 border-gray-200 rounded focus:border-deep-green outline-none font-bold text-sm cursor-pointer bg-white">
                        <option value="">All Roles</option>
                        <?php while ($r = $roles->fetch_assoc()): ?>
                            <option value="<?= $r['id'] ?>" <?= $roleFilter == $r['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['display_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="btn bg-deep-green text-white px-6 hover:bg-opacity-90 w-full md:w-auto">
                        Filter
                    </button>

                    <?php if ($dateFilter === 'today'): ?>
                        <a href="audit-logs.php" class="btn bg-red-500 text-white px-6 hover:bg-red-600 w-full md:w-auto text-center">
                            ✕ Clear Date
                        </a>
                    <?php else: ?>
                        <button type="submit" name="date" value="today" class="btn bg-lime-accent text-deep-green font-bold border-2 border-lime-accent hover:bg-white hover:border-deep-green w-full md:w-auto">
                            📅 Today
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($search) || !empty($roleFilter)): ?>
                        <a href="audit-logs.php" class="btn bg-gray-200 text-gray-600 px-4 hover:bg-gray-300 text-center">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="100">
            <div class="overflow-x-auto">
                <table class="table w-full text-sm text-left">
                    <thead class="bg-deep-green text-white">
                        <tr>
                            <th class="p-3">User / Role</th>
                            <th class="p-3">Action</th>
                            <th class="p-3">Details (Table/ID)</th>
                            <th class="p-3">IP / Meta</th>
                            <th class="p-3">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($logs->num_rows === 0): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500 flex flex-col items-center">
                                    <span class="text-4xl mb-2">📭</span>
                                    No logs found matching your criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($log = $logs->fetch_assoc()): ?>
                                <tr class="hover:bg-lime-50 transition duration-150">
                                    <td class="p-3">
                                        <div class="font-bold text-gray-800">
                                            <?= htmlspecialchars($log['username'] ?? 'Guest') ?>
                                        </div>
                                        <div class="text-xs text-gray-500 mb-1">
                                            <?= htmlspecialchars($log['full_name'] ?? '') ?>
                                        </div>
                                        <?php if ($log['role_name']): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase
                                                <?= $log['role_id'] == 3 ? 'bg-red-50 text-red-600 border-red-200' : // Admin 
                                                   ($log['role_id'] == 2 ? 'bg-blue-50 text-blue-600 border-blue-200' : // Salesman
                                                   'bg-gray-50 text-gray-600 border-gray-200') // Customer/Others
                                                ?>">
                                                <?= htmlspecialchars($log['role_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 align-top">
                                        <span class="font-mono font-bold text-deep-green block">
                                            <?= htmlspecialchars($log['action']) ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs text-gray-600 align-top">
                                        <span class="font-mono bg-gray-100 px-1 rounded">
                                            <?= htmlspecialchars($log['table_name']) ?>
                                        </span>
                                        <span class="ml-1 text-gray-400">#<?= $log['record_id'] ?></span>
                                        <?php if(!empty($log['old_value']) || !empty($log['new_value'])): ?>
                                            <div class="mt-1 text-[10px] text-gray-400 italic">Data changed</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 align-top">
                                        <span class="font-mono text-xs bg-gray-50 px-2 py-1 rounded border border-gray-200">
                                            <?= htmlspecialchars($log['ip_address']) ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500 text-xs font-mono align-top whitespace-nowrap">
                                        <?= date('d M', strtotime($log['created_at'])) ?>
                                        <br>
                                        <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 bg-gray-50 border-t text-xs text-gray-500 flex justify-between items-center">
                <span>Showing <?= $logs->num_rows ?> records</span>
                <span>Total: <?= $totalLogs ?></span>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center gap-2 mt-8">
                <?php 
                    $queryParams = $_GET; 
                    unset($queryParams['page']); 
                    $queryString = http_build_query($queryParams);
                ?>

                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&<?= $queryString ?>" class="btn btn-outline bg-white">← Prev</a>
                <?php endif; ?>
                
                <span class="px-4 py-2 font-bold bg-deep-green text-white border border-deep-green rounded">
                    <?= $page ?> / <?= $totalPages ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&<?= $queryString ?>" class="btn btn-outline bg-white">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>