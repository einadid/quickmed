<?php
/**
 * Ultra Modern Dynamic Hero Section - AESTHETIC STRIPES UPDATE
 */
?>

<style>
    /* 1. Dynamic Background Gradient */
    .dynamic-bg-glow {
        background: linear-gradient(-45deg, #022c22, #064e3b, #065f46, #047857);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
    }
    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* 2. Aesthetic Diagonal Stripes (ডোরাকাটা দাগ) */
    .aesthetic-stripes {
        /* ৪৫ ডিগ্রি এঙ্গেলে খুব হালকা লাইম কালারের দাগ */
        background-image: linear-gradient(
            45deg, 
            rgba(132, 204, 22, 0.05) 25%, 
            transparent 25%, 
            transparent 50%, 
            rgba(132, 204, 22, 0.05) 50%, 
            rgba(132, 204, 22, 0.05) 75%, 
            transparent 75%, 
            transparent
        );
        background-size: 40px 40px; /* দাগগুলোর আকার */
        animation: moveStripes 4s linear infinite;
    }
    
    @keyframes moveStripes {
        0% { background-position: 0 0; }
        100% { background-position: 40px 40px; }
    }

    /* 3. Floating Icons Animation */
    @keyframes floatRotateDynamic {
        0% { transform: translate(0px, 0px) rotate(0deg) scale(1); }
        25% { transform: translate(30px, -50px) rotate(90deg) scale(1.1); }
        50% { transform: translate(0px, -100px) rotate(180deg) scale(1); filter: brightness(1.3); }
        75% { transform: translate(-30px, -50px) rotate(270deg) scale(0.9); }
        100% { transform: translate(0px, 0px) rotate(360deg) scale(1); }
    }
    .floating-item {
        position: absolute;
        opacity: 0.15;
        pointer-events: none;
        animation: floatRotateDynamic 25s infinite linear;
        backdrop-filter: blur(2px);
    }
    .item-1 { font-size: 6rem; left: 5%; top: 10%; animation-duration: 20s; animation-delay: 0s; }
    .item-2 { font-size: 8rem; right: 10%; top: 30%; animation-duration: 30s; animation-delay: -5s; filter: blur(3px); opacity: 0.1; }
    .item-3 { font-size: 5rem; left: 20%; bottom: 20%; animation-duration: 22s; animation-delay: -10s; filter: blur(1px); }
    .item-4 { font-size: 7rem; right: 25%; bottom: 10%; animation-duration: 28s; animation-delay: -15s; }
    .item-5 { font-size: 4rem; left: 50%; top: 50%; animation-duration: 35s; animation-delay: -20s; filter: blur(4px); opacity: 0.08;}

    /* 4. Typewriter Effect */
    .typewriter-container {
        display: inline-block;
    }
    .typewriter-text {
        overflow: hidden;
        border-right: 4px solid #84cc16;
        white-space: nowrap;
        margin: 0 auto;
        animation: typing 4s steps(40, end) forwards, blink-caret .75s step-end infinite;
        max-width: 0;
    }
    @keyframes typing { from { max-width: 0 } to { max-width: 100% } }
    @keyframes blink-caret { from, to { border-color: transparent } 50% { border-color: #84cc16; } }
</style>
<section class="dynamic-bg-glow text-white py-24 border-b-4 border-lime-accent relative overflow-hidden">
    
    <div class="absolute inset-0 aesthetic-stripes pointer-events-none" style="z-index: 0;"></div>
    <div class="absolute inset-0 scan-lines pointer-events-none" style="opacity: 0.3; z-index: 2;"></div>
    
    <div class="absolute inset-0 overflow-hidden pointer-events-none" style="z-index: 1;">
        <div class="floating-item item-1">💊</div>
        <div class="floating-item item-2">💉</div>
        <div class="floating-item item-3">🏥</div>
        <div class="floating-item item-4">⚕️</div>
        <div class="floating-item item-5">🩺</div>
    </div>
    
    <div class="container mx-auto px-4 relative" style="z-index: 10;">
        <div class="max-w-5xl mx-auto text-center">
            
            <div data-aos="fade-down" data-aos-duration="1000">
                <div class="inline-block bg-lime-accent text-deep-green px-8 py-4 border-4 border-white mb-6 neon-border transform hover:scale-105 transition-transform duration-500 shadow-[0_0_20px_rgba(132,204,22,0.5)]">
                    <h1 class="text-5xl md:text-7xl font-bold font-mono tracking-wider">
                        <span class="inline-block animate-bounce" style="animation-delay: 0s;">Q</span>
                        <span class="inline-block animate-bounce" style="animation-delay: 0.1s;">u</span>
                        <span class="inline-block animate-bounce" style="animation-delay: 0.2s;">i</span>
                        <span class="inline-block animate-bounce" style="animation-delay: 0.3s;">c</span>
                        <span class="inline-block animate-bounce" style="animation-delay: 0.4s;">k</span>
                        <span class="inline-block animate-bounce" style="animation-delay: 0.5s;">M</span>
                        <span class="inline-block animate-bounce" style="animation-delay: 0.6s;">e</span>
                        <span class="inline-block animate-bounce" style="animation-delay: 0.7s;">d</span>
                    </h1>
                </div>
            </div>
            
            <h2 class="text-3xl md:text-5xl font-bold mb-6 text-transparent bg-clip-text bg-gradient-to-r from-white to-lime-accent drop-shadow-lg" data-aos="fade-up" data-aos-delay="200">
                <?= __('hero_title') ?>
            </h2>
            
            <div class="text-xl md:text-3xl mb-10 font-mono h-10 flex justify-center items-center" data-aos="fade-up" data-aos-delay="400">
                <div class="typewriter-container">
                    <p class="typewriter-text">
                        <?= __('hero_subtitle') ?>...
                    </p>
                </div>
            </div>
            
            <div class="max-w-3xl mx-auto mb-12 relative z-50" data-aos="zoom-in" data-aos-delay="600">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-lime-accent to-deep-green opacity-70 blur-md group-hover:opacity-100 group-hover:blur-lg transition duration-500 animate-pulse"></div>
                    
                    <div class="relative flex shadow-2xl">
                        <input 
                            type="text" 
                            id="heroSearch" 
                            class="w-full px-8 py-5 text-xl text-gray-800 border-4 border-lime-accent focus:outline-none focus:border-white transition-all font-bold rounded-l-lg font-mono" 
                            placeholder="🔍 <?= __('search_placeholder') ?>"
                            autocomplete="off"
                        >
                        <button class="bg-deep-green text-white px-8 border-y-4 border-r-4 border-lime-accent hover:bg-lime-accent hover:text-deep-green transition-all font-bold text-lg rounded-r-lg tracking-widest uppercase">
                            Search
                        </button>
                    </div>
                    
                    <div id="searchResults" class="absolute top-full left-0 right-0 bg-white text-gray-800 mt-3 border-4 border-lime-accent hidden max-h-96 overflow-y-auto z-[100] shadow-[0_10px_30px_rgba(0,0,0,0.5)] rounded-lg">
                        <div id="searchLoader" class="hidden p-4">
                            <div class="animate-pulse space-y-3">
                                <div class="h-16 bg-gray-200 rounded"></div>
                                <div class="h-16 bg-gray-200 rounded"></div>
                            </div>
                        </div>
                        <div id="resultsContent"></div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-wrap justify-center gap-4 mb-12 relative z-10" data-aos="fade-up" data-aos-delay="800">
                
                <a href="<?= SITE_URL ?>/prescription-upload.php"
                   class="btn btn-lime btn-lg text-xl transform transition-all duration-300 hover:scale-110 hover:rotate-2 neon-border flex items-center gap-2 bg-lime-accent text-deep-green px-6 py-3 font-bold border-2 border-white rounded shadow-lg">
                    <span class="text-2xl">📋</span> Upload Prescription
                </a>

                <a href="<?= SITE_URL ?>/shop.php"
                   class="btn btn-outline btn-lg text-xl border-4 border-white text-white hover:bg-white hover:text-deep-green transform transition-all duration-300 hover:scale-110 hover:-rotate-2 flex items-center gap-2 px-6 py-3 font-bold rounded shadow-lg">
                    <span class="text-2xl">🛍️</span> Shop Now
                </a>
                
            </div>
            
            <div class="relative z-10 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto" data-aos="zoom-in" data-aos-delay="1000">
                
                <div class="bg-deep-green bg-opacity-40 border-2 border-lime-accent p-6 backdrop-blur-md transform transition-all duration-500 hover:scale-110 hover:bg-opacity-60 rounded-xl shadow-[0_0_10px_rgba(132,204,22,0.2)] hover:shadow-[0_0_20px_rgba(132,204,22,0.5)] group">
                    <div class="text-5xl mb-3 group-hover:animate-bounce">✅</div>
                    <div class="text-3xl font-bold mb-1 counter text-lime-accent" data-target="100">0</div>
                    <div class="font-bold text-sm md:text-base font-mono">% Genuine</div>
                </div>
                
                <div class="bg-deep-green bg-opacity-40 border-2 border-lime-accent p-6 backdrop-blur-md transform transition-all duration-500 hover:scale-110 hover:bg-opacity-60 rounded-xl shadow-[0_0_10px_rgba(132,204,22,0.2)] hover:shadow-[0_0_20px_rgba(132,204,22,0.5)] group" style="animation-delay: 0.2s;">
                    <div class="text-5xl mb-3 group-hover:animate-pulse">🚚</div>
                    <div class="text-3xl font-bold mb-1 counter text-lime-accent" data-target="24">0</div>
                    <div class="font-bold text-sm md:text-base font-mono">Hour Delivery</div>
                </div>
                
                <div class="bg-deep-green bg-opacity-40 border-2 border-lime-accent p-6 backdrop-blur-md transform transition-all duration-500 hover:scale-110 hover:bg-opacity-60 rounded-xl shadow-[0_0_10px_rgba(132,204,22,0.2)] hover:shadow-[0_0_20px_rgba(132,204,22,0.5)] group" style="animation-delay: 0.4s;">
                    <div class="text-5xl mb-3 group-hover:rotate-12 transition-transform">💰</div>
                    <div class="text-3xl font-bold mb-1 counter text-lime-accent" data-target="30">0</div>
                    <div class="font-bold text-sm md:text-base font-mono">% Savings</div>
                </div>
                
                <div class="bg-deep-green bg-opacity-40 border-2 border-lime-accent p-6 backdrop-blur-md transform transition-all duration-500 hover:scale-110 hover:bg-opacity-60 rounded-xl shadow-[0_0_10px_rgba(132,204,22,0.2)] hover:shadow-[0_0_20px_rgba(132,204,22,0.5)] group" style="animation-delay: 0.6s;">
                    <div class="text-5xl mb-3 group-hover:scale-110 transition-transform">🔒</div>
                    <div class="text-3xl font-bold mb-1 counter text-lime-accent" data-target="100">0</div>
                    <div class="font-bold text-sm md:text-base font-mono">% Secure</div>
                </div>
                
            </div>
        </div>
    </div>
</section>

<div id="prescriptionModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto bg-black bg-opacity-80 backdrop-blur-sm flex items-center justify-center px-4">
    <div class="relative w-full max-w-lg bg-white rounded-lg shadow-2xl border-4 border-lime-accent transform transition-all scale-100">
        <div class="flex items-center justify-between p-5 border-b-4 border-deep-green bg-deep-green text-white">
            <h3 class="text-2xl font-bold font-mono">📋 <?= __('upload_prescription') ?></h3>
            <button onclick="closePrescriptionModal()" class="text-white hover:text-lime-accent text-4xl leading-none">&times;</button>
        </div>
        <div class="p-6">
            <?php if (isLoggedIn()): ?>
                <form id="prescriptionForm" action="<?= SITE_URL ?>/actions/upload_prescription.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <div class="mb-6">
                        <label class="block font-bold mb-3 text-deep-green text-lg">📸 Prescription Image *</label>
                        <div class="border-4 border-dashed border-deep-green p-8 text-center hover:border-lime-accent transition-all cursor-pointer bg-gray-50 rounded-lg group" id="dropZone">
                            <input type="file" name="prescription_image" id="prescriptionImage" accept="image/*" required class="hidden">
                            <div id="dropText" class="group-hover:scale-105 transition-transform">
                                <div class="text-6xl mb-4">📤</div>
                                <p class="text-lg font-bold mb-2 text-gray-700">Click or Drag & Drop</p>
                                <p class="text-sm text-gray-500">Upload a clear photo</p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block font-bold mb-3 text-deep-green text-lg">📝 Notes (Optional)</label>
                        <textarea name="notes" rows="3" class="w-full p-3 text-gray-800 border-4 border-deep-green rounded focus:outline-none focus:border-lime-accent" placeholder="Instructions..."></textarea>
                    </div>
                    <div id="imagePreview" class="mb-6 hidden bg-gray-100 p-2 rounded border-2 border-gray-300">
                        <div class="flex justify-between items-center mb-2">
                            <p class="font-bold text-sm text-deep-green">Selected:</p>
                            <button type="button" onclick="clearImage()" class="text-red-500 text-sm font-bold hover:underline">Remove</button>
                        </div>
                        <img src="" alt="Preview" class="max-h-48 mx-auto rounded border-2 border-deep-green">
                    </div>
                    <button type="submit" class="w-full bg-deep-green text-white font-bold text-xl py-4 hover:bg-lime-accent hover:text-deep-green border-2 border-transparent hover:border-deep-green transition-all shadow-lg uppercase tracking-wider">📤 Upload</button>
                </form>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="text-8xl mb-4 animate-pulse">🔐</div>
                    <h4 class="text-2xl font-bold text-deep-green mb-2">Login Required</h4>
                    <p class="text-gray-600 mb-8">Please login first.</p>
                    <div class="flex gap-4 justify-center">
                        <a href="<?= SITE_URL ?>/login.php" class="bg-deep-green text-white px-6 py-3 font-bold rounded hover:bg-lime-accent hover:text-deep-green">Login</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// --- Modal & Upload Logic ---
function openPrescriptionModal() { document.getElementById('prescriptionModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closePrescriptionModal() { document.getElementById('prescriptionModal').classList.add('hidden'); document.body.style.overflow = 'auto'; }
document.getElementById('prescriptionModal').addEventListener('click', function(e) { if (e.target === this) closePrescriptionModal(); });

const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('prescriptionImage');
if(dropZone && fileInput) {
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('border-lime-accent', 'bg-green-50'); });
    dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('border-lime-accent', 'bg-green-50'); });
    dropZone.addEventListener('drop', (e) => { e.preventDefault(); dropZone.classList.remove('border-lime-accent', 'bg-green-50'); if(e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; previewImage(e.dataTransfer.files[0]); }});
    fileInput.addEventListener('change', function(e) { if(e.target.files.length) previewImage(e.target.files[0]); });
}
function previewImage(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const previewDiv = document.getElementById('imagePreview');
        previewDiv.querySelector('img').src = e.target.result;
        previewDiv.classList.remove('hidden');
        document.getElementById('dropText').innerHTML = '<div class="text-deep-green text-xl font-bold animate-pulse">✅ File Selected</div>';
    }
    reader.readAsDataURL(file);
}
function clearImage() {
    document.getElementById('prescriptionImage').value = '';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('dropText').innerHTML = '<div class="text-6xl mb-4">📤</div><p class="text-lg font-bold mb-2 text-gray-700">Click or Drag & Drop</p>';
}

// --- Search Logic ---
let searchTimeout;
const searchInput = document.getElementById('heroSearch');
const searchResults = document.getElementById('searchResults');
const searchLoader = document.getElementById('searchLoader');
const resultsContent = document.getElementById('resultsContent');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        if (query.length < 2) { searchResults.classList.add('hidden'); return; }
        searchResults.classList.remove('hidden'); searchLoader.classList.remove('hidden'); resultsContent.innerHTML = '';
        searchTimeout = setTimeout(() => {
            const baseUrl = '<?= defined("SITE_URL") ? SITE_URL : "" ?>'; 
            fetch(`${baseUrl}/ajax/search_medicine.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => { searchLoader.classList.add('hidden'); displaySearchResults(data, baseUrl); })
                .catch(error => { searchLoader.classList.add('hidden'); resultsContent.innerHTML = '<div class="p-4 text-red-500">Error</div>'; });
        }, 300);
    });
}
function displaySearchResults(results, baseUrl) {
    if (!results || results.length === 0) { resultsContent.innerHTML = '<div class="p-8 text-center text-gray-500">No medicines found</div>'; return; }
    let html = '';
    results.forEach(item => {
        const imgPath = item.image ? `${baseUrl}/uploads/medicines/${item.image}` : `${baseUrl}/assets/images/placeholder.png`;
        html += `<a href="${baseUrl}/product.php?id=${item.id}" class="block p-4 border-b border-gray-100 hover:bg-green-50 transition-all group">
            <div class="flex items-center gap-4">
                <img src="${imgPath}" class="w-14 h-14 object-contain border border-gray-200 rounded bg-white">
                <div class="flex-1"><h4 class="font-bold text-deep-green group-hover:text-lime-600">${item.name}</h4><p class="text-sm text-gray-600">${item.power} • ${item.form}</p><p class="text-lime-600 font-bold">৳${item.price}</p></div>
            </div></a>`;
    });
    resultsContent.innerHTML = html;
}
document.addEventListener('click', function(e) {
    if (searchInput && searchResults && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add('hidden');
    }
});
</script>