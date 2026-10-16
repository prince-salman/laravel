<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data NPC - Laravel 13</title>
    <!-- Tailwind CSS CDN untuk styling sederhana dan rapi -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <div class="max-w-5xl mx-auto px-4 py-8">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-lg shadow-sm mb-6 border border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">⚔️ Manajemen Data NPC</h1>
                <p class="text-sm text-gray-500 mt-1">Aplikasi CRUD Sederhana dengan RESTful API, Upload Gambar, Pencarian, & Pagination</p>
            </div>
            <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-md shadow-sm transition flex items-center justify-center gap-2">
                <span>➕</span> Tambah NPC
            </button>
        </div>

        <!-- Alert Notifikasi -->
        <div id="alertBox" class="hidden mb-4 p-4 rounded-md text-sm font-medium transition duration-300"></div>

        <!-- Bar Pencarian & Info Total -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6 border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="relative flex-1">
                <input type="text" id="searchInput" placeholder="🔍 Cari berdasarkan nama atau peran NPC..." 
                       class="w-full pl-3 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div id="totalBadge" class="text-xs text-gray-500 font-semibold uppercase tracking-wider bg-gray-50 px-3 py-2 rounded border border-gray-200 text-center">
                Memuat data...
            </div>
        </div>

        <!-- Tabel Data NPC -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold uppercase text-xs">
                            <th class="py-3 px-4">Foto</th>
                            <th class="py-3 px-4">Nama NPC</th>
                            <th class="py-3 px-4">Peran (Role)</th>
                            <th class="py-3 px-4">Deskripsi</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="npcTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400">Sedang mengambil data dari API...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bagian Pagination -->
            <div id="paginationContainer" class="p-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50">
                <span id="pageInfo" class="text-xs text-gray-500">Halaman 1</span>
                <div id="paginationButtons" class="flex items-center gap-1"></div>
            </div>
        </div>

    </div>

    <!-- Modal Form (Tambah / Edit NPC) -->
    <div id="npcModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full overflow-hidden border border-gray-200 animate-in fade-in zoom-in duration-150">
            
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 id="modalTitle" class="font-bold text-gray-800 text-base">Tambah NPC Baru</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <form id="npcForm" onsubmit="handleFormSubmit(event)" class="p-6 space-y-4">
                <input type="hidden" id="npcId">

                <!-- Input Nama -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nama NPC <span class="text-red-500">*</span></label>
                    <input type="text" id="nameInput" required placeholder="Contoh: Gerald si Pandai Besi" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Input Role -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Peran (Role) <span class="text-red-500">*</span></label>
                    <select id="roleInput" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Pilih Peran...</option>
                        <option value="Merchant">Merchant (Pedagang)</option>
                        <option value="Guard">Guard (Penjaga)</option>
                        <option value="Quest Giver">Quest Giver (Pemberi Misi)</option>
                        <option value="Boss">Boss (Musuh Utama)</option>
                        <option value="Villager">Villager (Warga Desa)</option>
                    </select>
                </div>

                <!-- Input Deskripsi -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Deskripsi</label>
                    <textarea id="descriptionInput" rows="3" placeholder="Deskripsi latar belakang atau dialog NPC..." 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Input Foto / Gambar -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Foto / Avatar NPC</label>
                    <input type="file" id="imageInput" accept="image/*" onchange="previewImage(event)" 
                           class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    
                    <!-- Preview Foto -->
                    <div id="imagePreviewContainer" class="mt-3 hidden items-center gap-3">
                        <img id="imagePreview" src="" alt="Preview" class="w-16 h-16 object-cover rounded-md border border-gray-300 shadow-sm">
                        <span class="text-xs text-gray-500">Pratinjau gambar yang dipilih</span>
                    </div>
                </div>

                <!-- Tombol Aksi Modal -->
                <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-md transition">Batal</button>
                    <button type="submit" id="saveButton" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition">Simpan</button>
                </div>
            </form>

        </div>
    </div>

    <!-- JavaScript Penghubung ke API Laravel -->
    <script>
        // State aplikasi
        let currentPage = 1;
        let currentSearch = '';
        let searchTimeout = null;

        // URL dasar endpoint API
        const API_URL = '/api/npcs';

        // 1. Memuat daftar NPC dari API (Search + Pagination)
        async function loadNpcs(page = 1, search = '') {
            currentPage = page;
            currentSearch = search;

            const tableBody = document.getElementById('npcTableBody');
            tableBody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-gray-400">Sedang memuat data...</td></tr>`;

            try {
                const response = await fetch(`${API_URL}?page=${page}&search=${encodeURIComponent(search)}`);
                const result = await response.json();

                renderTable(result.data);
                renderPagination(result);
                document.getElementById('totalBadge').innerText = `Total: ${result.total || 0} NPC`;
            } catch (error) {
                console.error('Error saat load data:', error);
                tableBody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-red-500">Gagal memuat data dari API. Pastikan server Laravel & database aktif.</td></tr>`;
            }
        }

        // 2. Render baris tabel HTML
        function renderTable(npcs) {
            const tableBody = document.getElementById('npcTableBody');

            if (!npcs || npcs.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">
                            Tidak ada data NPC yang ditemukan.
                        </td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = npcs.map(npc => {
                const imageUrl = npc.image_url 
                    ? npc.image_url 
                    : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(npc.name) + '&background=e2e8f0&color=475569';

                return `
                    <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                        <td class="py-3 px-4">
                            <img src="${imageUrl}" alt="${npc.name}" class="w-12 h-12 rounded-full object-cover border border-gray-200 shadow-xs">
                        </td>
                        <td class="py-3 px-4 font-semibold text-gray-800">
                            ${escapeHtml(npc.name)}
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2.5 py-0.5 rounded-full font-medium">
                                ${escapeHtml(npc.role)}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-500 text-xs max-w-xs truncate">
                            ${escapeHtml(npc.description || '-')}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openEditModal(${npc.id})" class="px-2.5 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-xs font-medium transition">
                                    Edit
                                </button>
                                <button onclick="deleteNpc(${npc.id})" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium transition">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // 3. Render tombol navigasi Pagination
        function renderPagination(meta) {
            const container = document.getElementById('paginationButtons');
            const pageInfo = document.getElementById('pageInfo');

            pageInfo.innerText = `Halaman ${meta.current_page} dari ${meta.last_page || 1} (Total ${meta.total} data)`;

            if (meta.last_page <= 1) {
                container.innerHTML = '';
                return;
            }

            let buttonsHtml = '';

            // Tombol Prev
            if (meta.current_page > 1) {
                buttonsHtml += `<button onclick="loadNpcs(${meta.current_page - 1}, '${currentSearch}')" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs hover:bg-gray-100">Sebelumnya</button>`;
            }

            // Nomor Halaman
            for (let i = 1; i <= meta.last_page; i++) {
                const isActive = i === meta.current_page;
                buttonsHtml += `
                    <button onclick="loadNpcs(${i}, '${currentSearch}')" 
                            class="px-3 py-1 rounded text-xs border ${isActive ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'}">
                        ${i}
                    </button>
                `;
            }

            // Tombol Next
            if (meta.current_page < meta.last_page) {
                buttonsHtml += `<button onclick="loadNpcs(${meta.current_page + 1}, '${currentSearch}')" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs hover:bg-gray-100">Selanjutnya</button>`;
            }

            container.innerHTML = buttonsHtml;
        }

        // 4. Modal Handler (Buka & Tutup)
        function openCreateModal() {
            document.getElementById('modalTitle').innerText = 'Tambah NPC Baru';
            document.getElementById('npcForm').reset();
            document.getElementById('npcId').value = '';
            document.getElementById('imagePreviewContainer').classList.add('hidden');
            document.getElementById('npcModal').classList.remove('hidden');
        }

        async function openEditModal(id) {
            document.getElementById('modalTitle').innerText = 'Edit Data NPC';
            document.getElementById('npcForm').reset();
            document.getElementById('npcId').value = id;

            try {
                const res = await fetch(`${API_URL}/${id}`);
                const json = await res.json();
                const npc = json.data;

                document.getElementById('nameInput').value = npc.name;
                document.getElementById('roleInput').value = npc.role;
                document.getElementById('descriptionInput').value = npc.description || '';

                if (npc.image_url) {
                    document.getElementById('imagePreview').src = npc.image_url;
                    document.getElementById('imagePreviewContainer').classList.remove('hidden');
                } else {
                    document.getElementById('imagePreviewContainer').classList.add('hidden');
                }

                document.getElementById('npcModal').classList.remove('hidden');
            } catch (err) {
                showAlert('Gagal mengambil data NPC untuk diedit.', 'error');
            }
        }

        function closeModal() {
            document.getElementById('npcModal').classList.add('hidden');
        }

        // 5. Pratinjau Gambar saat dipilih dari file dialog
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreviewContainer').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        // 6. Simpan / Update data NPC via API
        async function handleFormSubmit(event) {
            event.preventDefault();

            const id = document.getElementById('npcId').value;
            const isEdit = Boolean(id);

            const formData = new FormData();
            formData.append('name', document.getElementById('nameInput').value);
            formData.append('role', document.getElementById('roleInput').value);
            formData.append('description', document.getElementById('descriptionInput').value);

            const fileInput = document.getElementById('imageInput');
            if (fileInput.files.length > 0) {
                formData.append('image', fileInput.files[0]);
            }

            const url = isEdit ? `${API_URL}/${id}` : API_URL;

            try {
                document.getElementById('saveButton').innerText = 'Menyimpan...';
                document.getElementById('saveButton').disabled = true;

                const response = await fetch(url, {
                    method: 'POST', // Menggunakan POST untuk multipart upload (baik create maupun update)
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    closeModal();
                    showAlert(data.message || 'Data berhasil disimpan!', 'success');
                    loadNpcs(currentPage, currentSearch);
                } else {
                    showAlert(data.message || 'Terjadi kesalahan validasi data.', 'error');
                }
            } catch (err) {
                showAlert('Gagal terhubung ke API backend.', 'error');
            } finally {
                document.getElementById('saveButton').innerText = 'Simpan';
                document.getElementById('saveButton').disabled = false;
            }
        }

        // 7. Hapus NPC via API
        async function deleteNpc(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus data NPC ini?')) {
                return;
            }

            try {
                const response = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    showAlert(data.message || 'NPC berhasil dihapus!', 'success');
                    loadNpcs(currentPage, currentSearch);
                } else {
                    showAlert('Gagal menghapus NPC.', 'error');
                }
            } catch (err) {
                showAlert('Gagal terhubung ke API backend.', 'error');
            }
        }

        // 8. Tampilkan Alert Pesan Sederhana
        function showAlert(message, type = 'success') {
            const box = document.getElementById('alertBox');
            box.innerText = message;
            box.className = type === 'success' 
                ? 'mb-4 p-3 rounded-md text-sm font-medium bg-green-100 text-green-800 border border-green-200' 
                : 'mb-4 p-3 rounded-md text-sm font-medium bg-red-100 text-red-800 border border-red-200';
            box.classList.remove('hidden');

            setTimeout(() => {
                box.classList.add('hidden');
            }, 4000);
        }

        // Utility: Mencegah XSS sederhana
        function escapeHtml(text) {
            if (!text) return '';
            return text.toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Event Listener: Pencarian dengan Debounce (300ms)
        document.getElementById('searchInput').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadNpcs(1, e.target.value.trim());
            }, 300);
        });

        // Inisialisasi awal saat halaman dibuka
        document.addEventListener('DOMContentLoaded', () => {
            loadNpcs(1, '');
        });
    </script>
</body>
</html>
