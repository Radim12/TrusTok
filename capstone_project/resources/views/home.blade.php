<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrustTok - Dashboard Analisis Sentimen TikTok Terpercaya</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS (CDN for instant preview, integrated beautifully with custom classes) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Space Grotesk', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Laravel Vite Bundler (Loads separated assets) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js (Lightweight interactive engine for pure Blade approach - Loaded after script initialization) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gradient-mesh text-gray-100 min-h-screen relative overflow-x-hidden selection:bg-purple-500/20"
    x-data="dashboard" x-effect="document.body.style.overflow = isLoading ? 'hidden' : 'auto'">

    <div x-show="isLoading" x-cloak
        class="fixed inset-0 bg-[#0f1021]/85 backdrop-blur-md z-[9999] flex flex-col items-center justify-center pointer-events-auto">
        <div class="relative flex items-center justify-center">
            <div class="w-16 h-16 border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin"></div>
        </div>
        <p class="mt-6 text-white text-lg font-medium tracking-[0.2em] animate-pulse">
            WAIT...
        </p>
    </div>


    <!-- Background glow lines overlapping the mesh dynamically -->
    <div
        class="absolute top-[5%] left-1/2 -translate-x-1/2 w-full max-w-7xl h-[1000px] pointer-events-none select-none z-0">
        <div
            class="absolute left-[-20%] top-0 w-[60%] h-[600px] bg-gradient-to-tr from-indigo-500/10 via-purple-500/5 to-transparent rounded-full blur-[120px]">
        </div>
        <div
            class="absolute right-[-20%] top-[400px] w-[60%] h-[600px] bg-gradient-to-br from-pink-500/5 via-violet-500/10 to-transparent rounded-full blur-[130px]">
        </div>
    </div>

    <!-- Header Navigation -->
    <header
        class="sticky top-0 z-50 w-full bg-[#070913]/85 backdrop-blur-md border-b border-white/5 px-4 sm:px-12 py-5 transition-all duration-300">
        <div class="max-w-7xl mx-auto flex items-center justify-between relative z-10">
            <!-- Logo / Title -->
            <div @click="scrollToSection('home')"
                class="cursor-pointer font-display font-bold text-base sm:text-lg text-white tracking-wider hover:opacity-90 transition-opacity flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 shadow-[0_0_8px_#a855f7]"></span>
                TrustTok
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-6 lg:gap-8">
                <button @click="scrollToSection('home')"
                    :class="activeTab === 'home' ? 'text-white' : 'text-gray-400 hover:text-white'"
                    class="relative font-sans text-xs sm:text-[13px] font-medium transition-all duration-300 select-none cursor-pointer py-1">
                    Home
                    <span x-show="activeTab === 'home'"
                        class="absolute -bottom-1 left-0 w-full h-[1px] bg-purple-400 shadow-[0_0_8px_rgba(168,85,247,0.5)]"></span>
                </button>
                <button @click="scrollToSection('trends')"
                    :class="activeTab === 'trends' ? 'text-white' : 'text-gray-400 hover:text-white'"
                    class="relative font-sans text-xs sm:text-[13px] font-medium transition-all duration-300 select-none cursor-pointer py-1">
                    Product
                    <span x-show="activeTab === 'trends'"
                        class="absolute -bottom-1 left-0 w-full h-[1px] bg-purple-400 shadow-[0_0_8px_rgba(168,85,247,0.5)]"></span>
                </button>
                <button @click="scrollToSection('scraper')"
                    :class="activeTab === 'scraper' ? 'text-white' : 'text-gray-400 hover:text-white'"
                    class="relative font-sans text-xs sm:text-[13px] font-medium transition-all duration-300 select-none cursor-pointer py-1">
                    Brand
                    <span x-show="activeTab === 'scraper'"
                        class="absolute -bottom-1 left-0 w-full h-[1px] bg-purple-400 shadow-[0_0_8px_rgba(168,85,247,0.5)]"></span>
                </button>
                <button @click="scrollToSection('evaluation')"
                    :class="activeTab === 'evaluation' ? 'text-white' : 'text-gray-400 hover:text-white'"
                    class="relative font-sans text-xs sm:text-[13px] font-medium transition-all duration-300 select-none cursor-pointer py-1">
                    Sentiment
                    <span x-show="activeTab === 'evaluation'"
                        class="absolute -bottom-1 left-0 w-full h-[1px] bg-purple-400 shadow-[0_0_8px_rgba(168,85,247,0.5)]"></span>
                </button>
            </nav>

            <!-- Mobile Hamburger Toggle -->
            <button @click="isMobileMenuOpen = !isMobileMenuOpen"
                class="flex md:hidden text-gray-400 hover:text-white p-2 transition-colors focus:outline-none cursor-pointer"
                aria-label="Toggle Menu">
                <svg x-show="!isMobileMenuOpen" class="w-6 h-6 animate-fade-in" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
                <svg x-show="isMobileMenuOpen" class="w-6 h-6 animate-fade-in" x-cloak fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="isMobileMenuOpen" x-cloak @click.away="isMobileMenuOpen = false"
            class="md:hidden absolute left-0 top-full w-full bg-[#070913]/98 border-b border-white/5 shadow-2xl backdrop-blur-md transition-all duration-300 z-40 py-4 flex flex-col space-y-2 px-6">
            <button @click="scrollToSection('home')"
                :class="activeTab === 'home' ? 'text-purple-400 border-l-2 border-purple-400 pl-3 -ml-3' : 'text-gray-300 hover:text-white'"
                class="w-full text-left py-3 font-sans text-xs sm:text-[13px] font-medium">
                Home
            </button>
            <button @click="scrollToSection('trends')"
                :class="activeTab === 'trends' ? 'text-purple-400 border-l-2 border-purple-400 pl-3 -ml-3' : 'text-gray-300 hover:text-white'"
                class="w-full text-left py-3 font-sans text-xs sm:text-[13px] font-medium">
                Trend Explorer
            </button>
            <button @click="scrollToSection('scraper')"
                :class="activeTab === 'scraper' ? 'text-purple-400 border-l-2 border-purple-400 pl-3 -ml-3' : 'text-gray-300 hover:text-white'"
                class="w-full text-left py-3 font-sans text-xs sm:text-[13px] font-medium">
                Live Scraper
            </button>
            <button @click="scrollToSection('evaluation')"
                :class="activeTab === 'evaluation' ? 'text-purple-400 border-l-2 border-purple-400 pl-3 -ml-3' : 'text-gray-300 hover:text-white'"
                class="w-full text-left py-3 font-sans text-xs sm:text-[13px] font-medium">
                Model Evaluation
            </button>
        </div>
    </header>

    <main class="space-y-24 pb-20 relative z-10 select-none">

        <!-- 1. HERO SECTION -->
        <div id="home" class="scroll-mt-28 space-y-24">
            <section class="pointer-events-none select-none absolute top-0 left-0 w-full h-[600px] z-0 opacity-40">
                <!-- Sparkle effect -->
                <span class="absolute left-[15%] top-[150px] text-lg text-purple-400 animate-pulse">✦</span>
                <span class="absolute right-[12%] top-[300px] text-xl text-indigo-400 animate-bounce">✦</span>
            </section>

            <section class="relative text-center max-w-4xl mx-auto pt-14 px-4 space-y-6 z-10">
                <p class="font-sans text-[13px] tracking-[0.2em] text-[#8e99b2] font-normal uppercase">
                    Welcome to TrustTok
                </p>

                <h2 class="font-display text-4xl sm:text-[50px] leading-[1.12] font-semibold text-white tracking-wide">
                    Spot Trending Products <br />
                    on TikTok in Seconds
                </h2>

                <p
                    class="text-[#8992ad] text-xs sm:text-[13px] leading-relaxed max-w-3xl mx-auto font-light tracking-wide opacity-95">
                    Harness the power of AI to extract viral products and analyze customer sentiment instantly.<br />
                    Perfect for local retail businesses, smart sellers, and social media monitoring<br />
                    to prevent deadstock and catch the next big wave!
                </p>
            </section>

            <!-- 2. THREE ARCHITECTURAL CAPABILITY CARDS -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-7xl mx-auto px-4 relative z-10">
                <!-- Card 1 -->
                <div
                    class="bg-[#101222]/50 hover:bg-[#121528]/80 border border-white/5 rounded-2xl p-7 sm:p-9 transition-all duration-300 hover:-translate-y-1">
                    <h3 class="font-display font-medium text-base text-white mb-4 tracking-wide">
                        Otomatisasi Pencarian Cerdas
                    </h3>
                    <p class="text-[#8089a8] text-[12px] leading-relaxed font-light">
                        Cukup pilih produk yang Anda inginkan. Sistem kami akan mencari video TikTok paling relevan
                        secara otomatis.
                    </p>
                </div>

                <!-- Card 2 -->
                <div
                    class="bg-[#101222]/50 hover:bg-[#121528]/80 border border-white/5 rounded-2xl p-7 sm:p-9 transition-all duration-300 hover:-translate-y-1">
                    <h3 class="font-display font-medium text-base text-white mb-4 tracking-wide">
                        Ekstraksi Entitas Akurat
                    </h3>
                    <p class="text-[#8089a8] text-[12px] leading-relaxed font-light">
                        Sistem kami otomatis menyaring ulasan, memisahkan nama produk asli dan brand dari ketikan slang
                        TikTok yang kotor dan berantakan.
                    </p>
                </div>

                <!-- Card 3 -->
                <div
                    class="bg-[#101222]/50 hover:bg-[#121528]/80 border border-white/5 rounded-2xl p-7 sm:p-9 transition-all duration-300 hover:-translate-y-1">
                    <h3 class="font-display font-medium text-base text-white mb-4 tracking-wide">
                        Validasi Kepercayaan Pasar
                    </h3>
                    <p class="text-[#8089a8] text-[12px] leading-relaxed font-light">
                        Menghitung rasio ulasan positif, netral, dan negatif untuk memastikan apakah produk viral
                        tersebut benar-benar tepercaya atau berisiko komplain.
                    </p>
                </div>
            </section>
        </div>

        <!-- Section Divider -->
        <div class="h-[1px] w-full bg-gradient-to-r from-transparent via-gray-800/60 to-transparent"></div>

        <!-- 3. DUAL-COLUMN DATA FILTERING & COMMENT VIEW -->
        <div id="trends" class="scroll-mt-28 relative overflow-visible">

            <!-- Constellation Background element (bottom-left) -->
            <div class="absolute left-[-20px] bottom-[-20px] w-64 h-64 opacity-25 pointer-events-none select-none z-0">
                <svg class="w-full h-full text-purple-600" fill="none" viewBox="0 0 100 100" stroke="currentColor"
                    stroke-width="0.8">
                    <path d="M 20 80 L 32 72 L 44 80 L 32 88 Z" />
                    <path d="M 32 72 L 44 64 L 56 72 L 44 80 Z" />
                    <path d="M 8 72 L 20 64 L 32 72 L 20 80 Z" />
                    <path d="M 20 64 L 32 56 L 44 64 L 32 72 Z" />
                    <circle cx="20" cy="80" r="1.2" fill="white" />
                    <circle cx="32" cy="72" r="1.2" fill="white" />
                    <circle cx="44" cy="80" r="1.2" fill="white" />
                    <circle cx="32" cy="88" r="1.2" fill="white" />
                </svg>
            </div>

            <section class="max-w-7xl mx-auto px-4 md:px-8 pt-8 space-y-2 relative z-10">

                <!-- Header Row -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- Dropdown filter on left -->
                    <div class="lg:col-span-6 space-y-5">
                        <h3 class="font-sans text-xl sm:text-[22px] font-semibold text-slate-100 tracking-wide">
                            Smart Filter Product
                        </h3>

                        <div class="relative w-full max-w-lg">
                            <button @click="isDropdownOpen = !isDropdownOpen"
                                class="w-full h-[54px] flex items-center justify-between px-6 rounded-xl bg-[#202136]/55 border border-white/10 hover:border-purple-400/40 text-slate-200 text-base font-normal tracking-wide transition-all text-left cursor-pointer shadow-lg backdrop-blur-md">

                                <span class="opacity-90"
                                    x-text="activeProduct === '' ? 'Pick In Option' : activeProduct">Pick In
                                    Option</span>

                                <svg class="w-5 h-5 text-purple-400 pointer-events-none" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="isDropdownOpen" x-cloak @click.away="isDropdownOpen = false"
                                class="absolute left-0 mt-2 w-full rounded-xl bg-[#141629] border border-white/10 shadow-2xl z-50 py-1.5">
                                <template x-for="prod in productList" :key="prod">
                                    <button
                                        @click="activeProduct = prod; isDropdownOpen = false; startScraping(prod.toLowerCase())"
                                        class="w-full text-left px-6 py-3 text-sm text-gray-300 hover:text-white hover:bg-purple-900/40 transition-all font-light cursor-pointer">
                                        <span x-text="prod"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div
            class="lg:col-span-6 flex items-start justify-center lg:justify-end gap-8 sm:gap-10 w-full pt-4 min-h-[320px] relative lg:pr-6 lg:mr-44">
            <div
                class="absolute inset-0 m-auto w-[380px] h-[300px] bg-gradient-to-tr from-pink-500/15 to-purple-600/10 rounded-full blur-[75px] pointer-events-none z-0">
            </div>

            <div
                class="w-[170px] sm:w-[195px] h-[230px] rounded-[28px] bg-[#16172a]/70 border border-white/15 flex flex-col items-center justify-center p-6 text-center hover:border-purple-400/40 transition-all duration-300 shadow-[0_16px_40px_rgba(0,0,0,0.6)] backdrop-blur-md z-10">
                <svg class="w-16 h-16 text-purple-400 opacity-95 mb-2" viewBox="0 0 48 48" fill="none"
                    stroke="currentColor" stroke-width="1.2">
                    <path
                        d="M41 21C41 29.5 33.5 36 24 36C21.8 36 19.8 35.6 17.8 35L10 38V31.5C6.5 28.8 4 25.2 4 21C4 12.5 12.5 6 24 6C35.5 6 41 12.5 41 21Z"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-[13px] font-sans text-slate-300 mt-2 tracking-wide font-medium">Total Comment</span>
                <span class="text-3xl sm:text-4xl font-bold text-white mt-2 leading-none tracking-wide font-display"
                    x-text="totalComments">3.000</span>
            </div>

            <div
                class="w-[170px] sm:w-[195px] h-[230px] rounded-[28px] bg-[#16172a]/70 border border-white/15 flex flex-col items-center justify-center p-6 text-center hover:border-purple-400/40 transition-all duration-300 shadow-[0_16px_40px_rgba(0,0,0,0.6)] backdrop-blur-md translate-y-12 sm:translate-y-14 z-10">
                <svg class="w-16 h-16 text-indigo-400 opacity-95 mb-2" viewBox="0 0 48 48" fill="none"
                    stroke="currentColor" stroke-width="1.2">
                    <path d="M30 17L41 11V37L30 31V17Z" stroke-linecap="round" stroke-linejoin="round" />
                    <rect x="5" y="13" width="25" height="22" rx="4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-[13px] font-sans text-slate-300 mt-2 tracking-wide font-medium">Brand
                    Terdeteksi</span>
                <span class="text-3xl sm:text-4xl font-bold text-white mt-2 leading-none tracking-wide font-display"
                    x-text="totalVideos">3</span>
            </div>
        </div>

        <!-- Animated Wave connector -->
        <div class="w-full h-16 relative pointer-events-none opacity-50 my-2 select-none">
            <svg class="absolute inset-0 w-full h-full text-white/5" viewBox="0 0 1200 64" fill="none"
                preserveAspectRatio="none">
                <path d="M 50 45 C 250 15, 450 65, 700 25 C 900 -5, 1100 50, 1150 15" stroke="currentColor"
                    stroke-width="0.8" fill="none" />
            </svg>
            <span class="absolute left-[3%] top-[60%] text-[14px] text-white/40 animate-pulse">✦</span>
            <span class="absolute left-[24%] top-[25%] text-[16px] text-white/60 animate-bounce">✦</span>
        </div>

        <!-- 3 Columns for comments, cleansing list, credentials -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 items-stretch relative">

            <!-- Column 1: Komentar Asli -->
            <div class="md:col-span-1 lg:col-span-4 flex relative">
                <div
                    class="absolute left-[-24px] top-[15px] pointer-events-none select-none z-20 hidden sm:flex flex-col items-center space-y-[14px]">
                    <div
                        class="w-5 h-5 rounded-full bg-gradient-to-tr from-slate-500 via-slate-200 to-slate-700 shadow-xl border border-white/20">
                    </div>
                    <div
                        class="relative w-[38px] h-[38px] rounded-full border border-white/10 shadow-2xl flex items-center justify-center overflow-hidden bg-[#fafafa]">
                        <div
                            class="absolute inset-0 bg-gradient-to-tr from-[#c27c13] via-[#ffeb7a] to-[#7c440a] w-1/2 left-0 font-sans">
                        </div>
                        <div class="absolute inset-0 bg-white/20 backdrop-blur-sm pointer-events-none"></div>
                    </div>

                    <!-- Silver/Purple Metallic 3D prism cone - RESTORED -->
                    <svg width="60" height="82" viewBox="0 0 68 88"
                        class="drop-shadow-[0_12px_24px_rgba(255,255,255,0.06)] translate-x-[-12px] translate-y-[2px]">
                        <defs>
                            <linearGradient id="pyr-left" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ffffff" />
                                <stop offset="40%" stop-color="#94a3b8" />
                                <stop offset="100%" stop-color="#2e2b4f" />
                            </linearGradient>
                            <linearGradient id="pyr-right" x1="100%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#e2e8f0" />
                                <stop offset="50%" stop-color="#3b3b61" />
                                <stop offset="100%" stop-color="#0a0a1a" />
                            </linearGradient>
                        </defs>
                        <polygon points="34,6 10,72 34,80" fill="url(#pyr-left)" />
                        <polygon points="34,6 58,68 34,80" fill="url(#pyr-right)" />
                    </svg>
                </div>

                <!-- Card container -->
                <div class="relative h-[380px] sm:h-[480px] w-full p-[1px] bg-gradient-to-br from-purple-500/20 to-indigo-500/10 shadow-xl"
                    style="clip-path: polygon(24px 0, 100% 0, 100% calc(100% - 24px), calc(100% - 24px) 100%, 0 100%, 0 24px);">
                    <div class="absolute inset-[1px] bg-[#121327]/85 flex flex-col p-6 sm:p-8"
                        style="clip-path: polygon(23px 0, 100% 0, 100% calc(100% - 23px), calc(100% - 23px) 100%, 0 100%, 0 23px);">
                        <div class="flex justify-center mb-6 pt-1">
                            <span
                                class="px-7 py-1.5 rounded-full border border-purple-500/25 bg-purple-950/20 text-[12px] font-sans font-normal tracking-wide text-white select-none">
                                Komentar Asli
                            </span>
                        </div>
                        <div class="overflow-y-auto space-y-4 flex-1 pr-1 scrollbar-thin transition-all duration-500 ease-out"
                            :class="animateIn ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'">
                            <template x-for="(cmt, index) in scrapedComments" :key="cmt.id">
                                <p
                                    class="text-xs sm:text-[13px] font-sans text-slate-300 leading-relaxed border-b border-white/5 pb-2">
                                    <span x-text="(index + 1) + '. '"></span>
                                    <span x-text="cmt.original.replace(/^\s*\d+\.\s*/, '')"></span>
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Hasil Cleansing -->
            <div class="md:col-span-1 lg:col-span-4 flex relative">
                <div class="relative h-[380px] sm:h-[480px] w-full p-[1px] bg-gradient-to-br from-purple-500/20 to-indigo-500/10 shadow-xl"
                    style="clip-path: polygon(24px 0, 100% 0, 100% calc(100% - 24px), calc(100% - 24px) 100%, 0 100%, 0 24px);">
                    <div class="absolute inset-[1px] bg-[#121327]/85 flex flex-col p-6 sm:p-8"
                        style="clip-path: polygon(23px 0, 100% 0, 100% calc(100% - 23px), calc(100% - 23px) 100%, 0 100%, 0 23px);">
                        <div class="flex justify-center mb-6 pt-1">
                            <span
                                class="px-7 py-1.5 rounded-full border border-purple-500/25 bg-purple-950/20 text-[12px] font-sans font-normal tracking-wide text-white select-none">
                                Hasil Cleansing
                            </span>
                        </div>
                        <div class="overflow-y-auto space-y-4 flex-1 pr-1 scrollbar-thin transition-all duration-500 ease-out"
                            :class="animateIn ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'">
                            <template x-for="(cmt, index) in scrapedComments" :key="'clean_'+cmt.id">
                                <p
                                    class="text-xs sm:text-[13px] font-sans text-slate-300 leading-relaxed border-b border-white/5 pb-2">
                                    <span x-text="(index + 1) + '. '"></span>
                                    <span x-text="cmt.cleansed.replace(/^\d+\.\s*/, '')"></span>
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 3: Credentials and Bevel frame -->
            <div
                class="md:col-span-2 lg:col-span-4 bg-transparent p-4 flex flex-col justify-between min-h-[380px] sm:min-h-[480px] relative">
                <div class="space-y-4">
                    <div class="flex">
                        <span
                            class="px-4 py-1.5 rounded-full border border-purple-400/30 bg-purple-900/15 text-[11px] text-slate-200 font-sans tracking-wide font-normal select-none">
                            Our Credentials
                        </span>
                    </div>

                    <h3
                        class="font-sans text-[34px] sm:text-[36px] font-semibold text-white tracking-normal leading-[1.18]">
                        Expertise You Can <br />
                        Rely On
                    </h3>

                    <div
                        class="text-slate-300/95 text-[13.5px] sm:text-[14px] leading-relaxed font-normal font-sans space-y-5 relative">
                        <p>
                            Sistem <strong class="text-white font-semibold">TrustTok</strong> memotong jalur
                            riset pasar konvensional yang melelahkan. Melalui mesin pencari dinamis, aplikasi
                            ini mengikis (scraping) tumpukan opini konsumen dari video-video viral TikTok secara
                            real-time.
                        </p>
                        <p class="pr-5">
                            Lewat pipeline text-processing, ulasan yang kotor disaring menjadi teks standar
                            baku. Kecerdasan buatan kami kemudian melakukan labeling otomatis untuk memisahkan
                            entitas nama produk dan kategori sentimennya.
                        </p>
                        <div
                            class="absolute bottom-[4px] right-[4px] w-3.5 h-3.5 rounded-full bg-gradient-to-tr from-slate-400 via-slate-100 to-slate-600 shadow-lg border border-white/20 select-none z-10">
                        </div>
                    </div>
                </div>

                <!-- Slanted gold frame elements -->
                <div class="relative pt-6 flex items-center justify-end">
                    <div class="relative w-full h-[150px] flex items-center justify-end">
                        <div class="absolute bottom-2 right-12 w-[160px] h-[110px] border-[10px] border-solid rounded-sm transform rotate-[16deg] shadow-[0_16px_36px_rgba(0,0,0,0.65)] flex items-center justify-center pointer-events-none select-none z-10"
                            style="border-image: linear-gradient(135deg, #8c5230 0%, #d4af37 25%, #ffd700 50%, #b8860b 75%, #5c3a21 100%) 10;">
                            <div class="w-full h-full bg-[#111226]/90 border border-white/5"></div>
                        </div>
                        <div
                            class="absolute bottom-[36px] right-[132px] w-[42px] h-[42px] rounded-full border border-white/10 shadow-2xl flex items-center justify-center overflow-hidden bg-white transform -rotate-12 pointer-events-none select-none z-20">
                            <div
                                class="absolute inset-0 bg-gradient-to-tr from-[#9e5f1e] via-[#eec550] to-[#fff5af] w-1/2 left-0">
                            </div>
                            <div class="absolute inset-0 bg-white/20 backdrop-blur-[1px]"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        </section>
        </div>

        <div class="h-[1px] w-full bg-gradient-to-r from-transparent via-gray-800/60 to-transparent"></div>

        <!-- 4. BRAND RECOGNITION PERFORMANCE CONTIGUOUS COLUMNS -->
        <div id="scraper" class="scroll-mt-28">
            <section
                class="max-w-7xl mx-auto px-6 py-16 bg-[#0c0e1a]/45 border border-white/5 rounded-3xl space-y-16 shadow-[0_12px_44px_rgba(0,0,0,0.5)] transition-all relative backdrop-blur-md">

                <!-- Geometric abstract pattern background -->
                <div class="absolute top-0 left-0 w-48 h-48 opacity-[0.12] pointer-events-none select-none">
                    <svg class="w-full h-full text-purple-500" fill="none" viewBox="0 0 100 100" stroke="currentColor"
                        stroke-width="0.8">
                        <path d="M 20 10 L 35 18 L 35 34 L 20 42 L 5 34 L 5 18 Z" />
                        <path d="M 35 18 L 50 10 L 65 18 L 65 34 L 50 42 L 35 34 Z" />
                    </svg>
                </div>

                <!-- Floating Coordinates 3D Ring asset - RESTORED -->
                <div
                    class="absolute right-6 top-6 w-36 h-36 hidden lg:block pointer-events-none opacity-[0.85] select-none animate-float">
                    <svg viewBox="0 0 120 120" class="w-full h-full">
                        <defs>
                            <linearGradient id="gold-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#d97706" />
                                <stop offset="50%" stop-color="#fbbf24" />
                                <stop offset="100%" stop-color="#78350f" />
                            </linearGradient>
                        </defs>
                        <rect x="30" y="30" width="60" height="60" rx="3" stroke="url(#gold-gradient)"
                            stroke-width="2.5" fill="none" transform="rotate(25 60 60)" />
                        <rect x="36" y="24" width="60" height="60" rx="3" stroke="rgba(255,255,255,0.1)"
                            stroke-width="1" fill="none" transform="rotate(-5 60 60)" />
                        <circle cx="28" cy="36" r="8" fill="white" />
                        <circle cx="28" cy="36" r="8" fill="url(#gold-gradient)" opacity="0.3" />
                    </svg>
                </div>

                <div class="text-center space-y-2 relative z-10">
                    <h3 class="font-display text-2xl sm:text-[28px] font-semibold text-white tracking-normal font-sans">
                        Brand Recognition <span class="text-[#a78bfa] font-light">Performance</span>
                    </h3>
                </div>

                <!-- Dynamically Bind and Render Contiguous Bar Chart Columns with Interactive Rise Animation -->
                <div class="max-w-5xl mx-auto pt-8 relative z-10 w-full px-4">
                    <div
                        class="w-full bg-[#1b1c2b]/95 border border-white/5 rounded-2xl p-0 h-[380px] overflow-x-auto scrollbar-thin shadow-2xl pt-16">
                        <div
                            class="min-w-[500px] sm:min-w-[550px] lg:min-w-0 h-full flex items-end justify-between gap-0">

                            <template x-for="(brand, idx) in brandMetrics" :key="brand.name">
                                <div class="flex-1 h-full flex flex-col justify-end relative w-full transition-all duration-350"
                                    :style="brand.percentage > 0 ? 'opacity: 1' : 'opacity: 0.15'">

                                    <div class="absolute w-full flex flex-col items-center text-center z-20 transition-all duration-500 ease-out"
                                        :style="{ bottom: 'calc(' + brand.percentage + '% + 10px)' }">
                                        <span class="block text-[11px] font-bold text-[#00f5ff] mb-1"
                                            x-text="brand.percentage + '%'"></span>
                                        <span
                                            class="block text-[14px] font-medium text-slate-300 truncate max-w-[80px] mb-2"
                                            x-text="brand.name"></span>
                                    </div>

                                    <div class="w-full flex flex-col justify-end relative h-full z-0">
                                        <div class="w-full bg-gradient-to-t from-purple-600 to-indigo-500 rounded-t-lg transition-all duration-500 ease-out"
                                            :style="{ height: (brand.percentage) + '%' }">
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>

            </section>
        </div>

        <div class="h-[1px] w-full bg-gradient-to-r from-transparent via-gray-800/60 to-transparent"></div>

        <!-- 5. DIRECTIVE PIE CHART & OPINION METRICS -->
        <div id="evaluation" class="scroll-mt-28">
            <section class="max-w-7xl mx-auto px-4 pt-6">
                <div class="w-full relative min-h-[460px] flex flex-col pt-[53px]">

                    <div
                        class="absolute top-0 left-0 h-[52px] w-[180px] sm:w-[250px] bg-[#0c0e1a]/95 border-t border-l border-white/10 rounded-tl-2xl flex items-center px-4 sm:px-8 z-10">
                        <h4 class="font-sans text-[11px] sm:text-sm font-semibold text-white tracking-wider">
                            Distribution Sentiment
                        </h4>
                        <div class="absolute right-[-30px] sm:right-[-40px] top-0 bottom-0 w-[30px] sm:w-[40px] bg-[#0c0e1a]/95"
                            style="clip-path: polygon(0 0, 0 100%, 100% 100%);"></div>
                    </div>

                    <div
                        class="w-full flex-1 bg-gradient-to-br from-[#0c0e1a]/90 via-[#0e1124]/95 to-[#0b0c16]/98 border border-white/5 border-t-0 rounded-tr-3xl rounded-b-3xl p-6 sm:p-12 shadow-[0_12px_44px_rgba(0,0,0,0.5)] relative overflow-hidden">

                        <div
                            class="absolute right-0 top-0 bottom-0 w-1/3 opacity-30 pointer-events-none overflow-hidden hidden lg:block">
                            <svg class="w-full h-full text-indigo-500/10" fill="none" viewBox="0 0 200 400"
                                stroke="currentColor">
                                <path d="M 120 50 L 150 67 L 150 102 L 120 119" />
                            </svg>
                        </div>

                        <div class="absolute left-[210px] sm:left-[290px] top-0 right-0 h-[1.3px] bg-white/10"></div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center relative z-10 h-full">

                            <div class="lg:col-span-5 flex items-center justify-center pt-4 lg:pt-0">
                                <div class="relative w-[280px] h-[280px] flex items-center justify-center rounded-full border border-white/10 shadow-2xl transition-transform duration-500 hover:scale-105"
                                    x-init="
            const updateAngles = (vals) => {
                const neu = parseFloat(vals.neu) || 0;
                const neg = parseFloat(vals.neg) || 0;
                const pos = parseFloat(vals.pos) || 0;
                const total = neu + neg + pos;
                if(total === 0) return;

                // Menghitung sudut tengah murni (0 - 360 derajat)
                midNeu = ((neu / 2) / total) * 360;
                midNeg = ((neu + (neg / 2)) / total) * 360;
                midPos = ((neu + neg + (pos / 2)) / total) * 360;
            };
            updateAngles(sentimentPie);
            $watch('sentimentPie', value => updateAngles(value));
         " x-data="{ midNeu: 36, midNeg: 108, midPos: 252 }"
                                    :style="'background: conic-gradient(#ab91ad 0% ' + sentimentPie.neu + '%, #b197fc ' + sentimentPie.neu + '% ' + (parseFloat(sentimentPie.neu) + parseFloat(sentimentPie.neg)) + '%, #dcc4ff ' + (parseFloat(sentimentPie.neu) + parseFloat(sentimentPie.neg)) + '% 100%)'">

                                    <div
                                        class="absolute inset-0 flex items-center justify-center pointer-events-none text-[12px] font-sans font-bold text-[#1a153b]">

                                        <div class="absolute text-center whitespace-nowrap transition-all duration-300"
                                            x-show="parseFloat(sentimentPie.neu) > 0"
                                            :style="'transform: rotate(' + (midNeu - 90) + 'deg) translate(70px) rotate(' + (90 - midNeu) + 'deg);'">
                                            Neutral - <span x-text="sentimentPie.neu + '%'"></span>
                                        </div>

                                        <div class="absolute text-center whitespace-nowrap transition-all duration-300"
                                            x-show="parseFloat(sentimentPie.neg) > 0"
                                            :style="'transform: rotate(' + (midNeg - 90) + 'deg) translate(70px) rotate(' + (90 - midNeg) + 'deg);'">
                                            Negative - <span x-text="sentimentPie.neg + '%'"></span>
                                        </div>

                                        <div class="absolute text-center whitespace-nowrap transition-all duration-300"
                                            x-show="parseFloat(sentimentPie.pos) > 0"
                                            :style="'transform: rotate(' + (midPos - 90) + 'deg) translate(75px) rotate(' + (90 - midPos) + 'deg);'">
                                            Positive - <span x-text="sentimentPie.pos + '%'"></span>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-7 flex flex-col justify-center space-y-8 transition-all duration-500 ease-out"
                                :class="animateIn ? 'opacity-100 translate-x-0' : 'opacity-40 translate-x-2'">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                                    <div
                                        class="px-5 py-4.5 rounded-[18px] bg-[#16172a]/70 border border-white/15 hover:border-purple-400/35 hover:bg-[#1a1c32]/85 transition-all duration-300 flex items-center justify-between min-h-[92px] shadow-[0_12px_36px_rgba(0,0,0,0.55)] backdrop-blur-md">
                                        <div class="space-y-1">
                                            <p
                                                class="text-[18px] font-sans font-extrabold text-white tracking-wide leading-none">
                                                Positif</p>
                                            <span
                                                class="text-[16px] font-sans font-bold text-slate-100 tracking-normal block leading-none pt-2"
                                                x-text="sentimentMetrics.pos">0</span>
                                        </div>
                                        <div class="text-white opacity-95 pr-1">
                                            <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div
                                        class="px-5 py-4.5 rounded-[18px] bg-[#16172a]/70 border border-white/15 hover:border-purple-400/35 hover:bg-[#1a1c32]/85 transition-all duration-300 flex items-center justify-between min-h-[92px] shadow-[0_12px_36px_rgba(0,0,0,0.55)] backdrop-blur-md">
                                        <div class="space-y-1">
                                            <p
                                                class="text-[18px] font-sans font-extrabold text-white tracking-wide leading-none">
                                                Neutral</p>
                                            <span
                                                class="text-[16px] font-sans font-bold text-slate-100 tracking-normal block leading-none pt-2"
                                                x-text="sentimentMetrics.neu">0</span>
                                        </div>
                                        <div class="text-white opacity-95 pr-1">
                                            <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div
                                        class="px-5 py-4.5 rounded-[18px] bg-[#16172a]/70 border border-white/15 hover:border-purple-400/35 hover:bg-[#1a1c32]/85 transition-all duration-300 flex items-center justify-between min-h-[92px] shadow-[0_12px_36px_rgba(0,0,0,0.55)] backdrop-blur-md">
                                        <div class="space-y-1">
                                            <p
                                                class="text-[18px] font-sans font-extrabold text-white tracking-wide leading-none">
                                                Negative</p>
                                            <span
                                                class="text-[16px] font-sans font-bold text-slate-100 tracking-normal block leading-none pt-2"
                                                x-text="sentimentMetrics.neg">0</span>
                                        </div>
                                        <div class="text-white opacity-95 pr-1">
                                            <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-[#8e99b2] text-[13px] leading-relaxed font-light font-sans max-w-2xl">
                                    Melalui visualisasi ini, Anda dapat memantau apakah produk yang sedang viral
                                    mendapatkan respons Positif yang membangun loyalitas, respons Netral berupa
                                    pertanyaan seputar harga/stok, atau justru indikasi risiko dari ulasan Negatif
                                    konsumen.
                                </p>
                            </div>

                        </div>

                    </div>
                </div>
            </section>
        </div>

    </main>

    <!-- Footer container matching layout design -->
    <footer
        class="w-full bg-[#04050d] border-t border-white/5 py-8 mt-24 text-center text-xs text-gray-500 font-sans tracking-wide">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="font-mono tracking-wider">&copy; 2026 TRUSTTOK AI. HAK CIPTA DILINDUNGI UNDANG-UNDANG.</span>
            <div class="flex gap-4 font-normal text-[11px] text-gray-400">
                <span class="hover:text-white cursor-pointer transition-colors uppercase">TERMS</span>
                <span class="hover:text-white cursor-pointer transition-colors uppercase">PRIVACY</span>
                <span class="hover:text-white cursor-pointer transition-colors uppercase font-mono">SECURITY
                    CONTROLS</span>
            </div>
        </div>
    </footer>

    <!-- Alpine.js Application logic loaded from resources/js/app.js -->

</body>

</html>
