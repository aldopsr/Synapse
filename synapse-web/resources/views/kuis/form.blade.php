@extends('layouts.app')

@section('title', 'Form Kuis - Synapse')
@section('header_title', 'Buat / Edit Kuis')

@section('content')
<style>
    .top-navigation { margin-bottom: 20px; }
    .btn-kembali {
        background: #f5f7fa; color: #333; padding: 10px 18px;
        border-radius: 8px; text-decoration: none; font-weight: 600;
        font-size: 14px; border: 1px solid #ddd;
    }
    .btn-kembali:hover { background: #e9ecef; }

    .form-card {
        background: white; padding: 30px; border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 800px;
    }
    .form-row { display: flex; gap: 20px; flex-wrap: wrap; }
    .form-group { margin-bottom: 18px; flex: 1; min-width: 200px; }
    .form-group label {
        display: block; margin-bottom: 7px; font-weight: 600;
        font-size: 13px; color: #444;
    }
    .form-group small { color: #888; font-size: 11px; }
    .form-control {
        width: 100%; padding: 11px 13px; border: 1px solid #ccc;
        border-radius: 8px; box-sizing: border-box;
        font-family: 'Inter', sans-serif; font-size: 14px; transition: 0.2s;
    }
    .form-control:focus {
        border-color: #279685; outline: none;
        box-shadow: 0 0 0 3px rgba(39,150,133,0.1);
    }
    .form-control:disabled {
        background: #f8f9fa; color: #555; cursor: not-allowed;
    }
    textarea.form-control { resize: vertical; min-height: 70px; }

    .locked-info {
        margin-top: 6px; padding: 8px 12px; background: #FFF9F0;
        border-left: 3px solid #FFB74D; border-radius: 4px;
        font-size: 12px; color: #6D4C00;
    }

    .toggle-card {
        background: #F0FDFB; padding: 15px 18px; border-radius: 10px;
        border: 1px solid #c4e8e2; display: flex;
        justify-content: space-between; align-items: center; margin-bottom: 18px;
    }
    .toggle-card .info { display: flex; align-items: center; gap: 10px; }
    .toggle-card .info-text strong { font-size: 14px; color: #279685; }
    .toggle-card .info-text small { display:block; color: #555; margin-top: 2px; }

    .toggle-switch { position: relative; display: inline-block; width: 50px; height: 28px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc; border-radius: 28px; transition: 0.3s;
    }
    .toggle-slider:before {
        position: absolute; content: "";
        height: 22px; width: 22px;
        left: 3px; bottom: 3px;
        background-color: white; border-radius: 50%; transition: 0.3s;
    }
    input:checked + .toggle-slider { background-color: #279685; }
    input:checked + .toggle-slider:before { transform: translateX(22px); }

    .schedule-section {
        background: #FFF9F0; padding: 18px; border-radius: 10px;
        border: 1px solid #FFE4B5; margin-bottom: 18px;
    }
    .schedule-section h4 {
        margin: 0 0 15px 0; color: #B7791F; font-size: 14px;
    }
    .schedule-section .hint { font-size: 11px; color: #888; margin-top: 5px; }

    .btn-actions {
        display: flex; justify-content: flex-end; gap: 12px;
        margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee;
    }
    .btn-batal {
        background: #f1f1f1; color: #333; text-decoration: none;
        padding: 12px 24px; border-radius: 8px;
        font-weight: 600; font-size: 14px;
    }
    .btn-simpan {
        background: linear-gradient(135deg, #279685, #1f7a6c);
        color: white; border: none; padding: 12px 28px;
        border-radius: 8px; cursor: pointer;
        font-weight: bold; font-size: 14px; transition: 0.2s;
    }
    .btn-simpan:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(39,150,133,0.3); }
    .btn-simpan:disabled { background: #aaa; cursor: not-allowed; transform: none; box-shadow: none; }

    .loader { display: none; padding: 40px; text-align: center; color: #888; }

    .debug-box {
        background: #1e1e1e; color: #4ec9b0; padding: 12px;
        border-radius: 6px; font-family: 'Courier New', monospace;
        font-size: 11px; max-height: 150px; overflow-y: auto;
        white-space: pre-wrap; word-break: break-all; margin-top: 10px;
    }
    .debug-toggle {
        font-size: 11px; color: #888; cursor: pointer;
        text-decoration: underline; margin-top: 5px; display: inline-block;
    }
</style>

<div class="top-navigation">
    <a href="/kuis" class="btn-kembali">⬅ Kembali ke Daftar Kuis</a>
</div>

<div id="loaderEdit" class="loader">⏳ Memuat data kuis...</div>

<div class="form-card" id="formCard">
    <h2 id="formTitle" style="margin-top:0; color:#333;">📝 Buat Kuis Baru</h2>
    <p style="color:#777; margin-top:0;">Isi detail di bawah ini, lalu lanjutkan ke pengelolaan soal.</p>

    <form id="formKuis">
        <div class="form-group">
            <label>Judul Kuis *</label>
            <input type="text" id="title" class="form-control" placeholder="Contoh: UTS Jaringan Komputer" required maxlength="255">
        </div>

        <div class="form-group">
            <label>Deskripsi</label>
            <textarea id="description" class="form-control" placeholder="Penjelasan singkat tentang kuis ini..."></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Mata Kuliah *</label>
                <select id="course_id" class="form-control" required>
                    <option value="">-- Memuat... --</option>
                </select>
                <div id="lockedInfo" class="locked-info" style="display:none;">
                    🔒 Sebagai dosen, Kapten otomatis terikat ke matkul yang diampu.
                </div>
                <span class="debug-toggle" onclick="document.getElementById('debugBox').style.display='block'">🐛 Tampilkan debug log</span>
                <div class="debug-box" id="debugBox" style="display:none;">Menunggu...</div>
            </div>
            <div class="form-group">
                <label>Durasi (menit) *</label>
                <input type="number" id="duration_minutes" class="form-control" placeholder="60" required min="1" max="300" value="60">
                <small>1 - 300 menit</small>
            </div>
            <div class="form-group">
                <label>Nilai Kelulusan</label>
                <input type="number" id="passing_score" class="form-control" placeholder="70" min="0" max="100" value="70">
                <small>0 - 100</small>
            </div>
        </div>

        <div class="toggle-card">
            <div class="info">
                <span style="font-size:24px;">⚡</span>
                <div class="info-text">
                    <strong>Status Aktif</strong>
                    <small>Kalau dimatikan, mahasiswa tidak bisa akses kuis ini meskipun sudah masuk jadwal.</small>
                </div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="is_active" checked>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="schedule-section">
            <h4>📅 Jadwal Kuis (Opsional)</h4>
            <p style="font-size:12px; color:#666; margin-top:0;">
                Kosongkan kalau Kapten ingin kuis langsung tersedia tanpa batas waktu.
            </p>
            <div class="form-row">
                <div class="form-group">
                    <label>Tanggal & Jam Mulai</label>
                    <input type="datetime-local" id="start_at" class="form-control">
                    <div class="hint">Mahasiswa bisa mulai mengerjakan dari waktu ini</div>
                </div>
                <div class="form-group">
                    <label>Tanggal & Jam Selesai</label>
                    <input type="datetime-local" id="end_at" class="form-control">
                    <div class="hint">Setelah waktu ini, kuis otomatis ditutup</div>
                </div>
            </div>
        </div>

        <div class="btn-actions">
            <a href="/kuis" class="btn-batal">Batal</a>
            <button type="submit" class="btn-simpan" id="btnSimpan">💾 Simpan Kuis</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const token = window.token || localStorage.getItem('token');
    if (!token) { window.location.href = '/'; return; }

    const API_BASE = 'http://127.0.0.1:8000/api';
    const mode = '{{ $mode ?? "create" }}';
    const quizId = '{{ $quiz_id ?? "" }}';

    const debugBox = document.getElementById('debugBox');
    function log(msg, type = 'info') {
        const colors = { info: '#4ec9b0', warn: '#dcdcaa', error: '#f48771', success: '#b5cea8' };
        const color = colors[type] || colors.info;
        const time = new Date().toLocaleTimeString();
        debugBox.innerHTML += `<span style="color:${color}">[${time}] ${msg}</span>\n`;
        debugBox.scrollTop = debugBox.scrollHeight;
        console.log(`[${type}] ${msg}`);
    }

    if (mode === 'edit') {
        document.getElementById('formTitle').innerText = '✏ Edit Kuis';
        document.getElementById('btnSimpan').innerText = '💾 Update Kuis';
        document.getElementById('formCard').style.display = 'none';
        document.getElementById('loaderEdit').style.display = 'block';
    }

    init();

    async function init() {
        await fetchCourses();
        if (mode === 'edit' && quizId) {
            await loadQuizData();
        }
    }

    // ========================================
    // 🌟 LOAD DROPDOWN MATA KULIAH (DOSEN VS ADMIN)
    // ========================================
    async function fetchCourses() {
        const select = document.getElementById('course_id');
        const userStr = localStorage.getItem('user');
        let user = null;
        try { user = userStr ? JSON.parse(userStr) : null; } catch(e) {}

        log(`👤 User: ${user ? user.role : 'unknown'}, course_id: ${user?.course_id || 'tidak ada'}`, 'info');

        // ===========================================
        // 🌟 SHORTCUT DOSEN: Pakai course_id langsung
        // ===========================================
        if (user && user.role === 'dosen' && user.course_id) {
            log('🔒 Mode dosen: pakai course_id langsung', 'success');

            // Coba ambil nama matkul (biar tidak cuma ID)
            let courseName = 'Mata Kuliah Saya';
            try {
                const res = await fetch(`${API_BASE}/courses/${user.course_id}`, {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    const course = data.data || data;
                    courseName = course.title || course.name || course.nama || courseName;
                    log(`📚 Nama matkul: ${courseName}`, 'success');
                } else {
                    log(`⚠️ Tidak bisa ambil nama matkul (status ${res.status}), pakai default`, 'warn');
                }
            } catch (e) {
                log('⚠️ Error fetch nama matkul, pakai default', 'warn');
            }

            select.innerHTML = `<option value="${user.course_id}" selected>${escapeHtml(courseName)}</option>`;
            select.value = user.course_id;
            select.disabled = true;
            document.getElementById('lockedInfo').style.display = 'block';
            return;
        }

        // ===========================================
        // 🌟 ADMIN: Fetch list semua matkul
        // ===========================================
        let courses = [];
        let success = false;

        try {
            log('🔄 Mode admin: GET /api/courses...', 'info');
            const res = await fetch(`${API_BASE}/courses`, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            log(`📡 Status: ${res.status}`, res.ok ? 'success' : 'warn');

            if (res.ok) {
                const data = await res.json();
                courses = extractCoursesFromResponse(data);
                log(`✅ Berhasil load ${courses.length} matkul dari /courses`, 'success');
                success = courses.length > 0;
            }
        } catch (e) {
            log('❌ /courses gagal: ' + e.message, 'error');
        }

        // Fallback ke /public/courses
        if (!success) {
            try {
                log('🔄 Fallback ke /public/courses...', 'info');
                const res = await fetch(`${API_BASE}/public/courses`, {
                    headers: { 'Accept': 'application/json' }
                });
                log(`📡 Status: ${res.status}`, res.ok ? 'success' : 'warn');

                if (res.ok) {
                    const data = await res.json();
                    courses = extractCoursesFromResponse(data);
                    log(`✅ Public courses: ${courses.length} matkul`, 'success');
                }
            } catch (e) {
                log('❌ /public/courses gagal: ' + e.message, 'error');
            }
        }

        if (courses.length === 0) {
            select.innerHTML = '<option value="">⚠️ Tidak ada matkul tersedia</option>';
            log('❌ Tidak ada matkul yang bisa diambil!', 'error');
            debugBox.style.display = 'block';
            return;
        }

        select.innerHTML = '<option value="">-- Pilih Mata Kuliah --</option>';
        courses.forEach(c => {
            const id = c._id || c.id;
            const title = c.title || c.name || c.nama || 'Matkul';
            select.innerHTML += `<option value="${id}">${escapeHtml(title)}</option>`;
        });
        log(`📚 Dropdown terisi ${courses.length} matkul`, 'success');
    }

    // 🌟 Helper: Ekstrak array matkul dari berbagai format response
    function extractCoursesFromResponse(data) {
        if (Array.isArray(data)) return data;
        if (data.data && Array.isArray(data.data)) return data.data;
        if (data.courses && Array.isArray(data.courses)) return data.courses;
        if (data.success && data.data) return Array.isArray(data.data) ? data.data : [];
        log('⚠️ Format response tidak dikenali', 'warn');
        return [];
    }

    // ========================================
    // LOAD DATA QUIZ (mode edit)
    // ========================================
    async function loadQuizData() {
        try {
            const res = await fetch(`${API_BASE}/admin/quizzes/${quizId}`, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await res.json();
            const quiz = data.data;

            if (!quiz) {
                alert('Quiz tidak ditemukan');
                window.location.href = '/kuis';
                return;
            }

            document.getElementById('title').value = quiz.title || '';
            document.getElementById('description').value = quiz.description || '';
            document.getElementById('course_id').value = quiz.course_id || '';
            document.getElementById('duration_minutes').value = quiz.duration_minutes || 60;
            document.getElementById('passing_score').value = quiz.passing_score || 70;
            document.getElementById('is_active').checked = quiz.is_active !== false;

            // 🌟 PAKAI start_at_iso & end_at_iso (lebih konsisten)
            document.getElementById('start_at').value = formatDateTimeLocal(quiz.start_at_iso || quiz.start_at);
            document.getElementById('end_at').value = formatDateTimeLocal(quiz.end_at_iso || quiz.end_at);

            document.getElementById('loaderEdit').style.display = 'none';
            document.getElementById('formCard').style.display = 'block';
        } catch (e) {
            console.error(e);
            alert('Gagal memuat data quiz: ' + e.message);
        }
    }

    function formatDateTimeLocal(isoStr) {
        if (!isoStr) return '';
        const d = new Date(isoStr);
        if (isNaN(d.getTime())) return '';
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // ========================================
    // SUBMIT FORM
    // ========================================
    document.getElementById('formKuis').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = document.getElementById('btnSimpan');
        const courseId = document.getElementById('course_id').value;

        if (!courseId) {
            alert('⚠️ Pilih mata kuliah terlebih dahulu!');
            return;
        }

        btn.disabled = true;
        btn.innerText = '⏳ Menyimpan...';

        const payload = {
            title: document.getElementById('title').value.trim(),
            description: document.getElementById('description').value.trim(),
            course_id: courseId,
            duration_minutes: parseInt(document.getElementById('duration_minutes').value, 10),
            passing_score: parseInt(document.getElementById('passing_score').value, 10) || 70,
            is_active: document.getElementById('is_active').checked,
            start_at: document.getElementById('start_at').value || null,
            end_at: document.getElementById('end_at').value || null,
        };

        if (payload.start_at && payload.end_at && payload.end_at < payload.start_at) {
            alert('⚠️ Jadwal selesai harus setelah jadwal mulai!');
            btn.disabled = false;
            btn.innerText = mode === 'edit' ? '💾 Update Kuis' : '💾 Simpan Kuis';
            return;
        }

        try {
            const url = mode === 'edit'
                ? `${API_BASE}/admin/quizzes/${quizId}`
                : `${API_BASE}/admin/quizzes`;
            const method = mode === 'edit' ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                const data = await res.json();
                if (mode === 'create') {
                    const newId = data.data._id || data.data.id;
                    alert('✅ Kuis berhasil dibuat! Yuk tambahkan soalnya.');
                    window.location.href = `/kuis/${newId}/soal`;
                } else {
                    alert('✅ Kuis berhasil diupdate!');
                    window.location.href = '/kuis';
                }
            } else {
                const err = await res.json();
                let msg = err.message || 'Gagal menyimpan';
                if (err.errors) {
                    msg += '\n\n' + Object.values(err.errors).flat().join('\n');
                }
                alert('❌ ' + msg);
            }
        } catch (e) {
            alert('⚠️ Error jaringan: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerText = mode === 'edit' ? '💾 Update Kuis' : '💾 Simpan Kuis';
        }
    });
})();
</script>
@endpush