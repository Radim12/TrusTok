document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        activeTab: 'home',
        isMobileMenuOpen: false,
        isDropdownOpen: false,
        activeProduct: '',

        // Variabel Utama Penampung Data
        productList: ['Toner', 'Moisturizer', 'Serum', 'Sunscreen', 'Face Wash', 'Cushion', 'Cleanser', 'Retinol', 'Bedak'],
        scrapedComments: [],
        brandMetrics: [],
        totalComments: '0',
        totalVideos: '0',
        animateIn: false,

        // Tambahkan state default untuk menampung data sentimen agar tidak error saat load awal
        sentimentPie: { neg: 0, neu: 0, pos: 0 },
        sentimentMetrics: { neg: '0', neu: '0', pos: '0' },

        // Fungsi Scroll Navigasi
        scrollToSection(id) {
            this.activeTab = id;
            this.isMobileMenuOpen = false;
            document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
        },

        // Fungsi AJAX Menembak FastAPI
        async startScraping(productName) {
            this.isLoading = true;
            this.brandMetrics = [];
            this.scrapedComments = [];
            try {
                console.log("Mengirim request kata kunci produk ke FastAPI:", productName);

                const response = await fetch('http://127.0.0.1:8000/get-metrics', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ product_name: productName })
                });

                if (!response.ok) {
                    throw new Error(`Koneksi Gagal HTTP: ${response.status}`);
                }

                const data = await response.json();
                console.log("Data diterima dari Backend:", data.brands);
                console.log("Respon balik FastAPI sukses:", data);

                // Mutasi State Global Alpine.js secara Real-Time
                this.scrapedComments = data.comments;
                this.brandMetrics = data.brands;
                this.totalComments = data.total_comments;
                this.totalVideos = data.total_video_riil || data.total_videos;

                // FIX UTAMA: Petakan respon dari FastAPI ke variabel state Alpine.js Anda
                this.sentimentPie = data.sentiment_pie;
                this.sentimentMetrics = data.sentiment_metrics;

                // Aktifkan animasi naik grafik
                this.animateIn = false;
                setTimeout(() => { this.animateIn = true; }, 100);

            } catch (error) {
                console.error("Terjadi error integrasi:", error);
                alert("Gagal terhubung ke FastAPI backend!");
            } finally {
                this.isLoading = false; // <--- MATIKAN LOADING (BUKA KUNCI)
            }
        }
    }));
});