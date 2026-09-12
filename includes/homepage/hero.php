<?php
/**
 * QuickMed - Beautiful Responsive Hero Section v3.0
 * Mobile-first, compact boxes, same design language
 */
?>
<style>
    .dynamic-bg-glow {
        background: linear-gradient(-45deg, #022c22, #064e3b, #065f46, #047857, #065f46);
        background-size: 400% 400%;
        animation: gradientBG 12s ease infinite;
        position: relative;
    }
    @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
    .aesthetic-stripes {
        background-image: linear-gradient(45deg, rgba(132,204,22,0.06) 25%, transparent 25%, transparent 50%, rgba(132,204,22,0.06) 50%, rgba(132,204,22,0.06) 75%, transparent 75%, transparent);
        background-size: 32px 32px;
        animation: moveStripes 3s linear infinite;
    }
    @keyframes moveStripes { 0% { background-position: 0 0; } 100% { background-position: 32px 32px; } }
    @keyframes floatRotateDynamic {
        0% { transform: translate(0,0) rotate(0deg) scale(1); }
        33% { transform: translate(15px,-25px) rotate(90deg) scale(1.05); }
        66% { transform: translate(-10px,-15px) rotate(180deg) scale(0.95); }
        100% { transform: translate(0,0) rotate(360deg) scale(1); }
    }
    .floating-item { position: absolute; opacity: 0.12; pointer-events: none; animation: floatRotateDynamic 20s infinite linear; }
    .item-1 { font-size: 3.5rem; left: 4%; top: 8%; animation-duration: 18s; }
    .item-2 { font-size: 4.5rem; right: 6%; top: 25%; animation-duration: 24s; animation-delay: -4s; filter: blur(1px); opacity: 0.08; }
    .item-3 { font-size: 3rem; left: 15%; bottom: 15%; animation-duration: 20s; animation-delay: -8s; }
    .item-4 { font-size: 4rem; right: 20%; bottom: 8%; animation-duration: 22s; animation-delay: -12s; }
    .item-5 { font-size: 2.5rem; left: 45%; top: 45%; animation-duration: 28s; animation-delay: -16s; filter: blur(2px); opacity: 0.06;}
    .typewriter-container { display: inline-block; max-width: 100%; }
    .typewriter-text {
        overflow: hidden; border-right: 3px solid #84cc16; white-space: nowrap;
        margin: 0 auto; animation: typing 3.5s steps(35,end) forwards, blink-caret .75s step-end infinite;
        max-width: 0; font-size: clamp(0.85rem, 2.8vw, 1.5rem);
    }
    @keyframes typing { from { max-width: 0 } to { max-width: 100% } }
    @keyframes blink-caret { from,to { border-color: transparent } 50% { border-color: #84cc16; } }
    .hero-search-wrap {
        background: rgba(255,255,255,0.98);
        border: 2.5px solid #84cc16;
        border-radius: 1rem;
        box-shadow: 0 12px 30px rgba(0,0,0,0.18), 0 0 0 4px rgba(132,204,22,0.15);
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .hero-search-wrap:focus-within {
        border-color: #fff;
        box-shadow: 0 16px 40px rgba(0,0,0,0.22), 0 0 0 6px rgba(132,204,22,0.25);
        transform: translateY(-1px);
    }
    #heroSearchResults { box-shadow: 0 20px 50px rgba(0,0,0,0.25); border-radius: 0.9rem; border: 2px solid #84cc16; }
    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #065f46; border-radius: 10px; }
    .hero-stat {
        background: rgba(6,95,70,0.45);
        backdrop-filter: blur(10px);
        border: 1.5px solid rgba(132,204,22,0.4);
        padding: 0.9rem 0.6rem;
        border-radius: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .hero-stat:hover { transform: translateY(-3px) scale(1.02); background: rgba(6,95,70,0.65); border-color: #84cc16; box-shadow: 0 8px 20px rgba(132,204,22,0.25); }
    @media (max-width: 640px) {
        .hero-stat { padding: 0.7rem 0.4rem; border-radius: 0.7rem; }
        .hero-stat .stat-emoji { font-size: 1.6rem !important; margin-bottom: 0.3rem !important; }
        .hero-stat .stat-num { font-size: 1.3rem !important; }
        .hero-stat .stat-label { font-size: 0.65rem !important; }
    }
    .qm-badge {
        background: #84cc16; color: #065f46; border: 2.5px solid #fff;
        box-shadow: 0 0 0 3px rgba(132,204,22,0.3), 0 8px 20px rgba(132,204,22,0.4);
        border-radius: 0.9rem; padding: 0.5rem 1.2rem; display: inline-block; transform: rotate(-1deg); transition: all 0.3s ease;
    }
    .qm-badge:hover { transform: rotate(0deg) scale(1.03); box-shadow: 0 0 0 4px rgba(132,204,22,0.4), 0 12px 28px rgba(132,204,22,0.5); }
    @media (max-width: 640px) { .qm-badge { padding: 0.4rem 0.9rem; border-radius: 0.7rem; border-width: 2px; } }
</style>
<section class="dynamic-bg-glow text-white py-10 sm:py-14 md:py-20 lg:py-24 border-b-[3px] border-lime-accent relative overflow-hidden">
    <div class="absolute inset-0 aesthetic-stripes pointer-events-none z-0"></div>
    <div class="absolute inset-0 pointer-events-none z-0" style="background: radial-gradient(circle at 20% 30%, rgba(132,204,22,0.12) 0%, transparent 40%), radial-gradient(circle at 80% 70%, rgba(255,255,255,0.06) 0%, transparent 35%);"></div>
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-[1]">
        <div class="floating-item item-1">💊</div><div class="floating-item item-2">💉</div><div class="floating-item item-3">🏥</div><div class="floating-item item-4">⚕️</div><div class="floating-item item-5">🩺</div>
    </div>
    <div class="container mx-auto px-3 sm:px-4 relative z-10">
        <div class="max-w-5xl mx-auto text-center">
            <div data-aos="fade-down" data-aos-duration="700" class="mb-4 sm:mb-6">
                <div class="qm-badge"><h1 class="text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-bold font-mono tracking-tight leading-none flex items-center gap-[1px]"><span>Q</span><span>u</span><span>i</span><span>c</span><span>k</span><span>M</span><span>e</span><span>d</span></h1></div>
                <div class="mt-2 sm:mt-3 flex items-center justify-center gap-2 text-[10px] sm:text-xs font-bold tracking-widest uppercase"><span class="w-1.5 h-1.5 rounded-full bg-lime-accent animate-pulse"></span><span class="text-lime-100">Trusted • Fast • Genuine</span><span class="w-1.5 h-1.5 rounded-full bg-lime-accent animate-pulse"></span></div>
            </div>
            <h2 class="text-[1.5rem] xs:text-[1.7rem] sm:text-3xl md:text-4xl lg:text-[2.7rem] font-bold mb-3 sm:mb-4 leading-tight drop-shadow-lg px-2" data-aos="fade-up" data-aos-delay="100"><span class="bg-clip-text text-transparent bg-gradient-to-r from-white via-white to-lime-200"><?= __('hero_title') ?></span></h2>
            <div class="mb-6 sm:mb-8 md:mb-10 min-h-[1.8rem] sm:min-h-[2.2rem] flex justify-center items-center px-2" data-aos="fade-up" data-aos-delay="200"><div class="typewriter-container"><p class="typewriter-text font-mono text-lime-100 font-medium"><?= __('hero_subtitle') ?>...</p></div></div>
            <div class="max-w-[95%] sm:max-w-2xl lg:max-w-3xl mx-auto mb-6 sm:mb-8 md:mb-10 relative z-50" data-aos="zoom-in" data-aos-delay="300">
                <div class="hero-search-wrap flex items-center"><span class="pl-3 sm:pl-4 pr-1 sm:pr-2 text-base sm:text-xl text-gray-400 flex-shrink-0">🔍</span><input type="text" id="heroSearchInput" class="flex-1 px-2 sm:px-3 py-3 sm:py-3.5 text-[0.9rem] sm:text-[1.05rem] text-gray-800 border-0 focus:outline-none font-semibold bg-transparent placeholder-gray-400 placeholder:text-[0.85rem] sm:placeholder:text-[1rem]" placeholder="<?= __('search_placeholder') ?>" autocomplete="off"><button onclick="triggerHeroSearch()" class="bg-deep-green text-white px-3 sm:px-6 py-3 sm:py-3.5 font-bold text-[0.75rem] sm:text-[0.9rem] hover:bg-lime-accent hover:text-deep-green transition-all border-l-2 border-lime-accent uppercase tracking-wider flex-shrink-0"><span class="hidden sm:inline">Search</span><span class="sm:hidden">Go</span></button></div>
                <div id="heroSearchResults" class="hidden absolute top-full left-0 w-full bg-white mt-2.5 max-h-[65vh] sm:max-h-80 overflow-y-auto z-[99999] custom-scroll"></div>
                <p class="text-[10px] sm:text-xs text-lime-100/70 mt-2.5 font-mono tracking-wide">Try: Napa, Ace, Seclo, Fexo • Press Enter to search</p>
            </div>
            <div class="flex flex-col xs:flex-row flex-wrap justify-center gap-2.5 sm:gap-3 mb-8 sm:mb-10 md:mb-12 px-2 sm:px-0" data-aos="fade-up" data-aos-delay="400">
                <a href="<?= SITE_URL ?>/prescription-upload.php" class="group inline-flex items-center justify-center gap-2 bg-lime-accent text-deep-green px-4 sm:px-5 py-2.5 sm:py-3 font-bold text-[0.8rem] sm:text-[0.9rem] rounded-[0.7rem] border-2 border-white shadow-[0_4px_15px_rgba(132,204,22,0.4)] hover:shadow-[0_6px_20px_rgba(132,204,22,0.5)] hover:bg-white hover:scale-[1.02] active:scale-[0.98] transition-all uppercase tracking-wide"><span class="text-base sm:text-lg group-hover:rotate-6 transition-transform">📋</span> Upload Prescription</a>
                <a href="<?= SITE_URL ?>/shop.php" class="group inline-flex items-center justify-center gap-2 bg-white/10 backdrop-blur-md border-2 border-white/80 text-white px-4 sm:px-5 py-2.5 sm:py-3 font-bold text-[0.8rem] sm:text-[0.9rem] rounded-[0.7rem] hover:bg-white hover:text-deep-green hover:scale-[1.02] active:scale-[0.98] transition-all uppercase tracking-wide"><span class="text-base sm:text-lg group-hover:rotate-12 transition-transform">🛍️</span> Shop Now</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3 md:gap-4 max-w-3xl mx-auto px-1 sm:px-0" data-aos="zoom-in" data-aos-delay="500">
                <div class="hero-stat group"><div class="stat-emoji text-[1.8rem] sm:text-[2rem] md:text-[2.2rem] mb-1 sm:mb-1.5 group-hover:animate-bounce">✅</div><div class="stat-num text-[1.4rem] sm:text-[1.6rem] md:text-[1.8rem] font-bold text-lime-accent font-mono leading-none counter" data-target="100">0</div><div class="stat-label font-bold text-[0.65rem] sm:text-[0.72rem] text-white/90 uppercase tracking-wide mt-1">Genuine</div></div>
                <div class="hero-stat group"><div class="stat-emoji text-[1.8rem] sm:text-[2rem] md:text-[2.2rem] mb-1 sm:mb-1.5 group-hover:animate-pulse">🚚</div><div class="stat-num text-[1.4rem] sm:text-[1.6rem] md:text-[1.8rem] font-bold text-lime-accent font-mono leading-none counter" data-target="24">0</div><div class="stat-label font-bold text-[0.65rem] sm:text-[0.72rem] text-white/90 uppercase tracking-wide mt-1">Hour Delivery</div></div>
                <div class="hero-stat group"><div class="stat-emoji text-[1.8rem] sm:text-[2rem] md:text-[2.2rem] mb-1 sm:mb-1.5 group-hover:rotate-12 transition-transform">💰</div><div class="stat-num text-[1.4rem] sm:text-[1.6rem] md:text-[1.8rem] font-bold text-lime-accent font-mono leading-none counter" data-target="30">0</div><div class="stat-label font-bold text-[0.65rem] sm:text-[0.72rem] text-white/90 uppercase tracking-wide mt-1">% Savings</div></div>
                <div class="hero-stat group"><div class="stat-emoji text-[1.8rem] sm:text-[2rem] md:text-[2.2rem] mb-1 sm:mb-1.5 group-hover:scale-110 transition-transform">🔒</div><div class="stat-num text-[1.4rem] sm:text-[1.6rem] md:text-[1.8rem] font-bold text-lime-accent font-mono leading-none counter" data-target="100">0</div><div class="stat-label font-bold text-[0.65rem] sm:text-[0.72rem] text-white/90 uppercase tracking-wide mt-1">% Secure</div></div>
            </div>
            <div class="mt-6 sm:mt-8 flex flex-wrap justify-center items-center gap-2 sm:gap-3 text-[10px] sm:text-xs text-white/60 font-mono"><span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-1 rounded-full border border-white/20"><span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span> 100% Authentic</span><span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-1 rounded-full border border-white/20">🚚 Free delivery 1000৳+</span><span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-1 rounded-full border border-white/20">👨‍⚕️ Pharmacist verified</span></div>
        </div>
    </div>
</section>
<div id="prescriptionModal" class="fixed inset-0 z-[9999] hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="relative w-full max-w-[95%] sm:max-w-lg bg-white rounded-xl shadow-2xl border-2 border-lime-accent max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b-2 border-deep-green bg-deep-green text-white sticky top-0 z-10"><h3 class="text-base sm:text-lg font-bold font-mono">📋 <?= __('upload_prescription') ?></h3><button onclick="closePrescriptionModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white text-xl leading-none transition">✕</button></div>
        <div class="p-4 sm:p-5">
            <?php if (isLoggedIn()): ?>
                <form id="prescriptionForm" action="<?= SITE_URL ?>/ajax/upload_prescription.php" method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>"><div class="mb-4"><label class="block font-bold mb-2 text-deep-green text-sm">📸 Prescription Image *</label><div class="border-2 border-dashed border-deep-green p-6 text-center hover:border-lime-accent transition-all cursor-pointer bg-gray-50 rounded-xl group" id="dropZone"><input type="file" name="prescription_image" id="prescriptionImage" accept="image/*" required class="hidden"><div id="dropText" class="group-hover:scale-[1.02] transition-transform"><div class="text-4xl mb-2">📤</div><p class="text-sm font-bold mb-1 text-gray-700">Click or Drag & Drop</p><p class="text-xs text-gray-500">Clear photo of prescription</p></div></div></div><div class="mb-4"><label class="block font-bold mb-2 text-deep-green text-sm">📝 Notes (Optional)</label><textarea name="notes" rows="2" class="w-full p-2.5 text-sm text-gray-800 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-lime-accent focus:ring-2 focus:ring-lime-100 transition" placeholder="Any instructions..."></textarea></div><div id="imagePreview" class="mb-4 hidden bg-gray-50 p-2 rounded-lg border"><div class="flex justify-between items-center mb-2"><p class="font-bold text-xs text-deep-green">Selected:</p><button type="button" onclick="clearImage()" class="text-red-500 text-xs font-bold hover:underline">Remove</button></div><img src="" alt="Preview" class="max-h-40 mx-auto rounded-lg border"></div><button type="submit" class="w-full bg-deep-green text-white font-bold text-sm py-3 rounded-xl hover:bg-lime-accent hover:text-deep-green transition-all shadow-md uppercase tracking-wide">📤 Upload Now</button></form>
            <?php else: ?>
                <div class="text-center py-6"><div class="text-5xl mb-3 animate-pulse">🔐</div><h4 class="text-lg font-bold text-deep-green mb-1">Login Required</h4><p class="text-gray-500 text-sm mb-5">Please login to upload prescription</p><a href="<?= SITE_URL ?>/login.php" class="inline-flex bg-deep-green text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-lime-accent hover:text-deep-green transition">Login Now</a></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('heroSearchInput');
    const resultsContainer = document.getElementById('heroSearchResults');
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            if (query.length < 2) { resultsContainer.classList.add('hidden'); return; }
            resultsContainer.classList.remove('hidden');
            resultsContainer.innerHTML = `<div class="p-5 text-center"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-deep-green mx-auto mb-2"></div><p class="text-gray-500 text-xs font-bold">Searching...</p></div>`;
            searchTimeout = setTimeout(() => { fetchResults(query); }, 280);
        });
        searchInput.addEventListener('keypress', function(e) { if (e.key === 'Enter') { triggerHeroSearch(); } });
    }
    async function fetchResults(query) {
        try {
            const siteUrl = '<?= SITE_URL ?>';
            const response = await fetch(`${siteUrl}/ajax/search_medicine.php?q=${encodeURIComponent(query)}`);
            const results = await response.json();
            if (results.length === 0) {
                resultsContainer.innerHTML = `<div class="p-5 text-center text-gray-500"><span class="text-3xl block mb-2">😔</span><p class="font-bold text-sm">No medicines found</p><p class="text-xs mt-1">Try generic name</p></div>`;
                return;
            }
            let html = '';
            results.forEach(item => {
                const imagePath = item.image ? `${siteUrl}/uploads/medicines/${item.image}` : `${siteUrl}/assets/images/placeholder.png`;
                html += `<a href="${siteUrl}/product.php?id=${item.id}" class="flex items-center gap-3 p-3 border-b last:border-0 hover:bg-green-50 transition-colors group"><img src="${imagePath}" class="w-11 h-11 object-contain border border-gray-200 rounded-lg bg-white p-1 flex-shrink-0"><div class="flex-1 min-w-0 text-left"><h4 class="font-bold text-deep-green text-[0.9rem] truncate group-hover:text-lime-600 transition">${item.name}</h4><p class="text-[11px] text-gray-500 font-mono truncate">${item.power} | ${item.form}</p></div><div class="text-right flex-shrink-0"><p class="text-[0.95rem] font-bold text-deep-green">৳${item.price}</p><span class="text-[9px] bg-lime-100 text-deep-green px-1.5 py-0.5 rounded font-bold">In Stock</span></div></a>`;
            });
            resultsContainer.innerHTML = html;
        } catch (error) {
            console.error(error);
            resultsContainer.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Search failed</div>';
        }
    }
    document.addEventListener('click', function(e) {
        if (searchInput && resultsContainer && !e.target.closest('#heroSearchInput') && !e.target.closest('#heroSearchResults')) {
            resultsContainer.classList.add('hidden');
        }
    });
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('prescriptionImage');
    if(dropZone && fileInput) {
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('border-lime-accent','bg-green-50'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('border-lime-accent','bg-green-50'); });
        dropZone.addEventListener('drop', (e) => { e.preventDefault(); dropZone.classList.remove('border-lime-accent','bg-green-50'); if(e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; previewImage(e.dataTransfer.files[0]); } });
        fileInput.addEventListener('change', function(e) { if(e.target.files.length) previewImage(e.target.files[0]); });
    }
    const modal = document.getElementById('prescriptionModal');
    if(modal) { modal.addEventListener('click', function(e) { if (e.target === this) closePrescriptionModal(); }); }
    const rxForm = document.getElementById('prescriptionForm');
    if (rxForm) {
        rxForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = rxForm.querySelector('button[type="submit"]');
            const oldText = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = 'Uploading...';
            try {
                const res = await fetch(rxForm.action, { method: 'POST', body: new FormData(rxForm) });
                const data = await res.json();
                if (data.success) { closePrescriptionModal(); rxForm.reset(); clearImage(); Swal.fire({ icon: 'success', title: 'Uploaded!', text: data.message, confirmButtonColor: '#065f46' }); }
                else { Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Upload failed', confirmButtonColor: '#065f46' }); }
            } catch (err) { Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.', confirmButtonColor: '#065f46' }); }
            btn.disabled = false; btn.innerHTML = oldText;
        });
    }
});
function triggerHeroSearch() {
    const input = document.getElementById('heroSearchInput');
    if (input.value.trim().length >= 2) { input.dispatchEvent(new Event('input')); }
    else { if(typeof Swal !== 'undefined') { Swal.fire({ icon: 'info', title: 'Search', text: 'Please type at least 2 characters', confirmButtonColor: '#065f46' }); } else { alert('Please type at least 2 characters'); } }
}
function openPrescriptionModal() { document.getElementById('prescriptionModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closePrescriptionModal() { document.getElementById('prescriptionModal').classList.add('hidden'); document.body.style.overflow = 'auto'; }
function previewImage(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const previewDiv = document.getElementById('imagePreview');
        previewDiv.querySelector('img').src = e.target.result;
        previewDiv.classList.remove('hidden');
        document.getElementById('dropText').innerHTML = '<div class="text-deep-green text-sm font-bold animate-pulse">✅ File Selected</div>';
    }
    reader.readAsDataURL(file);
}
function clearImage() {
    document.getElementById('prescriptionImage').value = '';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('dropText').innerHTML = '<div class="text-3xl mb-2">📤</div><p class="text-sm font-bold mb-1 text-gray-700">Click or Drag & Drop</p><p class="text-xs text-gray-500">Clear photo</p>';
}
</script>
