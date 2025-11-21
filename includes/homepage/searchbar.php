<?php
/**
 * Standalone search bar for other pages
 */
?>

<div class="bg-white border-4 border-green p-6 shadow-retro-lg" data-aos="fade-up">
    <h2 class="text-2xl font-bold text-green mb-4 uppercase">🔍 <?= __('search_placeholder') ?></h2>
    
    <div class="relative">
        <input 
            type="text" 
            id="mainSearch" 
            class="w-full px-6 py-4 text-lg text-gray-800 border-4 border-green focus:outline-none focus:border-lime-accent transition-all" 
            placeholder="<?= __('search_placeholder') ?>"
            autocomplete="off"
        >
        <button class="absolute right-2 top-2 bg-green text-white px-6 py-2 border-2 border-lime-accent hover:bg-lime-accent hover:text-green transition-all font-bold">
            SEARCH
        </button>
        
        <!-- Live Search Results -->
        <div id="mainSearchResults" class="absolute w-full bg-white text-gray-800 mt-2 border-4 border-green hidden max-h-96 overflow-y-auto z-50">
            <!-- Skeleton Loader -->
            <div id="searchLoader" class="hidden p-4 space-y-3">
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
            </div>
            
            <!-- Results Container -->
            <div id="searchResultsContent"></div>
        </div>
    </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/search.js"></script>