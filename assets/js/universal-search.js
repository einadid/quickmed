/**
 * Universal Search - Works on all pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all search inputs
    initializeUniversalSearch();
});

function initializeUniversalSearch() {
    // Find all search inputs
    const searchInputs = document.querySelectorAll('[data-search="medicine"]');
    
    searchInputs.forEach(input => {
        const resultsId = input.getAttribute('data-results');
        const resultsContainer = document.getElementById(resultsId);
        
        if (!resultsContainer) return;
        
        let searchTimeout;
        
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                resultsContainer.classList.add('hidden');
                return;
            }
            
            resultsContainer.classList.remove('hidden');
            resultsContainer.innerHTML = '<div class="p-4 bg-white text-center">Searching...</div>';
            
            searchTimeout = setTimeout(async function() {
                await performUniversalSearch(query, resultsContainer);
            }, 300);
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest(`#${input.id}`) && !e.target.closest(`#${resultsId}`)) {
                resultsContainer.classList.add('hidden');
            }
        });
    });
}

async function performUniversalSearch(query, container) {
    try {
        const siteUrl = window.location.origin + '/quickmed';
        const response = await fetch(`${siteUrl}/ajax/search_medicine.php?q=${encodeURIComponent(query)}`);
        const results = await response.json();
        
        if (results.length === 0) {
            container.innerHTML = '<div class="p-8 text-center bg-white">No medicines found</div>';
            return;
        }
        
        let html = '';
        results.forEach(item => {
            html += `
                <div class="p-4 border-b-2 hover:bg-light-green cursor-pointer bg-white" onclick="addToCart(${item.id}, ${item.shop_id}, 1)">
                    <div class="flex items-center gap-3">
                        <img src="${siteUrl}/uploads/medicines/${item.image || 'placeholder.png'}" class="w-12 h-12 object-contain border-2 border-deep-green">
                        <div class="flex-1">
                            <p class="font-bold text-deep-green">${item.name}</p>
                            <p class="text-sm">${item.power} - ৳${item.price}</p>
                        </div>
                        <button class="bg-deep-green text-white px-3 py-1 font-bold">Add</button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
    } catch (error) {
        console.error('Search error:', error);
        container.innerHTML = '<div class="p-8 text-center text-red-600 bg-white">Search failed</div>';
    }
}