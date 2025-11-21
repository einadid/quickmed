<?php
/**
 * QuickMed - User Profile Management
 * All roles can edit their profile here
 */

require_once 'config.php';

requireLogin();

$pageTitle = 'My Profile - QuickMed';
$user = getCurrentUser();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = clean($_POST['full_name']);
    $phone = clean($_POST['phone']);
    $address = clean($_POST['address'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    $errors = [];
    
    // Validate
    if (empty($fullName)) $errors[] = 'Full name is required';
    if (empty($phone)) $errors[] = 'Phone is required';
    
    // Handle profile image upload
    $profileImage = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadResult = uploadFile($_FILES['profile_image'], $uploadDir, ['jpg', 'jpeg', 'png']);
        if ($uploadResult['success']) {
            $profileImage = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['message'];
        }
    }
    
    // Handle password change
    $passwordUpdate = '';
    $passwordParams = [];
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Current password required to set new password';
        } else {
            // Verify current password
            $checkQuery = "SELECT password_hash FROM users WHERE id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("i", $user['id']);
            $checkStmt->execute();
            $currentHash = $checkStmt->get_result()->fetch_assoc()['password_hash'];
            
            if (!password_verify($currentPassword, $currentHash)) {
                $errors[] = 'Current password is incorrect';
            } else {
                if (strlen($newPassword) < MIN_PASSWORD_LENGTH) {
                    $errors[] = 'New password must be at least ' . MIN_PASSWORD_LENGTH . ' characters';
                } else {
                    $passwordUpdate = ', password_hash = ?';
                    $passwordParams[] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
            }
        }
    }
    
    if (empty($errors)) {
        // Build update query
        $query = "UPDATE users SET full_name = ?, phone = ?, address = ?";
        $types = "sss";
        $params = [$fullName, $phone, $address];
        
        if ($profileImage) {
            $query .= ", profile_image = ?";
            $types .= "s";
            $params[] = $profileImage;
        }
        
        if (!empty($passwordParams)) {
            $query .= $passwordUpdate;
            $types .= "s";
            $params = array_merge($params, $passwordParams);
        }
        
        $query .= " WHERE id = ?";
        $types .= "i";
        $params[] = $user['id'];
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            logAudit('PROFILE_UPDATE', 'users', $user['id']);
            $_SESSION['success'] = 'Profile updated successfully!';
            redirect('profile.php');
        } else {
            $errors[] = 'Failed to update profile';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

// Refresh user data
$user = getCurrentUser();

include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green mb-4 font-mono uppercase">
                👤 My Profile
            </h1>
            <div class="bg-lime-accent inline-block px-6 py-3 border-4 border-deep-green">
                <p class="text-deep-green font-bold text-xl">
                    <?= htmlspecialchars($user['role_display']) ?>
                </p>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Profile Sidebar -->
            <div class="md:col-span-1">
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-right">
                    <!-- Profile Image -->
                    <div class="text-center mb-6">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img 
                                src="<?= SITE_URL ?>/uploads/profiles/<?= $user['profile_image'] ?>" 
                                alt="Profile"
                                class="w-40 h-40 mx-auto object-cover border-4 border-deep-green bg-gray-100"
                            >
                        <?php else: ?>
                            <div class="w-40 h-40 mx-auto bg-lime-accent border-4 border-deep-green flex items-center justify-center text-6xl font-bold text-deep-green">
                                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-2xl font-bold text-deep-green text-center mb-2">
                        <?= htmlspecialchars($user['full_name']) ?>
                    </h3>
                    
                    <p class="text-center text-gray-600 mb-4">
                        @<?= htmlspecialchars($user['username']) ?>
                    </p>

                    <div class="bg-off-white p-4 border-2 border-gray-300 mb-4">
                        <p class="text-sm font-bold text-gray-600 mb-2">Member ID</p>
                        <p class="text-xl font-mono font-bold text-deep-green break-all">
                            <?= htmlspecialchars($user['member_id'] ?? 'N/A') ?>
                        </p>
                    </div>

                    <?php if ($user['role_name'] === 'customer'): ?>
                        <div class="bg-lime-accent p-4 border-2 border-deep-green text-center">
                            <p class="text-sm font-bold text-deep-green mb-1">Loyalty Points</p>
                            <p class="text-4xl font-bold text-deep-green">⭐ <?= $user['points'] ?></p>
                            <p class="text-xs text-gray-700 mt-1">= ৳<?= floor($user['points'] / 100) * 10 ?> discount</p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-6 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-bold"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Joined:</span>
                            <span class="font-bold"><?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                        </div>
                        <?php if ($user['last_login']): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Login:</span>
                                <span class="font-bold"><?= timeAgo($user['last_login']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="md:col-span-2">
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-left">
                    <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                        ✏️ Edit Profile
                    </h2>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                        <!-- Profile Image Upload -->
                        <div class="mb-6">
                            <label class="block font-bold mb-2 text-deep-green text-lg">Profile Picture</label>
                            <input 
                                type="file" 
                                name="profile_image" 
                                accept="image/*"
                                class="input border-4 border-deep-green"
                            >
                            <p class="text-sm text-gray-600 mt-1">Upload JPG, JPEG, or PNG (max 5MB)</p>
                        </div>

                        <!-- Full Name -->
                        <div class="mb-6">
                            <label class="block font-bold mb-2 text-deep-green text-lg">Full Name *</label>
                            <input 
                                type="text" 
                                name="full_name" 
                                class="input border-4 border-deep-green" 
                                value="<?= htmlspecialchars($user['full_name']) ?>"
                                required
                            >
                        </div>

                        <!-- Phone -->
                        <div class="mb-6">
                            <label class="block font-bold mb-2 text-deep-green text-lg">Phone Number *</label>
                            <input 
                                type="tel" 
                                name="phone" 
                                class="input border-4 border-deep-green" 
                                value="<?= htmlspecialchars($user['phone']) ?>"
                                required
                            >
                        </div>

                        <!-- Address (for customers) -->
                        <?php if ($user['role_name'] === 'customer'): ?>
                            <div class="mb-6">
                                <label class="block font-bold mb-2 text-deep-green text-lg">Delivery Address</label>
                                <textarea 
                                    name="address" 
                                    rows="3"
                                    class="input border-4 border-deep-green"
                                    placeholder="House/Flat, Road, Area, City"
                                ><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                        <?php endif; ?>

                        <!-- Change Password Section -->
                        <div class="bg-yellow-50 border-4 border-yellow-500 p-6 mb-6">
                            <h3 class="text-xl font-bold text-yellow-800 mb-4 uppercase">🔐 Change Password</h3>
                            
                            <div class="mb-4">
                                <label class="block font-bold mb-2 text-yellow-800">Current Password</label>
                                <input 
                                    type="password" 
                                    name="current_password" 
                                    class="input border-4 border-yellow-600"
                                    placeholder="Enter current password"
                                >
                            </div>

                            <div class="mb-4">
                                <label class="block font-bold mb-2 text-yellow-800">New Password</label>
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    class="input border-4 border-yellow-600"
                                    placeholder="Enter new password (min <?= MIN_PASSWORD_LENGTH ?> characters)"
                                    minlength="<?= MIN_PASSWORD_LENGTH ?>"
                                >
                            </div>

                            <p class="text-sm text-yellow-700">
                                Leave blank if you don't want to change password
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" name="update_profile" class="btn btn-primary w-full text-xl py-4">
                            💾 Update Profile
                        </button>
                    </form>
                </div>

                <!-- Additional Info Card -->
                <?php if ($user['role_name'] === 'customer'): ?>
                    <div class="card bg-lime-accent border-4 border-deep-green mt-8" data-aos="fade-up">
                        <h3 class="text-xl font-bold text-deep-green mb-4 uppercase">📊 Your Statistics</h3>
                        
                        <?php
                        // Get customer stats
                        $statsQuery = "SELECT 
                            (SELECT COUNT(*) FROM orders WHERE user_id = ?) as total_orders,
                            (SELECT SUM(total_amount) FROM orders WHERE user_id = ?) as total_spent,
                            (SELECT COUNT(*) FROM prescriptions WHERE user_id = ?) as prescriptions_uploaded
                            FROM dual";
                        $statsStmt = $conn->prepare($statsQuery);
                        $statsStmt->bind_param("iii", $user['id'], $user['id'], $user['id']);
                        $statsStmt->execute();
                        $stats = $statsStmt->get_result()->fetch_assoc();
                        ?>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-white border-2 border-deep-green p-4 text-center">
                                <p class="text-3xl font-bold text-deep-green"><?= $stats['total_orders'] ?? 0 ?></p>
                                <p class="text-sm font-bold mt-1">Total Orders</p>
                            </div>
                            <div class="bg-white border-2 border-deep-green p-4 text-center">
                                <p class="text-2xl font-bold text-deep-green">৳<?= number_format($stats['total_spent'] ?? 0, 0) ?></p>
                                <p class="text-sm font-bold mt-1">Total Spent</p>
                            </div>
                            <div class="bg-white border-2 border-deep-green p-4 text-center">
                                <p class="text-3xl font-bold text-deep-green"><?= $stats['prescriptions_uploaded'] ?? 0 ?></p>
                                <p class="text-sm font-bold mt-1">Prescriptions</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>