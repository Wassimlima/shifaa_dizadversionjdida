<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/auth.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شفاء - منصة الخدمات الطبية الجزائرية | Shifaa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;700&amp;family=Inter:wght@400;500;600&amp;display=swap');
        
        body {
            font-family: 'Noto Sans Arabic', 'Inter', system-ui, sans-serif;
        }
        
        .nav-link {
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: #0ea47a;
            transform: translateY(-1px);
        }
        
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        
        .section-title {
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -8px;
            right: 0;
            width: 60%;
            height: 3px;
            background: linear-gradient(to left, #0ea47a, transparent);
            border-radius: 3px;
        }
        
        .wilaya-badge {
            background: linear-gradient(135deg, #0ea47a, #0a7d5e);
            color: white;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .dashboard-nav {
            background: linear-gradient(to bottom, #0f172a, #1e2937);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <!-- Top Bar -->
    <div class="bg-emerald-800 text-white py-2 text-sm">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <span><i class="fas fa-map-marker-alt ml-1"></i> الجزائر - 69 ولاية</span>
                <span class="hidden md:inline">| دعم 24/7</span>
            </div>
            <div>
                <?php if ($currentUser): ?>
                    <span class="text-emerald-200">مرحباً، <?= htmlspecialchars($currentUser['full_name']) ?></span>
                <?php else: ?>
                    <a href="login.php" class="hover:text-emerald-200">تسجيل الدخول</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="index.php" class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-heartbeat text-white text-2xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-2xl text-emerald-700">شفاء</span>
                        <span class="text-xs text-gray-500 block -mt-1">Shifaa.dz</span>
                    </div>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                    <a href="index.php" class="nav-link">الرئيسية</a>
                    <a href="index.php#wilayas" class="nav-link">الولايات (69)</a>
                    <a href="pricing.php" class="nav-link">التسعير</a>
                    <a href="index.php#how" class="nav-link">كيف يعمل</a>
                </div>

                <div class="flex items-center gap-3">
                    <?php if ($currentUser): ?>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <a href="admin/admin_dashboard.php" 
                               class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg flex items-center gap-2">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>لوحة الإدارة</span>
                            </a>
                        <?php else: ?>
                            <a href="#" onclick="goToMyDashboard()" 
                               class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg flex items-center gap-2">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>لوحتي</span>
                            </a>
                        <?php endif; ?>
                        
                        <a href="logout.php" 
                           class="px-4 py-2 text-sm border border-gray-300 hover:bg-gray-100 rounded-lg flex items-center gap-2 text-gray-700">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>خروج</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" 
                           class="px-5 py-2 text-sm font-medium border border-emerald-600 text-emerald-700 hover:bg-emerald-50 rounded-lg">
                            تسجيل الدخول
                        </a>
                        <a href="pricing.php" 
                           class="px-5 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">
                            اشترك الآن
                        </a>
                    <?php endif; ?>

                    <!-- Mobile Menu Button -->
                    <button class="md:hidden p-2" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            if (menu) menu.classList.toggle('hidden');
        }
        
        function goToMyDashboard() {
            window.location.href = 'login.php';
        }
    </script>
