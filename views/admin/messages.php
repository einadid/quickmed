<?php
/**
 * Admin - Customer Messages
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('admin');

$pageTitle = 'Customer Messages';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM contact_messages WHERE id = $id");
    redirect('views/admin/messages.php');
}

// Get Messages
$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">💬 Customer Messages</h1>
        <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
    </div>

    <div class="grid gap-6">
        <?php if($messages->num_rows == 0): ?>
            <div class="text-center text-gray-500 py-10">No messages found.</div>
        <?php else: ?>
            <?php while($msg = $messages->fetch_assoc()): ?>
                <div class="bg-white border-l-4 border-deep-green shadow-md p-6 hover:shadow-xl transition-all relative group">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-xl text-deep-green"><?= htmlspecialchars($msg['name']) ?></h3>
                            <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($msg['email']) ?> • <?= date('d M Y, h:i A', strtotime($msg['created_at'])) ?></p>
                        </div>
                        <a href="?delete=<?= $msg['id'] ?>" onclick="return confirm('Delete this message?')" class="text-red-500 hover:text-red-700 font-bold text-xl">&times;</a>
                    </div>
                    <p class="text-gray-800 bg-gray-50 p-4 border border-gray-200 rounded mt-2">
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    </p>
                    <a href="mailto:<?= $msg['email'] ?>" class="inline-block mt-4 text-lime-accent font-bold hover:underline">
                        ↪ Reply via Email
                    </a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>