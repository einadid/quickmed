# 🌐 InfinityFree-তে QuickMed Deploy — সম্পূর্ণ গাইড (Bangla)

**Target Site:** https://quickmed.free.nf/
**সময় লাগবে:** প্রায় ১৫–২০ মিনিট

> এই গাইড অনুসরণ করলে GitHub-এর কোড সরাসরি InfinityFree-তে live হবে।
> `config.php` আগেই Live সেটিংসহ তৈরি — DB পাসওয়ার্ড বসানো আছে,
> তাই কোডে হাত দিতে হবে না। ✅

---

## 📋 যা যা লাগবে

| জিনিস | কোথায় পাবেন |
|---|---|
| InfinityFree অ্যাকাউন্ট | [app.infinityfree.com](https://app.infinityfree.com) (ফ্রি) |
| Domain | `quickmed.free.nf` (অ্যাকাউন্টে আগেই আছে) |
| GitHub কোড | এই repository (`arena/01a094ef-quickmed` branch) |
| DB তথ্য (আগেই `config.php`-তে বসানো) | Host: `sql112.infinityfree.com` · DB: `if0_40419807_quickmed_db` |

---

## ধাপ ১️⃣ — GitHub থেকে কোড ডাউনলোড

1. GitHub-এ repository পেজে যান → Branch: **`arena/01a094ef-quickmed`** সিলেক্ট করুন
2. সবুজ **Code** বাটন → **Download ZIP**
3. ZIP extract করুন → ভেতরে `quickmed/` ফোল্ডার পাবেন

> ⚠️ **মনে রাখুন:** আপলোডের সময় `quickmed` ফোল্ডারটা নয়,
> **ফোল্ডারের ভেতরের ফাইলগুলো** আপলোড করতে হবে।

---

## ধাপ ২️⃣ — InfinityFree File Manager-এ আপলোড

1. [app.infinityfree.com](https://app.infinityfree.com) → লগইন → আপনার অ্যাকাউন্ট
   (`quickmed.free.nf`) → **File Manager** খুলুন
   (অথবা Control Panel → **Online File Manager**)
2. **`htdocs/`** ফোল্ডারের ভেতরে ঢুকুন
3. `htdocs`-এর ভেতরের ডিফল্ট ফাইল (`index2.html` ইত্যাদি) থাকলে **ডিলিট** করুন
4. ZIP থেকে extract করা **`quickmed/` ফোল্ডারের ভেতরের সবকিছু** `htdocs/`-তে আপলোড করুন

**সঠিক হলে `htdocs/` দেখতে এমন হবে:**

```
htdocs/
├── index.php
├── config.php          ← Live DB সেটিংসহ (হাত দিতে হবে না ✅)
├── shop.php, cart.php, checkout.php, ...
├── assets/
├── includes/
├── views/
├── database/
│   ├── quickmed.sql
│   └── seed_admin.php
└── uploads/            ← (না থাকলে config.php নিজে বানিয়ে নেবে)
```

> ❌ **ভুল:** `htdocs/quickmed/index.php` (ভেতরে আরেকটা ফোল্ডার)
> ✅ **সঠিক:** `htdocs/index.php` (সরাসরি ফাইল)

💡 **বড় ফাইল একসাথে আপলোড করতে সমস্যা হলে:** FileZilla (FTP) ব্যবহার করুন —
FTP host/username/password পাবেন InfinityFree Control Panel → **FTP Details**-এ।

---

## ধাপ ৩️⃣ — Database তৈরি + SQL Import 🗄️

### ৩.১ — Database আছে কিনা দেখুন

1. InfinityFree Control Panel → **MySQL Databases**
2. Database **`if0_40419807_quickmed_db`** আগে থেকে থাকার কথা
   (এই নামটাই `config.php`-তে বসানো আছে)
3. না থাকলে **Create Database** দিয়ে বানান — নাম যাই হোক,
   তাহলে `config.php`-এর LIVE অংশে `DB_NAME` আপডেট করতে হবে

### ৩.২ — SQL ফাইল Import

1. Control Panel → **phpMyAdmin** খুলুন → বাম পাশে
   `if0_40419807_quickmed_db` সিলেক্ট করুন
2. উপরে **Import** ট্যাব → **Choose File** →
   আপনার কম্পিউটার থেকে `database/quickmed.sql` সিলেক্ট করুন
3. নিচে **Import/Go** চাপুন
4. ✅ Success মেসেজ + 20টা টেবিল (`users`, `orders`, `medicines`...) দেখা যাবে

**এই SQL ফাইলে যা আছে:**
- ২০টি টেবিল (সম্পূর্ণ schema)
- ৫টি Role (customer, admin, shop_manager, doctor, salesman)
- ১টি Demo Shop (QuickMed Main Branch, Chattogram)
- ❌ কোনো user **নেই** — admin পরের ধাপে বানাবেন (নিরাপত্তার জন্য)

> ⚠️ আগে পুরনো টেবিল থাকলে সমস্যা নেই — SQL-এ
> `CREATE TABLE IF NOT EXISTS` ব্যবহার করা হয়েছে।
> তবে পুরনো ভাঙা টেবিল থাকলে সেগুলো **Drop** করে fresh import করা ভালো।

---

## ধাপ ৪️⃣ — Admin অ্যাকাউন্ট তৈরি 👑

1. ব্রাউজারে যান: **`https://quickmed.free.nf/database/seed_admin.php`**
2. সবুজ success মেসেজ দেখাবে — admin তৈরি! ✅
3. **এক্ষুনি File Manager থেকে `htdocs/database/seed_admin.php` ফাইলটি ডিলিট করুন!**
   (নিরাপত্তার জন্য — এটা one-time script)

**Admin লগইন:**

| | |
|---|---|
| 🌐 URL | `https://quickmed.free.nf/login.php` |
| 📧 Email | `admin@quickmed.com` |
| 🔑 Password | `Admin@123` |

> ⚠️ লগইন করেই **Profile → পাসওয়ার্ড বদলে নিন!**

---

## ধাপ ৫️⃣ — যাচাই করুন ✔️

| চেক | URL |
|---|---|
| 🏠 Homepage খোলে? | `https://quickmed.free.nf/` |
| 🛍️ Shop + ছবি আসে? | `https://quickmed.free.nf/shop.php` |
| 🔑 Admin লগইন হয়? | `https://quickmed.free.nf/login.php` |
| 📊 Admin dashboard? | লগইনের পর `views/admin/dashboard.php` |

---

## ❓ সমস্যা হলে (Troubleshooting)

| সমস্যা | সমাধান |
|---|---|
| **"System is currently under maintenance"** | DB কানেকশন ফেল — Control Panel → MySQL Databases → Host (`sql112...`), DB নাম, পাসওয়ার্ড `config.php`-এর LIVE অংশের সাথে মিলিয়ে দেখুন |
| **404 / সাদা পেজ** | ফাইল ভুল জায়গায় — `htdocs/index.php` সরাসরি থাকতে হবে, `htdocs/quickmed/` এর ভেতরে নয় |
| **ছবি আসে না** | `uploads/` ফোল্ডারের permission **755** করুন (File Manager → Right click → Permissions/Chmod) |
| **seed_admin.php-তে "roles table not found"** | ধাপ ৩-এর SQL Import হয়নি — আবার Import করুন |
| **CSS ভাঙা দেখায়** | `assets/` ফোল্ডার পুরো আপলোড হয়েছে কিনা দেখুন; Ctrl+F5 দিয়ে refresh করুন |
| **InfinityFree "suspended" / slow প্রথমবার** | প্রথম ভিজিটে সার্ভার জাগতে ৩০–৬০ সেকেন্ড লাগতে পারে — একটু অপেক্ষা করে refresh করুন |

---

## 🔄 পরে আবার আপডেট দিতে হলে

1. GitHub থেকে নতুন ZIP ডাউনলোড (শুধু বদলানো ফাইলও আপলোড করতে পারেন)
2. File Manager/FTP দিয়ে `htdocs/`-তে overwrite করুন
3. ⚠️ **`config.php` overwrite করবেন না** যদি live-তে কিছু বদলে থাকেন!
4. DB-তে নতুন টেবিল/কলাম লাগলে শুধু সেই SQL টুকু phpMyAdmin → **SQL** ট্যাবে চালান

---

## 📁 কী আপলোড করবেন / কী করবেন না

| ✅ আপলোড করবেন (`htdocs/`) | ❌ দরকার নেই |
|---|---|
| সব `.php` ফাইল | `.git/` ফোল্ডার |
| `assets/`, `includes/`, `views/` | `node_modules/` (যদি থাকে) |
| `database/` (seed-এর পর `seed_admin.php` ডিলিট!) | এই `.md` গাইড ফাইল (ঐচ্ছিক) |
| `uploads/` (খালি হলেও চলবে — auto-create হয়) | — |

---
Made with ❤️ in Bangladesh 🇧🇩 — QuickMed v2.0
