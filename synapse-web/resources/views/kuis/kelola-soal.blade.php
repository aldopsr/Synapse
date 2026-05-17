@extends('layouts.app')

@section('title', 'Kelola Soal Kuis - Synapse')
@section('header_title', 'Kelola Soal Kuis')

@section('content')
<style>
    .top-navigation { margin-bottom: 20px; }
    .btn-kembali {
        background: #f5f7fa; color: #333; padding: 10px 18px;
        border-radius: 8px; text-decoration: none; font-weight: 600;
        font-size: 14px; border: 1px solid #ddd;
    }
    .btn-kembali:hover { background: #e9ecef; }

    .header-title { margin-bottom: 20px; font-size: 22px; color: #333; }
    .text-purple { color: #279685; }

    .layout-container { display: flex; gap: 25px; flex-wrap: wrap; align-items: flex-start; }

    .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; padding: 25px; box-sizing: border-box; }
    .col-kiri { flex: 1; min-width: 380px; max-width: 480px; }
    .col-kanan { flex: 2; min-width: 400px; }

    .card-title { font-size: 16px; font-weight: bold; color: #333; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #555; }
    .form-control { width: 100%; padding: 11px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-family: 'Inter', sans-serif; transition: 0.2s; font-size: 14px; }
    .form-control:focus { border-color: #279685; outline: none; box-shadow: 0 0 0 3px rgba(39,150,133,0.1); }

    .form-row { display: flex; gap: 12px; flex-wrap: wrap; }
    .form-row .form-group { flex: 1; min-width: 150px; }

    .btn-simpan {
        background: linear-gradient(135deg, #279685, #1f7a6c);
        color: white; border: none; padding: 12px 20px; border-radius: 8px;
        cursor: pointer; font-weight: bold; width: 100%; transition: 0.2s; font-size: 14px;
    }
    .btn-simpan:hover { transform: translateY(-1px); }
    .btn-simpan:disabled { background: #aaa; cursor: not-allowed; transform: none; }

    /* Quiz info banner */
    .quiz-info {
        background: linear-gradient(135deg, #E3FAF8, #F0FDFB);
        padding: 16px 20px; border-radius: 10px;
        margin-bottom: 20px; border-left: 4px solid #279685;
    }
    .quiz-info h3 { margin: 0 0 8px 0; color: #1f7a6c; font-size: 16px; }
    .quiz-info .meta { display: flex; gap: 20px; flex-wrap: wrap; font-size: 12px; color: #555; }

    /* 🌟 TYPE TABS */
    .type-tabs {
        display: flex; gap: 6px; margin-bottom: 18px;
        background: #f0f0f0; padding: 4px; border-radius: 10px;
    }
    .type-tab {
        flex: 1; padding: 9px 8px; text-align: center; cursor: pointer;
        border-radius: 7px; font-size: 12px; font-weight: 600;
        color: #666; transition: 0.2s; border: none; background: transparent;
    }
    .type-tab:hover { background: rgba(255,255,255,0.6); }
    .type-tab.active {
        background: white; color: #279685;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* 🌟 IMAGE UPLOADER */
    .image-uploader {
        border: 2px dashed #279685; border-radius: 10px; padding: 16px;
        text-align: center; background: #F0FDFB; cursor: pointer;
        transition: 0.2s; min-height: 120px; display: flex;
        align-items: center; justify-content: center; flex-direction: column;
        position: relative;
    }
    .image-uploader:hover { background: #E0F8F4; }
    .image-uploader.has-preview { padding: 0; border-style: solid; }
    .image-uploader input[type="file"] { display: none; }
    .image-preview { max-width: 100%; max-height: 200px; border-radius: 8px; display: block; }
    .image-remove-btn {
        position: absolute; top: 6px; right: 6px;
        background: rgba(220,53,69,0.9); color: white; border: none;
        padding: 5px 10px; border-radius: 6px; font-size: 11px;
        cursor: pointer; font-weight: 600;
    }

    /* 🌟 OPTION ROWS dengan checkbox/radio terintegrasi */
    .option-row {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 8px;
    }
    .option-row .check-input {
        width: 22px; height: 22px; cursor: pointer;
        accent-color: #279685;
    }
    .option-row .option-label {
        width: 22px; font-weight: bold; color: #279685;
        text-align: center;
    }
    .option-row .form-control { flex: 1; margin: 0; }

    .option-hint {
        background: #FFF9F0; padding: 8px 12px; border-radius: 6px;
        font-size: 11px; color: #6D4C00; margin-top: 6px;
        border-left: 3px solid #FFB74D;
    }

    /* Difficulty badges */
    .difficulty-pills { display: flex; gap: 8px; }
    .difficulty-pill {
        flex: 1; padding: 8px; text-align: center; cursor: pointer;
        border: 2px solid #ddd; border-radius: 8px; font-size: 12px;
        font-weight: 600; transition: 0.2s; background: white;
    }
    .difficulty-pill:hover { border-color: #279685; }
    .difficulty-pill.active.mudah { border-color: #28a745; background: #d4edda; color: #155724; }
    .difficulty-pill.active.sedang { border-color: #ffc107; background: #fff3cd; color: #856404; }
    .difficulty-pill.active.sulit { border-color: #dc3545; background: #f8d7da; color: #721c24; }

    /* Soal item display */
    .soal-item {
        border: 1px solid #eee; background-color: #FAFAFA;
        border-radius: 10px; padding: 18px; margin-bottom: 15px;
        position: relative; transition: 0.2s;
    }
    .soal-item:hover { border-color: #ddd; background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
    .soal-meta {
        display: flex; gap: 8px; flex-wrap: wrap;
        margin-bottom: 10px; align-items: center;
    }
    .soal-meta .badge {
        padding: 3px 10px; border-radius: 12px; font-size: 11px;
        font-weight: 600; display: inline-block;
    }
    .badge-type { background: #E3FAF8; color: #279685; }
    .badge-points { background: #FFF3E0; color: #B7791F; }
    .badge-difficulty-mudah { background: #d4edda; color: #155724; }
    .badge-difficulty-sedang { background: #fff3cd; color: #856404; }
    .badge-difficulty-sulit { background: #f8d7da; color: #721c24; }

    .soal-image { max-width: 100%; max-height: 180px; border-radius: 8px; margin: 10px 0; border: 1px solid #eee; }
    .soal-teks { font-weight: bold; color: #333; margin: 10px 0; font-size: 15px; padding-right: 60px; }
    .soal-explanation {
        background: #E8F5E9; padding: 10px 14px; border-radius: 8px;
        margin-top: 10px; border-left: 3px solid #4CAF50;
        font-size: 13px; color: #2E7D32;
    }
    .opsi-list { list-style-type: none; padding: 0; margin: 0; font-size: 14px; color: #666; }
    .opsi-list li {
        margin-bottom: 6px; padding: 6px 12px; border-radius: 6px;
        background: #fff; border: 1px solid #eee; display: flex;
        align-items: center; gap: 8px;
    }
    .opsi-benar { border-color: #279685 !important; background-color: #E3FAF8 !important; color: #1f7a6c; font-weight: bold; }
    .opsi-benar::before { content: '✓'; color: #279685; font-weight: bold; }

    .btn-hapus {
        position: absolute; top: 15px; right: 15px;
        background: none; border: none; color: #E74C3C;
        font-weight: bold; font-size: 13px; cursor: pointer;
    }
    .btn-hapus:hover { text-decoration: underline; }

    .empty-state { text-align: center; color: #888; font-style: italic; padding: 30px; }

    /* Hidden helper untuk show/hide section */
    .hidden { display: none !important; }
</style>

<div class="top-navigation">
    <a href="/kuis" class="btn-kembali">⬅ Kembali ke Daftar Kuis</a>
</div>

<h2 class="header-title">Kelola Soal: <span class="text-purple" id="judulKuis">Memuat...</span></h2>

<div class="quiz-info" id="quizInfoBanner" style="display:none;">
    <h3 id="bannerTitle">Memuat info kuis...</h3>
    <div class="meta" id="bannerMeta"></div>
</div>

<div class="layout-container">

    <!-- KIRI: FORM TAMBAH SOAL -->
    <div class="card col-kiri">
        <h3 class="card-title">➕ Tambah Soal Baru</h3>

        <!-- 🌟 TYPE TABS -->
        <div class="form-group">
            <label>Tipe Soal</label>
            <div class="type-tabs">
                <button type="button" class="type-tab active" data-type="multiple_choice">📝 Pilihan Ganda</button>
                <button type="button" class="type-tab" data-type="true_false">✓✗ True/False</button>
                <button type="button" class="type-tab" data-type="multiple_answer">☑ Multi Answer</button>
            </div>
        </div>

        <form id="formTambahSoal">
            <input type="hidden" id="question_type" value="multiple_choice">

            <div class="form-group">
                <label>Pertanyaan *</label>
                <textarea id="question" class="form-control" rows="3" placeholder="Tulis pertanyaan di sini..." required></textarea>
            </div>

            <!-- 🌟 IMAGE UPLOADER -->
            <div class="form-group">
                <label>📷 Gambar (Opsional)</label>
                <div class="image-uploader" id="imageUploader" onclick="document.getElementById('imageInput').click()">
                    <input type="file" id="imageInput" accept="image/jpeg,image/png,image/jpg,image/webp">
                    <div id="imagePlaceholder">
                        <div style="font-size:32px;">🖼️</div>
                        <div style="color:#279685; font-weight:600; margin-top:6px;">Klik untuk upload gambar</div>
                        <div style="color:#888; font-size:11px;">PNG, JPG, WEBP (Max 2MB)</div>
                    </div>
                    <img id="imagePreview" class="image-preview" style="display:none;" alt="preview">
                    <button type="button" id="imageRemoveBtn" class="image-remove-btn" style="display:none;" onclick="event.stopPropagation(); removeImage();">🗑️ Hapus</button>
                </div>
            </div>

            <!-- 🌟 OPTIONS - DINAMIS BERDASARKAN TIPE -->

            <!-- Multiple Choice (default) -->
            <div id="section_multiple_choice" class="options-section">
                <div class="form-group">
                    <label>Pilihan Jawaban *</label>
                    <div class="option-row">
                        <input type="radio" name="mc_correct" id="mc_correct_a" value="A" class="check-input" checked>
                        <span class="option-label">A</span>
                        <input type="text" id="option_a_mc" class="form-control" placeholder="Pilihan A">
                    </div>
                    <div class="option-row">
                        <input type="radio" name="mc_correct" id="mc_correct_b" value="B" class="check-input">
                        <span class="option-label">B</span>
                        <input type="text" id="option_b_mc" class="form-control" placeholder="Pilihan B">
                    </div>
                    <div class="option-row">
                        <input type="radio" name="mc_correct" id="mc_correct_c" value="C" class="check-input">
                        <span class="option-label">C</span>
                        <input type="text" id="option_c_mc" class="form-control" placeholder="Pilihan C">
                    </div>
                    <div class="option-row">
                        <input type="radio" name="mc_correct" id="mc_correct_d" value="D" class="check-input">
                        <span class="option-label">D</span>
                        <input type="text" id="option_d_mc" class="form-control" placeholder="Pilihan D">
                    </div>
                    <div class="option-hint">💡 Klik radio button untuk menandai jawaban yang benar</div>
                </div>
            </div>

            <!-- True/False -->
            <div id="section_true_false" class="options-section hidden">
                <div class="form-group">
                    <label>Pilih Jawaban yang Benar *</label>
                    <div class="option-row">
                        <input type="radio" name="tf_correct" id="tf_correct_a" value="A" class="check-input" checked>
                        <span class="option-label">A</span>
                        <input type="text" class="form-control" value="Benar / True" disabled>
                    </div>
                    <div class="option-row">
                        <input type="radio" name="tf_correct" id="tf_correct_b" value="B" class="check-input">
                        <span class="option-label">B</span>
                        <input type="text" class="form-control" value="Salah / False" disabled>
                    </div>
                    <div class="option-hint">💡 Pertanyaan T/F biasanya berupa pernyataan yang dinilai benar atau salah</div>
                </div>
            </div>

            <!-- Multiple Answer -->
            <div id="section_multiple_answer" class="options-section hidden">
                <div class="form-group">
                    <label>Pilihan Jawaban * (boleh pilih lebih dari 1)</label>
                    <div class="option-row">
                        <input type="checkbox" id="ma_correct_a" value="A" class="check-input">
                        <span class="option-label">A</span>
                        <input type="text" id="option_a_ma" class="form-control" placeholder="Pilihan A">
                    </div>
                    <div class="option-row">
                        <input type="checkbox" id="ma_correct_b" value="B" class="check-input">
                        <span class="option-label">B</span>
                        <input type="text" id="option_b_ma" class="form-control" placeholder="Pilihan B">
                    </div>
                    <div class="option-row">
                        <input type="checkbox" id="ma_correct_c" value="C" class="check-input">
                        <span class="option-label">C</span>
                        <input type="text" id="option_c_ma" class="form-control" placeholder="Pilihan C">
                    </div>
                    <div class="option-row">
                        <input type="checkbox" id="ma_correct_d" value="D" class="check-input">
                        <span class="option-label">D</span>
                        <input type="text" id="option_d_ma" class="form-control" placeholder="Pilihan D">
                    </div>
                    <div class="option-hint">💡 Centang semua jawaban yang benar (skoring: partial — proporsional)</div>
                </div>
            </div>

            <!-- 🌟 EXPLANATION -->
            <div class="form-group">
                <label>💡 Penjelasan Jawaban (Opsional)</label>
                <textarea id="explanation" class="form-control" rows="2" placeholder="Penjelasan ini akan tampil ke mahasiswa setelah submit..."></textarea>
            </div>

            <!-- 🌟 POINTS & DIFFICULTY -->
            <div class="form-row">
                <div class="form-group">
                    <label>Bobot Poin</label>
                    <input type="number" id="points" class="form-control" value="10" min="1" max="100">
                </div>
                <div class="form-group" style="flex: 2;">
                    <label>Tingkat Kesulitan</label>
                    <div class="difficulty-pills">
                        <button type="button" class="difficulty-pill mudah" data-difficulty="mudah">🟢 Mudah</button>
                        <button type="button" class="difficulty-pill sedang active" data-difficulty="sedang">🟡 Sedang</button>
                        <button type="button" class="difficulty-pill sulit" data-difficulty="sulit">🔴 Sulit</button>
                    </div>
                    <input type="hidden" id="difficulty" value="sedang">
                </div>
            </div>

            <button type="submit" class="btn-simpan" id="btnSimpanSoal">💾 Simpan Soal</button>
        </form>
    </div>

    <!-- KANAN: DAFTAR SOAL -->
    <div class="card col-kanan">
        <h3 class="card-title">📋 Daftar Soal <span id="soalCount" style="color:#888; font-weight:normal; font-size:14px;"></span></h3>
        <div id="containerSoal">
            <p class="empty-state">Loading...</p>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function() {
    const token = window.token || localStorage.getItem('token');
    if (!token) { window.location.href = '/'; return; }

    const API_BASE = 'http://127.0.0.1:8000/api';
    const quizId = '{{ $quiz_id ?? "" }}';

    if (!quizId) {
        alert('Quiz ID tidak valid');
        window.location.href = '/kuis';
        return;
    }

    let selectedImage = null; // File gambar yang dipilih

    init();

    async function init() {
        await Promise.all([loadQuizInfo(), fetchQuestions()]);
        setupTypeTabs();
        setupDifficultyPills();
        setupImageUploader();
    }

    // ========================================
    // LOAD INFO KUIS
    // ========================================
    async function loadQuizInfo() {
        try {
            const res = await fetch(`${API_BASE}/admin/quizzes/${quizId}`, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await res.json();
            const quiz = data.data;
            if (!quiz) return;

            document.getElementById('judulKuis').innerText = quiz.title || 'Kuis';
            document.getElementById('bannerTitle').innerText = quiz.title || 'Kuis';

            const statusLabel = {
                aktif: '🟢 Aktif', nonaktif: '🔴 Nonaktif',
                belum_mulai: '🟡 Belum Mulai', sudah_selesai: '⚪ Selesai'
            }[quiz.status] || quiz.status;

            const courseTitle = quiz.course ? quiz.course.title : '-';

            document.getElementById('bannerMeta').innerHTML = `
                <span>📚 <strong>${escapeHtml(courseTitle)}</strong></span>
                <span>⏱ ${quiz.duration_minutes || 0} menit</span>
                <span>🎯 KKM: ${quiz.passing_score || 70}</span>
                <span>${statusLabel}</span>
            `;
            document.getElementById('quizInfoBanner').style.display = 'block';
        } catch (e) {
            console.error(e);
        }
    }

    // ========================================
    // SETUP TYPE TABS
    // ========================================
    function setupTypeTabs() {
        document.querySelectorAll('.type-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.type-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const type = tab.dataset.type;
                document.getElementById('question_type').value = type;

                // Show/hide section
                document.querySelectorAll('.options-section').forEach(s => s.classList.add('hidden'));
                document.getElementById('section_' + type).classList.remove('hidden');
            });
        });
    }

    // ========================================
    // SETUP DIFFICULTY PILLS
    // ========================================
    function setupDifficultyPills() {
        document.querySelectorAll('.difficulty-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                document.querySelectorAll('.difficulty-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                document.getElementById('difficulty').value = pill.dataset.difficulty;
            });
        });
    }

    // ========================================
    // SETUP IMAGE UPLOADER
    // ========================================
    function setupImageUploader() {
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert('⚠️ Ukuran gambar maksimal 2MB!');
                this.value = '';
                return;
            }

            selectedImage = file;
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('imagePreview').src = ev.target.result;
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('imagePlaceholder').style.display = 'none';
                document.getElementById('imageRemoveBtn').style.display = 'block';
                document.getElementById('imageUploader').classList.add('has-preview');
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeImage = function() {
        selectedImage = null;
        document.getElementById('imageInput').value = '';
        document.getElementById('imagePreview').src = '';
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('imagePlaceholder').style.display = 'block';
        document.getElementById('imageRemoveBtn').style.display = 'none';
        document.getElementById('imageUploader').classList.remove('has-preview');
    };

    // ========================================
    // FETCH SOAL
    // ========================================
    async function fetchQuestions() {
        try {
            const res = await fetch(`${API_BASE}/admin/quizzes/${quizId}/questions`, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await res.json();
            renderSoal(data.data || []);
        } catch (e) {
            document.getElementById('containerSoal').innerHTML =
                '<p class="empty-state" style="color:red;">Gagal memuat soal.</p>';
        }
    }

    function renderSoal(questions) {
        const container = document.getElementById('containerSoal');
        document.getElementById('soalCount').innerText = `(${questions.length} soal)`;

        if (questions.length === 0) {
            container.innerHTML = '<p class="empty-state">Belum ada soal. Tambah soal di form sebelah kiri 👈</p>';
            return;
        }

        container.innerHTML = '';
        questions.forEach((q, idx) => {
            const id = q._id || q.id;
            const type = q.question_type || 'multiple_choice';
            const difficulty = q.difficulty || 'sedang';
            const points = q.points || 10;

            const typeLabel = {
                multiple_choice: '📝 Pilihan Ganda',
                true_false: '✓✗ True/False',
                multiple_answer: '☑ Multi Answer'
            }[type];

            // Render pilihan dengan highlight jawaban benar
            let optionsHtml = '<ul class="opsi-list">';

            if (type === 'multiple_answer') {
                const correctSet = (q.correct_answers || []).map(a => a.toUpperCase());
                ['A', 'B', 'C', 'D'].forEach(letter => {
                    const optKey = 'option_' + letter.toLowerCase();
                    const isCorrect = correctSet.includes(letter);
                    if (q[optKey]) {
                        optionsHtml += `<li class="${isCorrect ? 'opsi-benar' : ''}">${letter}. ${escapeHtml(q[optKey])}</li>`;
                    }
                });
            } else if (type === 'true_false') {
                const correct = (q.correct_answer || '').toUpperCase();
                optionsHtml += `<li class="${correct === 'A' ? 'opsi-benar' : ''}">A. Benar / True</li>`;
                optionsHtml += `<li class="${correct === 'B' ? 'opsi-benar' : ''}">B. Salah / False</li>`;
            } else {
                const correct = (q.correct_answer || '').toUpperCase();
                ['A', 'B', 'C', 'D'].forEach(letter => {
                    const optKey = 'option_' + letter.toLowerCase();
                    if (q[optKey]) {
                        optionsHtml += `<li class="${correct === letter ? 'opsi-benar' : ''}">${letter}. ${escapeHtml(q[optKey])}</li>`;
                    }
                });
            }
            optionsHtml += '</ul>';

            const div = document.createElement('div');
            div.className = 'soal-item';
            div.innerHTML = `
                <button class="btn-hapus" onclick="hapusSoal('${id}')">❌ Hapus</button>
                <div class="soal-meta">
                    <span class="badge badge-type">${typeLabel}</span>
                    <span class="badge badge-difficulty-${difficulty}">${getDifficultyLabel(difficulty)}</span>
                    <span class="badge badge-points">⭐ ${points} poin</span>
                </div>
                <p class="soal-teks">${idx + 1}. ${escapeHtml(q.question || '')}</p>
                ${q.image_url ? `<img src="${q.image_url}" class="soal-image" alt="soal-image">` : ''}
                ${optionsHtml}
                ${q.explanation ? `<div class="soal-explanation"><strong>💡 Penjelasan:</strong> ${escapeHtml(q.explanation)}</div>` : ''}
            `;
            container.appendChild(div);
        });
    }

    function getDifficultyLabel(diff) {
        return { mudah: '🟢 Mudah', sedang: '🟡 Sedang', sulit: '🔴 Sulit' }[diff] || '🟡 Sedang';
    }

    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // ========================================
    // 🌟 SUBMIT SOAL (multipart untuk upload gambar)
    // ========================================
    document.getElementById('formTambahSoal').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanSoal');
        const type = document.getElementById('question_type').value;

        // Validasi & build payload
        const formData = new FormData();
        formData.append('question', document.getElementById('question').value.trim());
        formData.append('question_type', type);
        formData.append('points', document.getElementById('points').value || 10);
        formData.append('difficulty', document.getElementById('difficulty').value);
        formData.append('explanation', document.getElementById('explanation').value.trim());

        if (selectedImage) {
            formData.append('image', selectedImage);
        }

        // Validasi & ekstrak data per tipe
        if (type === 'multiple_choice') {
            const a = document.getElementById('option_a_mc').value.trim();
            const b = document.getElementById('option_b_mc').value.trim();
            const c = document.getElementById('option_c_mc').value.trim();
            const d = document.getElementById('option_d_mc').value.trim();

            if (!a || !b || !c || !d) {
                alert('⚠️ Semua 4 pilihan harus diisi untuk Pilihan Ganda!');
                return;
            }

            const correctRadio = document.querySelector('input[name="mc_correct"]:checked');
            if (!correctRadio) {
                alert('⚠️ Pilih kunci jawaban!');
                return;
            }

            formData.append('option_a', a);
            formData.append('option_b', b);
            formData.append('option_c', c);
            formData.append('option_d', d);
            formData.append('correct_answer', correctRadio.value);

        } else if (type === 'true_false') {
            const correctRadio = document.querySelector('input[name="tf_correct"]:checked');
            if (!correctRadio) {
                alert('⚠️ Pilih jawaban yang benar (Benar/Salah)!');
                return;
            }
            formData.append('correct_answer', correctRadio.value);

        } else if (type === 'multiple_answer') {
            const a = document.getElementById('option_a_ma').value.trim();
            const b = document.getElementById('option_b_ma').value.trim();
            const c = document.getElementById('option_c_ma').value.trim();
            const d = document.getElementById('option_d_ma').value.trim();

            if (!a || !b || !c || !d) {
                alert('⚠️ Semua 4 pilihan harus diisi untuk Multi Answer!');
                return;
            }

            const correctList = [];
            ['a', 'b', 'c', 'd'].forEach(letter => {
                if (document.getElementById('ma_correct_' + letter).checked) {
                    correctList.push(letter.toUpperCase());
                }
            });

            if (correctList.length === 0) {
                alert('⚠️ Pilih minimal 1 jawaban yang benar!');
                return;
            }

            formData.append('option_a', a);
            formData.append('option_b', b);
            formData.append('option_c', c);
            formData.append('option_d', d);
            formData.append('correct_answers', JSON.stringify(correctList));
        }

        btn.innerText = '⏳ Menyimpan...';
        btn.disabled = true;

        try {
            const res = await fetch(`${API_BASE}/admin/quizzes/${quizId}/questions`, {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                body: formData
            });

            if (res.ok) {
                resetForm();
                fetchQuestions();
            } else {
                const err = await res.json();
                let msg = err.message || 'Gagal';
                if (err.errors) msg += '\n' + Object.values(err.errors).flat().join('\n');
                alert('❌ ' + msg);
            }
        } catch (e) {
            alert('⚠️ Error: ' + e.message);
        } finally {
            btn.innerText = '💾 Simpan Soal';
            btn.disabled = false;
        }
    });

    function resetForm() {
        document.getElementById('formTambahSoal').reset();
        // Reset radio defaults
        document.getElementById('mc_correct_a').checked = true;
        document.getElementById('tf_correct_a').checked = true;
        // Reset image
        removeImage();
        // Reset difficulty ke sedang
        document.querySelectorAll('.difficulty-pill').forEach(p => p.classList.remove('active'));
        document.querySelector('.difficulty-pill.sedang').classList.add('active');
        document.getElementById('difficulty').value = 'sedang';
        document.getElementById('points').value = 10;
        // Tetap di tipe yang sama (jangan reset tab)
    }

    // ========================================
    // HAPUS SOAL
    // ========================================
    window.hapusSoal = async function(id) {
        if (!confirm('Yakin ingin menghapus soal ini?\nGambar terkait juga akan terhapus.')) return;

        try {
            const res = await fetch(`${API_BASE}/admin/quiz-questions/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            if (res.ok) fetchQuestions();
            else alert('❌ Gagal hapus soal');
        } catch (e) {
            alert('⚠️ Error: ' + e.message);
        }
    };
})();
</script>
@endpush