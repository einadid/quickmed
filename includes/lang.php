<?php
/**
 * QuickMed - Bilingual Support (English & Bangla)
 * COMPLETE TRANSLATION FILE
 */

// Set default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Language switcher handler
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bn'])) {
    $_SESSION['lang'] = $_GET['lang'];
    // Redirect back to same page
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: " . $referer);
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
        'orders' => 'Orders',
        'login' => 'Login',
        'signup' => 'Sign Up',
        'logout' => 'Logout',
        'dashboard' => 'Dashboard',
        'profile' => 'Profile',
        'inventory' => 'Inventory',
        'parcels' => 'Parcels',
        'users' => 'Users',
        'medicines' => 'Medicines',
        'reports' => 'Reports',
        
        // Homepage Hero
        'hero_title' => 'Your Trusted Online Pharmacy',
        'hero_subtitle' => 'Genuine medicines delivered to your doorstep',
        'search_placeholder' => 'Search medicines (e.g. Napa, Ace)...',
        'upload_prescription' => 'Upload Prescription',
        'shop_now' => 'Shop Now',
        
        // Homepage Sections
        'shop_by_concerns' => 'Shop by Health Concerns',
        'flash_sale' => 'Flash Sale',
        'featured_products' => 'Featured Products',
        'customer_reviews' => 'Customer Voices',
        'health_tips' => 'Health Tips & Blog',
        'latest_news' => 'Latest News',
        'about_us' => 'About Us',
        'contact_us' => 'Contact Us',
        
        // Categories
        'cat_heart' => 'Heart',
        'cat_diabetes' => 'Diabetes',
        'cat_baby_care' => 'Baby Care',
        'cat_skin' => 'Skin',
        'cat_orthopedic' => 'Orthopedic',
        'cat_eye_ear' => 'Eye & Ear',
        'cat_dental' => 'Dental',
        'cat_allergy' => 'Allergy',
        'cat_gastric' => 'Gastric',
        'cat_pain' => 'Pain Relief',
        
        // Product Cards
        'add_to_cart' => 'Add to Cart',
        'buy_now' => 'Buy Now',
        'in_stock' => 'In Stock',
        'out_of_stock' => 'Out of Stock',
        'low_stock' => 'Low Stock',
        'price' => 'Price',
        'view_details' => 'View Details',
        
        // Cart & Checkout
        'your_cart' => 'Your Cart',
        'cart_empty' => 'Your cart is empty',
        'continue_shopping' => 'Continue Shopping',
        'proceed_checkout' => 'Proceed to Checkout',
        'subtotal' => 'Subtotal',
        'delivery_charge' => 'Delivery Charge',
        'discount' => 'Discount',
        'grand_total' => 'Grand Total',
        'delivery_info' => 'Delivery Information',
        'full_name' => 'Full Name',
        'phone' => 'Phone Number',
        'address' => 'Delivery Address',
        'payment_method' => 'Payment Method',
        'cash_on_delivery' => 'Cash on Delivery',
        'place_order' => 'Place Order',
        'use_points' => 'Use Points',
        
        // Order Status
        'order_id' => 'Order ID',
        'status' => 'Status',
        'processing' => 'Processing',
        'packed' => 'Packed',
        'ready' => 'Ready',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
        
        // Auth Pages
        'email' => 'Email Address',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'remember_me' => 'Remember Me',
        'forgot_password' => 'Forgot Password?',
        'have_account' => 'Already have an account?',
        'no_account' => 'Don\'t have an account?',
        'create_account' => 'Create Account',
        'staff_code' => 'Staff Verification Code',
        
        // POS System
        'pos_terminal' => 'POS Terminal',
        'member_id' => 'Member ID',
        'customer_name' => 'Customer Name',
        'check' => 'Check',
        'redeem' => 'Redeem',
        'complete_sale' => 'Complete Sale',
        'print_invoice' => 'Print Invoice',
        'vat' => 'VAT',
        
        // Admin/Manager
        'total_sales' => 'Total Sales',
        'total_revenue' => 'Total Revenue',
        'add_medicine' => 'Add Medicine',
        'update_stock' => 'Update Stock',
        'manage_users' => 'Manage Users',
        'manage_shops' => 'Manage Shops',
        'generate_code' => 'Generate Code',
        'action' => 'Action',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
        
        // Footer
        'follow_us' => 'Follow Us',
        'all_rights_reserved' => 'All rights reserved',
        'send_message' => 'Send Message',
        'message_sent' => 'Message Sent!',
    ],
    
    'bn' => [
        // Navigation
        'home' => 'হোম',
        'shop' => 'শপ',
        'cart' => 'কার্ট',
        'orders' => 'অর্ডার',
        'login' => 'লগইন',
        'signup' => 'সাইন আপ',
        'logout' => 'লগআউট',
        'dashboard' => 'ড্যাশবোর্ড',
        'profile' => 'প্রোফাইল',
        'inventory' => 'ইনভেন্টরি',
        'parcels' => 'পার্সেল',
        'users' => 'ব্যবহারকারী',
        'medicines' => 'ওষুধ',
        'reports' => 'রিপোর্ট',
        
        // Homepage Hero
        'hero_title' => 'আপনার বিশ্বস্ত অনলাইন ফার্মেসি',
        'hero_subtitle' => 'খাঁটি ওষুধ আপনার দোরগোড়ায়',
        'search_placeholder' => 'ওষুধ খুঁজুন (যেমন: নাপা, এইস)...',
        'upload_prescription' => 'প্রেসক্রিপশন আপলোড',
        'shop_now' => 'এখনই কিনুন',
        
        // Homepage Sections
        'shop_by_concerns' => 'সমস্যা অনুযায়ী কিনুন',
        'flash_sale' => 'ফ্ল্যাশ সেল',
        'featured_products' => 'সেরা পণ্যসমূহ',
        'customer_reviews' => 'গ্রাহকদের মতামত',
        'health_tips' => 'স্বাস্থ্য টিপস',
        'latest_news' => 'সর্বশেষ খবর',
        'about_us' => 'আমাদের সম্পর্কে',
        'contact_us' => 'যোগাযোগ',
        
        // Categories
        'cat_heart' => 'হৃদরোগ',
        'cat_diabetes' => 'ডায়াবেটিস',
        'cat_baby_care' => 'শিশু যত্ন',
        'cat_skin' => 'চর্মরোগ',
        'cat_orthopedic' => 'হাড় ও জয়েন্ট',
        'cat_eye_ear' => 'চোখ ও কান',
        'cat_dental' => 'দাঁত ও মুখ',
        'cat_allergy' => 'এলার্জি',
        'cat_gastric' => 'গ্যাস্ট্রিক',
        'cat_pain' => 'ব্যথানাশক',
        
        // Product Cards
        'add_to_cart' => 'অ্যাড করুন',
        'buy_now' => 'কিনুন',
        'in_stock' => 'স্টকে আছে',
        'out_of_stock' => 'স্টক শেষ',
        'low_stock' => 'সীমিত স্টক',
        'price' => 'মূল্য',
        'view_details' => 'বিস্তারিত দেখুন',
        
        // Cart & Checkout
        'your_cart' => 'আপনার কার্ট',
        'cart_empty' => 'আপনার কার্ট খালি',
        'continue_shopping' => 'কেনাকাটা চালিয়ে যান',
        'proceed_checkout' => 'চেকআউটে যান',
        'subtotal' => 'সাবটোটাল',
        'delivery_charge' => 'ডেলিভারি চার্জ',
        'discount' => 'ছাড়',
        'grand_total' => 'সর্বমোট',
        'delivery_info' => 'ডেলিভারি তথ্য',
        'full_name' => 'পুরো নাম',
        'phone' => 'ফোন নম্বর',
        'address' => 'ঠিকানা',
        'payment_method' => 'পেমেন্ট পদ্ধতি',
        'cash_on_delivery' => 'ক্যাশ অন ডেলিভারি',
        'place_order' => 'অর্ডার করুন',
        'use_points' => 'পয়েন্ট ব্যবহার করুন',
        
        // Order Status
        'order_id' => 'অর্ডার আইডি',
        'status' => 'অবস্থা',
        'processing' => 'প্রক্রিয়াধীন',
        'packed' => 'প্যাক করা হয়েছে',
        'ready' => 'প্রস্তুত',
        'out_for_delivery' => 'ডেলিভারির পথে',
        'delivered' => 'ডেলিভারড',
        'cancelled' => 'বাতিল',
        'returned' => 'ফেরত',
        
        // Auth Pages
        'email' => 'ইমেইল',
        'password' => 'পাসওয়ার্ড',
        'confirm_password' => 'পাসওয়ার্ড নিশ্চিত করুন',
        'remember_me' => 'মনে রাখুন',
        'forgot_password' => 'পাসওয়ার্ড ভুলে গেছেন?',
        'have_account' => 'ইতিমধ্যে অ্যাকাউন্ট আছে?',
        'no_account' => 'অ্যাকাউন্ট নেই?',
        'create_account' => 'অ্যাকাউন্ট খুলুন',
        'staff_code' => 'স্টাফ ভেরিফিকেশন কোড',
        
        // POS System
        'pos_terminal' => 'POS টার্মিনাল',
        'member_id' => 'মেম্বার আইডি',
        'customer_name' => 'গ্রাহকের নাম',
        'check' => 'চেক',
        'redeem' => 'রিডিম',
        'complete_sale' => 'সেল সম্পন্ন করুন',
        'print_invoice' => 'রিসিট প্রিন্ট',
        'vat' => 'ভ্যাট',
        
        // Admin/Manager
        'total_sales' => 'মোট বিক্রি',
        'total_revenue' => 'মোট আয়',
        'add_medicine' => 'ওষুধ যোগ করুন',
        'update_stock' => 'স্টক আপডেট',
        'manage_users' => 'ইউজার ম্যানেজমেন্ট',
        'manage_shops' => 'দোকান ম্যানেজমেন্ট',
        'generate_code' => 'কোড তৈরি করুন',
        'action' => 'অ্যাকশন',
        'edit' => 'এডিট',
        'delete' => 'ডিলিট',
        'save' => 'সেভ',
        'cancel' => 'বাতিল',
        
        // Footer
        'follow_us' => 'আমাদের অনুসরণ করুন',
        'all_rights_reserved' => 'সর্বস্বত্ব সংরক্ষিত',
        'send_message' => 'মেসেজ পাঠান',
        'message_sent' => 'মেসেজ পাঠানো হয়েছে!',
    ]
];

/**
 * Translation Helper Function
 */
function __($key) {
    global $translations, $currentLang;
    return $translations[$currentLang][$key] ?? $key;
}

/**
 * Get Opposite Language Code
 */
function getOppositeLang() {
    global $currentLang;
    return $currentLang === 'en' ? 'bn' : 'en';
}

/**
 * Get Opposite Language Name
 */
function getOppositeLangName() {
    global $currentLang;
    return $currentLang === 'en' ? 'বাংলা' : 'English';
}
?>