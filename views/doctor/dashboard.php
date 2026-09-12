<?php
/**
 * Doctor Dashboard - QuickMed (Redesigned)
 * Features: Live Clock, Floating Emojis, Modern Cards
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$pageTitle = 'Doctor Dashboard - QuickMed';
$user = getCurrentUser();

// 1. Stats Queries
// Pending Prescriptions
$pendingRx = $conn->query("SELECT COUNT(*) as total FROM prescriptions WHERE status = 'pending'")->fetch_assoc()['total'];

// My Posts
$myPosts = $conn->query("SELECT COUNT(*) as total FROM health_posts WHERE author_id = " . $user['id'])->fetch_assoc()['total'];

// Approved Prescriptions (Added for extra stat)
$approvedRx = $conn->query("SELECT COUNT(*) as total FROM prescriptions WHERE status = 'approved' AND reviewed_by = " . $user['id'])->fetch_assoc()['total'];

include __DIR__ . '/../../includes/header.php';

$firstName = explode(' ', $user['full_name']);
$docName = $firstName[1] ?? $user['full_name'];
$dashTitle = 'Dr. ' . $docName;
$dashSubtitle = '🆔 ' . ($user['member_id'] ?? '—') . ' · ' . date('l, d F Y');
$dashIcon = '🩺';
include __DIR__ . '/../../includes/dashnav.php';
?>

<section class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 min-h-screen">
    <div class="max-w-6xl mx-auto">
            
        <div class="grid sm:grid-cols-3 gap-5 mb-10" data-aos="fade-up">
            <?php qm_stat('⚠️', (int)$pendingRx, 'Pending Reviews', 'amber'); ?>
            <?php qm_stat('✅', (int)$approvedRx, 'Approved by You', ''); ?>
            <?php qm_stat('✍️', (int)$myPosts, 'Published Articles', 'blue'); ?>
        </div>

        <div class="dash-shell mb-8" data-aos="zoom-in">
            <div class="dash-shell-body flex flex-col md:flex-row items-center gap-6">
                <span class="text-6xl">📋</span>
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-2xl font-bold text-[#065f46]">Review Prescriptions</h2>
                    <p class="text-gray-500">Check uploaded prescriptions, verify medicines, and approve orders for patients.</p>
                </div>
                <a href="prescriptions.php" class="btn btn-primary">Start Reviewing →</a>
            </div>
        </div>

        <div class="qa-grid" data-aos="fade-up">
            <a href="create-post.php" class="qa-card"><span class="qa-icon">📢</span><span class="qa-label">Write Health Tip</span></a>
            <a href="my-posts.php" class="qa-card"><span class="qa-icon">📚</span><span class="qa-label">Manage Posts</span></a>
            <a href="add-news.php" class="qa-card"><span class="qa-icon">📰</span><span class="qa-label">Publish News</span></a>
        </div>

    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>