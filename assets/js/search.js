/**
 * QuickMed - Live Search JavaScript - COMPLETE FIX
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

        // Show loading
        resultsContainer.classList.remove('hidden');
        resultsContainer.innerHTML = `
            <div class="p-4 bg-white">
                <div class="animate-pulse space-y-3">
                    <div class="h-20 bg-gray-200 border-2 border-gray-300"></div>
                    <div class="h-20 bg-gray-200 border-2 border-gray-300"></div>
                    <div class="h-20 bg-gray-200 border-2 border-gray-300"></div>
                </div>
            </div>
        `;

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
        const siteUrl = window.location.origin + '/quickmed';
        const response = await fetch(siteUrl + '/ajax/search_medicine.php?q=' + encodeURIComponent(query));
        const results = await response.json();

        if (results.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center text-gray-500 bg-white">
                    <div class="text-6xl mb-4">😔</div>
                    <p class="text-xl font-bold">No medicines found</p>
                    <p class="text-sm mt-2">Try different search terms</p>
                </div>
            `;
            return;
        }

        let html = '';
        results.forEach(function(item) {
            const stockBadge = item.stock > 50 
                ? '<span class="badge badge-success">✅ In Stock</span>'
                : item.stock > 0 
                    ? '<span class="badge badge-warning">⚠️ Low Stock</span>'
                    : '<span class="badge badge-danger">❌ Out</span>';

            const imagePath = item.image ? item.image : 'placeholder.png';

            html += `
                <div class="search-result-item block p-4 border-b-4 border-gray-200 hover:bg-light-green transition-all cursor-pointer bg-white" 
                     onclick="selectSearchResult(${item.id}, ${item.shop_id}, '${item.name.replace(/'/g, "\\'")}')">
                    <div class="flex items-center gap-4">
                        <img 
                            src="${siteUrl}/uploads/medicines/${imagePath}" 
                            alt="${item.name}"
                            class="w-16 h-16 object-contain border-2 border-deep-green bg-white p-1"
                            onerror="this.src='${siteUrl}/assets/images/placeholder.png'"
                        >
                        <div class="flex-1">
                            <h4 class="font-bold text-deep-green text-lg">${item.name}</h4>
                            <p class="text-sm text-gray-600">${item.power} | ${item.form}</p>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-lg font-bold text-lime-accent">৳${item.price}</span>
                                <span class="text-xs text-gray-500">📍 ${item.city}</span>
                            </div>
                        </div>
                        ${stockBadge}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

    } catch (error) {
        console.error('Search error:', error);
        container.innerHTML = `
            <div class="p-8 text-center text-red-600 bg-white">
                <p class="font-bold">❌ Search failed</p>
                <p class="text-sm">Please try again</p>
            </div>
        `;
    }
}

// Handle search result selection
function selectSearchResult(medicineId, shopId, medicineName) {
    Swal.fire({
        title: 'Add to Cart?',
        text: 'Add ' + medicineName + ' to your cart?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#065f46',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, add it!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            addToCart(medicineId, shopId, 1);
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('heroSearch').value = '';
        }
    });
}