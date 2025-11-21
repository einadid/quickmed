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

// Ensure role_display exists for the header
if (!isset($user['role_display'])) {
    $user['role_display'] = isset($user['role_name']) 
        ? ucwords(str_replace('_', ' ', $user['role_name'])) 
        : 'User';
}

include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-4xl mx-auto">
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
            
            <div class="md:col-span-1">
                <div class="card bg-white border-4 border-deep-green relative overflow-hidden" data-aos="fade-right">
                    
                    <div class="absolute top-4 right-0 bg-lime-accent text-deep-green px-4 py-1 font-bold text-sm border-l-2 border-deep-green shadow-md transform rotate-0">
                        <?= strtoupper($user['role_name']) ?>
                    </div>
                    
                    <div class="text-center mb-6 mt-4">
                        <div class="relative inline-block">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img 
                                    src="<?= SITE_URL ?>/uploads/profiles/<?= $user['profile_image'] ?>" 
                                    alt="Profile"
                                    class="w-32 h-32 mx-auto object-cover border-4 border-deep-green rounded-full p-1 bg-white"
                                >
                            <?php else: ?>
                                <div class="w-32 h-32 mx-auto bg-off-white border-4 border-deep-green flex items-center justify-center text-5xl font-bold text-deep-green rounded-full">
                                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="absolute bottom-0 right-0 bg-lime-accent w-8 h-8 border-2 border-white rounded-full flex items-center justify-center text-xs">
                                ✨
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-deep-green text-center mb-1">
                        <?= htmlspecialchars($user['full_name']) ?>
                    </h3>
                    
                    <p class="text-center text-gray-500 text-sm mb-6">
                        Member since <?= date('M Y', strtotime($user['created_at'])) ?>
                    </p>
                    
                    <div class="bg-deep-green p-4 mb-6 text-white text-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-lime-accent opacity-10 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                        <p class="text-xs text-lime-accent uppercase font-bold mb-1 tracking-widest">MEMBER ID</p>
                        <p class="text-2xl font-mono font-bold tracking-wider relative z-10">
                            <?= htmlspecialchars($user['member_id'] ?? 'N/A') ?>
                        </p>
                    </div>
                    
                    <?php if ($user['role_name'] === 'customer'): ?>
                        <div class="bg-white border-4 border-lime-accent p-4 mb-6 text-center relative overflow-hidden">
                            <div class="absolute -top-6 -right-6 w-16 h-16 bg-lime-accent rounded-full opacity-20"></div>
                            <div class="absolute -bottom-6 -left-6 w-16 h-16 bg-deep-green rounded-full opacity-10"></div>
                            
                            <p class="text-sm font-bold text-gray-600 mb-1">LOYALTY POINTS</p>
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-4xl font-bold text-deep-green animate-pulse">⭐</span>
                                <span class="text-4xl font-bold text-deep-green"><?= number_format($user['points']) ?></span>
                            </div>
                            <p class="text-xs text-green-600 mt-2 font-bold bg-green-50 py-1 px-2 inline-block rounded">
                                Value: ৳<?= floor($user['points'] / 100) * 10 ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-3 text-sm border-t-2 border-gray-100 pt-4">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">📧</span>
                            <span class="text-gray-600 truncate"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-lg">📱</span>
                            <span class="text-gray-600"><?= htmlspecialchars($user['phone']) ?></span>
                        </div>
                        <?php if ($user['address']): ?>
                        <div class="flex items-center gap-3">
                            <span class="text-lg">📍</span>
                            <span class="text-gray-600 truncate"><?= htmlspecialchars($user['address']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-left">
                    <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                        ✏️ Edit Profile
                    </h2>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

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

                        <button type="submit" name="update_profile" class="btn btn-primary w-full text-xl py-4">
                            💾 Update Profile
                        </button>
                    </form>
                </div>

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