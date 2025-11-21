<?php
/**
 * QuickMed - Bilingual Support (English & Bangla)
 */

// Set default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Language switcher handler
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bn'])) {
    $_SESSION['lang'] = $_GET['lang'];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$currentLang = $_SESSION['lang'];

// Translation arrays
$translations = [
    'en' => [
        // Navigation
        'home' => 'Home',
        'shop' => 'Shop',
        'cart' => 'Cart',
        'orders' => 'My Orders',
        'login' => 'Login',
        'signup' => 'Sign Up',
        'logout' => 'Logout',
        'dashboard' => 'Dashboard',
        'profile' => 'Profile',
        
        // Homepage
        'hero_title' => 'Your Trusted Online Pharmacy',
        'hero_subtitle' => 'Genuine medicines delivered to your doorstep',
        'search_placeholder' => 'Search medicines...',
        'upload_prescription' => 'Upload Prescription',
        'shop_by_concerns' => 'Shop by Health Concerns',
        'flash_sale' => 'Flash Sale',
        'featured_products' => 'Featured Products',
        'customer_reviews' => 'Customer Reviews',
        'health_tips' => 'Health Tips & Blog',
        'latest_news' => 'Latest News',
        
        // Categories
        'cat_heart' => 'Heart',
        'cat_diabetes' => 'Diabetes',
        'cat_baby_care' => 'Baby Care',
        'cat_skin' => 'Skin',
        'cat_orthopedic' => 'Orthopedic',
        'cat_eye_ear' => 'Eye & Ear',
        'cat_dental' => 'Dental',
        'cat_allergy' => 'Allergy',
        
        // Product
        'add_to_cart' => 'Add to Cart',
        'buy_now' => 'Buy Now',
        'in_stock' => 'In Stock',
        'out_of_stock' => 'Out of Stock',
        'low_stock' => 'Low Stock',
        'price' => 'Price',
        'quantity' => 'Quantity',
        'total' => 'Total',
        
        // Cart
        'your_cart' => 'Your Cart',
        'cart_empty' => 'Your cart is empty',
        'continue_shopping' => 'Continue Shopping',
        'proceed_checkout' => 'Proceed to Checkout',
        'subtotal' => 'Subtotal',
        'delivery_charge' => 'Delivery Charge',
        'discount' => 'Discount',
        'grand_total' => 'Grand Total',
        
        // Checkout
        'checkout' => 'Checkout',
        'delivery_info' => 'Delivery Information',
        'full_name' => 'Full Name',
        'phone' => 'Phone Number',
        'address' => 'Delivery Address',
        'delivery_type' => 'Delivery Type',
        'home_delivery' => 'Home Delivery',
        'store_pickup' => 'Store Pickup',
        'payment_method' => 'Payment Method',
        'cash_on_delivery' => 'Cash on Delivery',
        'use_points' => 'Use Loyalty Points',
        'available_points' => 'Available Points',
        'place_order' => 'Place Order',
        
        // Orders
        'order_number' => 'Order Number',
        'order_date' => 'Order Date',
        'order_status' => 'Status',
        'order_details' => 'Order Details',
        'track_order' => 'Track Order',
        
        // Status
        'processing' => 'Processing',
        'packed' => 'Packed',
        'ready' => 'Ready',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        
        // Auth
        'email' => 'Email',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'remember_me' => 'Remember Me',
        'forgot_password' => 'Forgot Password?',
        'have_account' => 'Already have an account?',
        'no_account' => 'Don\'t have an account?',
        'verification_code' => 'Verification Code',
        
        // Messages
        'success' => 'Success',
        'error' => 'Error',
        'warning' => 'Warning',
        'info' => 'Info',
        'loading' => 'Loading...',
        'no_results' => 'No results found',
        
        // Footer
        'about_us' => 'About Us',
        'contact_us' => 'Contact Us',
        'terms' => 'Terms & Conditions',
        'privacy' => 'Privacy Policy',
        'follow_us' => 'Follow Us',
        'all_rights_reserved' => 'All rights reserved',
        
        // Stats
        'total_medicines' => 'Total Medicines',
        'active_shops' => 'Active Shops',
        'total_orders' => 'Total Orders',
        'happy_customers' => 'Happy Customers',
        'delivery_success' => 'Delivery Success Rate',
    ],
    
    'bn' => [
        // Navigation
        'home' => 'হোম',
        'shop' => 'শপ',
        'cart' => 'কার্ট',
        'orders' => 'আমার অর্ডার',
        'login' => 'লগইন',
        'signup' => 'সাইন আপ',
        'logout' => 'লগআউট',
        'dashboard' => 'ড্যাশবোর্ড',
        'profile' => 'প্রোফাইল',
        
        // Homepage
        'hero_title' => 'আপনার বিশ্বস্ত অনলাইন ফার্মেসি',
        'hero_subtitle' => 'খাঁটি ওষুধ আপনার দোরগোড়ায়',
        'search_placeholder' => 'ওষুধ খুঁজুন...',
        'upload_prescription' => 'প্রেসক্রিপশন আপলোড করুন',
        'shop_by_concerns' => 'স্বাস্থ্য সমস্যা অনুযায়ী কিনুন',
        'flash_sale' => 'ফ্ল্যাশ সেল',
        'featured_products' => 'বিশেষ পণ্য',
        'customer_reviews' => 'ক্রেতাদের মতামত',
        'health_tips' => 'স্বাস্থ্য টিপস ও ব্লগ',
        'latest_news' => 'সর্বশেষ খবর',
        
        // Categories
        'cat_heart' => 'হৃদরোগ',
        'cat_diabetes' => 'ডায়াবেটিস',
        'cat_baby_care' => 'শিশু যত্ন',
        'cat_skin' => 'চর্মরোগ',
        'cat_orthopedic' => 'হাড় ও জয়েন্ট',
        'cat_eye_ear' => 'চোখ ও কান',
        'cat_dental' => 'দাঁত',
        'cat_allergy' => 'এলার্জি',
        
        // Product
        'add_to_cart' => 'কার্টে যোগ করুন',
        'buy_now' => 'এখনই কিনুন',
        'in_stock' => 'স্টকে আছে',
        'out_of_stock' => 'স্টকে নেই',
        'low_stock' => 'সীমিত স্টক',
        'price' => 'মূল্য',
        'quantity' => 'পরিমাণ',
        'total' => 'মোট',
        
        // Cart
        'your_cart' => 'আপনার কার্ট',
        'cart_empty' => 'আপনার কার্ট খালি',
        'continue_shopping' => 'কেনাকাটা চালিয়ে যান',
        'proceed_checkout' => 'চেকআউটে যান',
        'subtotal' => 'সাবটোটাল',
        'delivery_charge' => 'ডেলিভারি চার্জ',
        'discount' => 'ছাড়',
        'grand_total' => 'সর্বমোট',
        
        // Checkout
        'checkout' => 'চেকআউট',
        'delivery_info' => 'ডেলিভারি তথ্য',
        'full_name' => 'পুরো নাম',
        'phone' => 'ফোন নম্বর',
        'address' => 'ডেলিভারি ঠিকানা',
        'delivery_type' => 'ডেলিভারি ধরন',
        'home_delivery' => 'হোম ডেলিভারি',
        'store_pickup' => 'স্টোর থেকে নিন',
        'payment_method' => 'পেমেন্ট পদ্ধতি',
        'cash_on_delivery' => 'ক্যাশ অন ডেলিভারি',
        'use_points' => 'লয়্যালটি পয়েন্ট ব্যবহার করুন',
        'available_points' => 'উপলব্ধ পয়েন্ট',
        'place_order' => 'অর্ডার করুন',
        
        // Orders
        'order_number' => 'অর্ডার নম্বর',
        'order_date' => 'অর্ডার তারিখ',
        'order_status' => 'স্ট্যাটাস',
        'order_details' => 'অর্ডার বিস্তারিত',
        'track_order' => 'ট্র্যাক অর্ডার',
        
        // Status
        'processing' => 'প্রসেসিং',
        'packed' => 'প্যাক করা হয়েছে',
        'ready' => 'প্রস্তুত',
        'out_for_delivery' => 'ডেলিভারির জন্য',
        'delivered' => 'ডেলিভার হয়েছে',
        'cancelled' => 'বাতিল',
        
        // Auth
        'email' => 'ইমেইল',
        'password' => 'পাসওয়ার্ড',
        'confirm_password' => 'পাসওয়ার্ড নিশ্চিত করুন',
        'remember_me' => 'আমাকে মনে রাখুন',
        'forgot_password' => 'পাসওয়ার্ড ভুলে গেছেন?',
        'have_account' => 'ইতিমধ্যে অ্যাকাউন্ট আছে?',
        'no_account' => 'অ্যাকাউন্ট নেই?',
        'verification_code' => 'ভেরিফিকেশন কোড',
        
        // Messages
        'success' => 'সফল',
        'error' => 'ত্রুটি',
        'warning' => 'সতর্কতা',
        'info' => 'তথ্য',
        'loading' => 'লোড হচ্ছে...',
        'no_results' => 'কোন ফলাফল পাওয়া যায়নি',
        
        // Footer
        'about_us' => 'আমাদের সম্পর্কে',
        'contact_us' => 'যোগাযোগ করুন',
        'terms' => 'শর্তাবলী',
        'privacy' => 'গোপনীয়তা নীতি',
        'follow_us' => 'আমাদের অনুসরণ করুন',
        'all_rights_reserved' => 'সর্বস্বত্ব সংরক্ষিত',
        
        // Stats
        'total_medicines' => 'মোট ওষুধ',
        'active_shops' => 'সক্রিয় শপ',
        'total_orders' => 'মোট অর্ডার',
        'happy_customers' => 'সন্তুষ্ট গ্রাহক',
        'delivery_success' => 'ডেলিভারি সফলতার হার',
    ]
];

/**
 * Translation helper function
 */
function __($key) {
    global $translations, $currentLang;
    return $translations[$currentLang][$key] ?? $key;
}

/**
 * Get opposite language for switcher
 */
function getOppositeLang() {
    global $currentLang;
    return $currentLang === 'en' ? 'bn' : 'en';
}

/**
 * Get opposite language name
 */
function getOppositeLangName() {
    global $currentLang;
    return $currentLang === 'en' ? 'বাংলা' : 'English';
}
?>