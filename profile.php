<?php
/**
 * QuickMed - User Profile Management (FIXED)
 */

require_once 'config.php';

requireLogin();

$pageTitle = 'My Profile - QuickMed';
$user = getCurrentUser();

// Enable Error Reporting for Debugging (Remove in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Handle Profile Update
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
    
    // Handle Profile Image
    $profileImage = $user['profile_image']; // Keep existing image by default
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/profiles/';
        
        // Create directory if not exists
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
    
    // Handle Password Change
    $passwordHash = $user['password_hash']; // Keep existing password by default
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Current password required to set new password';
        } else {
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $errors[] = 'Current password is incorrect';
            } elseif (strlen($newPassword) < MIN_PASSWORD_LENGTH) {
                $errors[] = 'New password must be at least ' . MIN_PASSWORD_LENGTH . ' characters';
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            }
        }
    }
    
    if (empty($errors)) {
        // Update Query
        $updateQuery = "UPDATE users SET full_name = ?, phone = ?, address = ?, profile_image = ?, password_hash = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssssi", $fullName, $phone, $address, $profileImage, $passwordHash, $user['id']);
        
        if ($stmt->execute()) {
            // Log Activity
            logAudit('PROFILE_UPDATE', 'users', $user['id']);
            $_SESSION['success'] = 'Profile updated successfully!';
            
            // Refresh page to show updates
            echo "<script>window.location.href = 'profile.php';</script>";
            exit();
        } else {
            $errors[] = 'Failed to update profile: ' . $conn->error;
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

// Refresh user data after update attempt
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
                <div class="card bg-white border-4 border-deep-green relative overflow-hidden" data-aos="fade-right">
                    
                    <!-- Role Badge -->
                    <div class="absolute top-4 right-0 bg-lime-accent text-deep-green px-4 py-1 font-bold text-sm border-l-2 border-deep-green shadow-md">
                        <?= strtoupper($user['role_name']) ?>
                    </div>

                    <!-- Profile Image -->
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
                        </div>
                    </div>

                    <h3 class="text-xl font-bold text-deep-green text-center mb-1">
                        <?= htmlspecialchars($user['full_name']) ?>
                    </h3>
                    
                    <p class="text-center text-gray-500 text-sm mb-6">
                        Joined: <?= date('M Y', strtotime($user['created_at'])) ?>
                    </p>

                    <!-- Member ID -->
                    <div class="bg-deep-green p-4 mb-6 text-white text-center">
                        <p class="text-xs text-lime-accent uppercase font-bold mb-1 tracking-widest">MEMBER ID</p>
                        <p class="text-2xl font-mono font-bold tracking-wider">
                            <?= htmlspecialchars($user['member_id'] ?? 'N/A') ?>
                        </p>
                    </div>

                    <!-- Points -->
                    <?php if ($user['role_name'] === 'customer'): ?>
                        <div class="bg-white border-4 border-lime-accent p-4 mb-6 text-center">
                            <p class="text-sm font-bold text-gray-600 mb-1">LOYALTY POINTS</p>
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-4xl font-bold text-deep-green">⭐</span>
                                <span class="text-4xl font-bold text-deep-green"><?= number_format($user['points']) ?></span>
                            </div>
                            <p class="text-xs text-green-600 mt-2 font-bold bg-green-50 py-1 px-2 inline-block rounded">
                                Value: ৳<?= floor($user['points'] / 100) * 10 ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Contact Info -->
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

            <!-- Edit Profile Form -->
            <div class="md:col-span-2">
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-left">
                    <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                        ✏️ Edit Profile
                    </h2>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                        <!-- Profile Image -->
                        <div class="mb-6">
                            <label class="block font-bold mb-2 text-deep-green text-lg">Change Picture</label>
                            <input 
                                type="file" 
                                name="profile_image" 
                                accept="image/*"
                                class="input border-4 border-deep-green w-full"
                            >
                            <p class="text-sm text-gray-600 mt-1">Upload JPG, JPEG, or PNG (Max 5MB)</p>
                        </div>

                        <!-- Full Name -->
                        <div class="mb-6">
                            <label class="block font-bold mb-2 text-deep-green text-lg">Full Name *</label>
                            <input 
                                type="text" 
                                name="full_name" 
                                class="input border-4 border-deep-green w-full" 
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
                                class="input border-4 border-deep-green w-full" 
                                value="<?= htmlspecialchars($user['phone']) ?>"
                                required
                            >
                        </div>

                        <!-- Address -->
                        <div class="mb-6">
                            <label class="block font-bold mb-2 text-deep-green text-lg">Address</label>
                            <textarea 
                                name="address" 
                                rows="3"
                                class="input border-4 border-deep-green w-full"
                                placeholder="House/Flat, Road, Area, City"
                            ><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>

                        <!-- Change Password -->
                        <div class="bg-yellow-50 border-4 border-yellow-500 p-6 mb-6">
                            <h3 class="text-xl font-bold text-yellow-800 mb-4 uppercase">🔐 Change Password</h3>
                            
                            <div class="mb-4">
                                <label class="block font-bold mb-2 text-yellow-800">Current Password</label>
                                <input 
                                    type="password" 
                                    name="current_password" 
                                    class="input border-4 border-yellow-600 w-full"
                                    placeholder="Enter current password"
                                >
                            </div>

                            <div class="mb-4">
                                <label class="block font-bold mb-2 text-yellow-800">New Password</label>
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    class="input border-4 border-yellow-600 w-full"
                                    placeholder="Enter new password (min <?= MIN_PASSWORD_LENGTH ?> characters)"
                                    minlength="<?= MIN_PASSWORD_LENGTH ?>"
                                >
                            </div>
                            <p class="text-sm text-yellow-700">Leave blank if you don't want to change password.</p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" name="update_profile" class="btn btn-primary w-full text-xl py-4 neon-border">
                            💾 Update Profile
                        </button>
                    </form>
                </div>

                <!-- Additional Info -->
                <?php if ($user['role_name'] === 'customer'): ?>
                    <div class="card bg-lime-accent border-4 border-deep-green mt-8" data-aos="fade-up">
                        <h3 class="text-xl font-bold text-deep-green mb-4 uppercase">📊 Your Statistics</h3>
                        <?php
                        $statsQuery = "SELECT 
                            (SELECT COUNT(*) FROM orders WHERE user_id = ?) as total_orders,
                            (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = ?) as total_spent,
                            (SELECT COUNT(*) FROM prescriptions WHERE user_id = ?) as prescriptions_uploaded";
                        $statsStmt = $conn->prepare($statsQuery);
                        $statsStmt->bind_param("iii", $user['id'], $user['id'], $user['id']);
                        $statsStmt->execute();
                        $stats = $statsStmt->get_result()->fetch_assoc();
                        ?>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-white border-2 border-deep-green p-4">
                                <p class="text-3xl font-bold text-deep-green"><?= $stats['total_orders'] ?></p>
                                <p class="text-sm font-bold mt-1">Orders</p>
                            </div>
                            <div class="bg-white border-2 border-deep-green p-4">
                                <p class="text-2xl font-bold text-deep-green">৳<?= number_format($stats['total_spent']) ?></p>
                                <p class="text-sm font-bold mt-1">Spent</p>
                            </div>
                            <div class="bg-white border-2 border-deep-green p-4">
                                <p class="text-3xl font-bold text-deep-green"><?= $stats['prescriptions_uploaded'] ?></p>
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