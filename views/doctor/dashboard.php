<?php
/**
 * Doctor Dashboard
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$pageTitle = 'Doctor Dashboard - QuickMed';
$user = getCurrentUser();

// Pending Prescriptions Count
$pendingRx = $conn->query("SELECT COUNT(*) as total FROM prescriptions WHERE status = 'pending'")->fetch_assoc()['total'];

// My Posts Count
$myPosts = $conn->query("SELECT COUNT(*) as total FROM health_posts WHERE author_id = " . $user['id'])->fetch_assoc()['total'];

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <div>
                <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">👨‍⚕️ Doctor Panel</h1>
                <p class="text-gray-600">Welcome, Dr. <?= htmlspecialchars($user['full_name']) ?></p>
            </div>
            <div class="bg-lime-accent px-4 py-2 rounded font-bold text-deep-green shadow-md">
                ID: <?= htmlspecialchars($user['member_id']) ?>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid md:grid-cols-3 gap-6 mb-12">
            <div class="card bg-white border-l-8 border-yellow-500 p-6 shadow-md" data-aos="fade-up">
                <h3 class="text-xl font-bold text-gray-600">Pending Rx Review</h3>
                <p class="text-4xl font-bold text-deep-green mt-2"><?= $pendingRx ?></p>
            </div>
            <div class="card bg-white border-l-8 border-blue-500 p-6 shadow-md" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-xl font-bold text-gray-600">My Articles</h3>
                <p class="text-4xl font-bold text-deep-green mt-2"><?= $myPosts ?></p>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2 class="text-2xl font-bold text-deep-green mb-6 border-b-4 border-lime-accent pb-2 inline-block">🚀 Actions</h2>
        <div class="grid md:grid-cols-3 gap-6">
            
            <a href="prescriptions.php" class="card bg-white border-4 border-deep-green hover:bg-light-green p-8 text-center transition group">
                <span class="text-5xl mb-4 block group-hover:scale-110 transition">📋</span>
                <h3 class="text-xl font-bold text-deep-green">Review Prescriptions</h3>
                <p class="text-sm text-gray-500 mt-2">Check & Approve patient requests</p>
            </a>

            <a href="create-post.php" class="card bg-white border-4 border-deep-green hover:bg-light-green p-8 text-center transition group">
                <span class="text-5xl mb-4 block group-hover:scale-110 transition">✍️</span>
                <h3 class="text-xl font-bold text-deep-green">Write Health Tip</h3>
                <p class="text-sm text-gray-500 mt-2">Publish blogs & news</p>
            </a>

            <a href="my-posts.php" class="card bg-white border-4 border-deep-green hover:bg-light-green p-8 text-center transition group">
                <span class="text-5xl mb-4 block group-hover:scale-110 transition">📚</span>
                <h3 class="text-xl font-bold text-deep-green">Manage Posts</h3>
                <p class="text-sm text-gray-500 mt-2">Edit or delete your articles</p>
            </a>

        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>