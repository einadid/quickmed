/**
 * QuickMed - Live Search JavaScript - COMPLETE FIX
 * Features: Live Typing, Debouncing, Row Redirect, Direct Add-to-Cart
 */

let searchTimeout;

// Initialize search on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeHeroSearch();
});

// Hero Search Initialization
function initializeHeroSearch() {
    const searchInput = document.getElementById('heroSearch');
    const resultsContainer = document.getElementById('searchResults');
    
    if (!searchInput || !resultsContainer) return;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            resultsContainer.classList.add('hidden');
            return;
        }

        // Show loading state
        resultsContainer.classList.remove('hidden');
        resultsContainer.innerHTML = `
            <div class="p-4 bg-white border border-gray-200 shadow-lg rounded-b-lg">
                <div class="animate-pulse space-y-3">
                    <div class="h-16 bg-gray-100 rounded"></div>
                    <div class="h-16 bg-gray-100 rounded"></div>
                    <div class="h-16 bg-gray-100 rounded"></div>
                </div>
            </div>
        `;

        // Debounce search (wait 300ms after typing stops)
        searchTimeout = setTimeout(function() {
            performSearch(query, resultsContainer);
        }, 300);
    });

    // Close results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#heroSearch') && !e.target.closest('#searchResults')) {
            resultsContainer.classList.add('hidden');
        }
    });
}

// Perform AJAX search
async function performSearch(query, container) {
    try {
        // Dynamic Site URL detection
        const siteUrl = window.location.origin + '/quickmed'; 
        
        const response = await fetch(siteUrl + '/ajax/search_medicine.php?q=' + encodeURIComponent(query));
        const results = await response.json();

        if (results.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center text-gray-500 bg-white border border-gray-200 shadow-lg rounded-b-lg">
                    <div class="text-4xl mb-2">💊</div>
                    <p class="font-bold">No medicines found</p>
                    <p class="text-xs mt-1">Try searching by generic name</p>
                </div>
            `;
            return;
        }

        let html = '<div class="bg-white border border-gray-200 shadow-xl rounded-b-lg overflow-hidden">';
        
        results.forEach(function(item) {
            // Stock Status Logic
            const stockBadge = item.stock > 50 
                ? '<span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full">In Stock</span>'
                : item.stock > 0 
                    ? '<span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Low Stock</span>'
                    : '<span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Out of Stock</span>';

            const imagePath = item.image ? item.image : 'placeholder.png';

            // --- HTML CONSTRUCTION ---
            html += `
                <div class="search-result-item block p-4 bg-white hover:bg-green-50 transition-colors cursor-pointer border-b border-gray-100 last:border-0" 
                     onclick="window.location.href='${siteUrl}/product.php?id=${item.id}'">
                    
                    <div class="flex items-center gap-4">
                        <img 
                            src="${siteUrl}/uploads/medicines/${imagePath}" 
                            alt="${item.name}"
                            class="w-12 h-12 object-contain border border-gray-200 bg-white p-0.5 rounded"
                            onerror="this.src='${siteUrl}/assets/images/placeholder.png'"
                        >
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-800 text-base leading-tight truncate">${item.name}</h4>
                            <p class="text-xs text-gray-500 mb-1 truncate">${item.power} | ${item.form}</p>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-emerald-600">৳${item.price}</span>
                                <span class="text-[10px] text-gray-400 flex items-center gap-0.5">
                                    📍 ${item.city}
                                </span>
                            </div>
                        </div>

                        <div class="text-right flex flex-col items-end gap-2">
                            ${stockBadge}
                            
                            <button 
                                onclick="event.stopPropagation(); addToCart(${item.id}, ${item.shop_id}, 1);"
                                class="bg-emerald-600 text-white px-3 py-1.5 text-xs font-bold rounded hover:bg-emerald-700 transition-colors flex items-center gap-1 shadow-sm active:scale-95 z-10"
                                ${item.stock <= 0 ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''}
                            >
                                <span>ADD</span> 🛒
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;

    } catch (error) {
        console.error('Search error:', error);
        container.innerHTML = `
            <div class="p-4 text-center text-red-500 bg-white border border-red-100 shadow-lg rounded-b-lg">
                <p class="font-bold text-sm">Connection Error</p>
            </div>
        `;
    }
}