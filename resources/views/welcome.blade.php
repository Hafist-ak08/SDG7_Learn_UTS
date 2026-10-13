<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SDG7 Learn - Platform Edukasi Energi Bersih</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; }
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4f46e5;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .data-card { transition: transform 0.2s, box-shadow 0.2s; }
        .data-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-indigo-600">SDG7Learn</h1>
                <div class="flex gap-2">
                    <button onclick="loadArticles()" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                        📚 Artikel
                    </button>
                    <button onclick="loadQuizzes()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        📝 Kuis
                    </button>
                    <button onclick="loadForum()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        💬 Forum
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Loading Indicator -->
        <div id="loading" class="hidden flex justify-center items-center py-12">
            <div class="loading-spinner"></div>
            <span class="ml-3 text-gray-600">Mengambil data dari backend...</span>
        </div>

        <!-- Success Message -->
        <div id="successMsg" class="hidden mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-700 font-medium">✅ Data berhasil diambil dari backend API</p>
            <p class="text-sm text-green-600 mt-1">Data ditampilkan dalam tampilan frontend (hanya di folder view)</p>
        </div>

        <!-- Articles Section -->
        <section id="articlesSection" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">📚 Daftar Artikel</h2>
            <div id="articlesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center py-8 text-gray-500 col-span-full">Klik "Artikel" untuk memuat data</div>
            </div>
        </section>

        <!-- Quizzes Section -->
        <section id="quizzesSection" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">📝 Daftar Kuis</h2>
            <div id="quizzesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center py-8 text-gray-500 col-span-full">Klik "Kuis" untuk memuat data</div>
            </div>
        </section>

        <!-- Forum Section -->
        <section id="forumSection" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">💬 Forum Diskusi</h2>
            <div id="forumContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center py-8 text-gray-500 col-span-full">Klik "Forum" untuk memuat data</div>
            </div>
        </section>

        <!-- Quiz Results Section -->
        <section id="resultsSection" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 Hasil Kuis</h2>
            <div id="resultsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center py-8 text-gray-500 col-span-full">Data hasil kuis akan muncul di sini</div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500">
            <p>SDG7Learn - Platform Edukasi Energi Bersih & Terjangkau</p>
        </div>
    </footer>

    <!-- JavaScript for API Fetching -->
    <script>
        // API Configuration (following api_contract.md)
        const API_BASE = "/api/v1";
        
        // Show loading state
        function showLoading() {
            document.getElementById("loading").classList.remove("hidden");
            document.getElementById("successMsg").classList.add("hidden");
        }
        
        // Hide loading state
        function hideLoading() {
            document.getElementById("loading").classList.add("hidden");
        }
        
        // Show success message
        function showSuccess() {
            document.getElementById("successMsg").classList.remove("hidden");
        }
        
        // Fetch data from API
        async function fetchFromAPI(endpoint) {
            const response = await fetch(`${API_BASE}${endpoint}`, {
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json"
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Verify API contract response format
            if (data.success !== true) {
                throw new Error(data.message || "API response error");
            }
            
            return data;
        }
        
        // Load Articles
        async function loadArticles() {
            try {
                showLoading();
                const apiResponse = await fetchFromAPI("/artikel");
                const articles = apiResponse.data || [];
                const meta = apiResponse.meta || {};
                
                // Render articles in the view (data only exists in view)
                const html = articles.map(article => `
                    <div class="data-card bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">${escapeHtml(article.judul || "Tiada judul")}</h3>
                        <p class="text-gray-600 text-sm mb-3">${escapeHtml(article.deskripsi || article.ringkasan || "Terdapat deskripsi")}</p>
                        <div class="flex justify-between items-center mt-4 pt-4 border-t">
                            <span class="text-xs text-gray-400">${article.tanggal || "N/A"}</span>
                            <span class="inline-block px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded">Artikel</span>
                        </div>
                    </div>
                `).join("");
                
                document.getElementById("articlesContainer").innerHTML = html || "<div class="text-center py-8 text-gray-500 col-span-full">Tidak ada artikel tersedia</div>";
                showSuccess();
                hideLoading();
                
                console.log("✅ Articles fetched:", articles.length, "items");
                
            } catch (error) {
                console.error("❌ Error loading articles:", error);
                document.getElementById("articlesContainer").innerHTML = `
                    <div class="text-center py-8 text-red-500 col-span-full">
                        ❌ Gagal mengambil artikel. ${error.message}
                    </div>`;
                hideLoading();
            }
        }
        
        // Load Quizzes
        async function loadQuizzes() {
            try {
                showLoading();
                const apiResponse = await fetchFromAPI("/kuis");
                const quizzes = apiResponse.data || [];
                
                const html = quizzes.map(quiz => `
                    <div class="data-card bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">${escapeHtml(quiz.judul || "Tiada judul")}</h3>
                        <p class="text-gray-600 text-sm mb-3">${escapeHtml(quiz.deskripsi || "Terdapat deskripsi")}</p>
                        <div class="flex justify-between items-center mt-4 pt-4 border-t">
                            <span class="text-xs text-gray-400">${quiz.jumlah_pertanyaan || 0} pertanyaan</span>
                            <span class="inline-block px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">Kuis</span>
                        </div>
                    </div>
                `).join("");
                
                document.getElementById("quizzesContainer").innerHTML = html || "<div class="text-center py-8 text-gray-500 col-span-full">Tidak ada kuis tersedia</div>";
                showSuccess();
                hideLoading();
                
                console.log("✅ Quizzes fetched:", quizzes.length, "items");
                
            } catch (error) {
                console.error("❌ Error loading quizzes:", error);
                document.getElementById("quizzesContainer").innerHTML = `
                    <div class="text-center py-8 text-red-500 col-span-full">
                        ❌ Gagal mengambil kuis. ${error.message}
                    </div>`;
                hideLoading();
            }
        }
        
        // Load Forum Posts
        async function loadForum() {
            try {
                showLoading();
                const apiResponse = await fetchFromAPI("/forum/postingan");
                const posts = apiResponse.data || [];
                
                const html = posts.map(post => `
                    <div class="data-card bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">${escapeHtml(post.judul || "Tiada judul")}</h3>
                        <p class="text-gray-600 text-sm mb-3">${escapeHtml(post.ringkasan || post.isi || "Terdapat ringkasan")}</p>
                        <div class="flex justify-between items-center mt-4 pt-4 border-t">
                            <span class="text-xs text-gray-400">${post.tanggal || "N/A"}</span>
                            <span class="inline-block px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">Postingan</span>
                        </div>
                    </div>
                `).join("");
                
                document.getElementById("forumContainer").innerHTML = html || "<div class="text-center py-8 text-gray-500 col-span-full">Tidak ada postingan tersedia</div>";
                showSuccess();
                hideLoading();
                
                console.log("✅ Forum posts fetched:", posts.length, "items");
                
            } catch (error) {
                console.error("❌ Error loading forum:", error);
                document.getElementById("forumContainer").innerHTML = `
                    <div class="text-center py-8 text-red-500 col-span-full">
                        ❌ Gagal mengambil forum. ${error.message}
                    </div>`;
                hideLoading();
            }
        }
        
        // Load Quiz Results (for a specific quiz)
        async function loadQuizResults(quizId) {
            try {
                showLoading();
                const apiResponse = await fetchFromAPI(`/kuis/${quizId}/hasil`);
                const results = apiResponse.data || [];
                
                const html = results.map(result => `
                    <div class="data-card bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Hasil Kuis #${result.kuis_id}</h3>
                        <div class="flex justify-between items-center mt-4 pt-4 border-t">
                            <div>
                                <p class="text-sm text-gray-600">Skor: <span class="font-semibold">${result.skor}</span> / ${result.skor_maks}</p>
                                <p class="text-xs text-gray-400">${result.persentase || Math.round((result.skor / result.skor_maks * 100))}% benar</p>
                            </div>
                            <span class="inline-block px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded">Hasil</span>
                        </div>
                    </div>
                `).join("");
                
                document.getElementById("resultsContainer").innerHTML = html || "<div class="text-center py-8 text-gray-500 col-span-full">Tidak ada hasil kuis tersedia</div>";
                showSuccess();
                hideLoading();
                
                console.log("✅ Quiz results fetched:", results.length, "items");
                
            } catch (error) {
                console.error("❌ Error loading quiz results:", error);
                document.getElementById("resultsContainer").innerHTML = `
                    <div class="text-center py-8 text-red-500 col-span-full">
                        ❌ Gagal mengambil hasil kuis. ${error.message}
                    </div>`;
                hideLoading();
            }
        }
        
        // Helper function to escape HTML (security)
        function escapeHtml(text) {
            if (!text) return "";
            const div = document.createElement("div");
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Auto-load data on page load (optional - can be removed if not needed)
        document.addEventListener("DOMContentLoaded", function() {
            console.log("SDG7Learn Frontend - API Contract Compliant");
            console.log("API Base URL:", API_BASE);
            console.log("Endpoints available:");
            console.log("  - GET /api/v1/artikel");
            console.log("  - GET /api/v1/kuis");
            console.log("  - GET /api/v1/forum/postingan");
            console.log("  - GET /api/v1/kuis/{id}/hasil");
        });
    </script>
</body>
</html>
