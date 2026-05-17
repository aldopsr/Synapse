@extends('layouts.app')

@section('title')
{{ $mode === 'edit' ? 'Edit Kuis' : 'Buat Kuis Baru' }} - Synapse
@endsection

@section('header_title')
{{ $mode === 'edit' ? 'Edit Kuis' : 'Buat Kuis Baru' }}
@endsection

@section('content')
<style>
/* =====================================================
   FORM KUIS — modernized
   ===================================================== */
.back-link {
    display: inline-flex; align-items: center; gap: 7px;
    color: #888; font-size: 13px; font-weight: 600;
    text-decoration: none; margin-bottom: 22px;
    transition: color .15s;
}
.back-link:hover { color: #279685; }
.back-link:hover svg { transform: translateX(-3px); }
.back-link svg { transition: transform .15s; }

/* Two-column layout */
.form-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px; align-items: start;
}
@media (max-width: 820px) { .form-layout { grid-template-columns: 1fr; } }

/* Cards */
.form-card {
    background: #fff; border-radius: 16px;
    border: 1px solid #eee; overflow: hidden;
    margin-bottom: 16px;
}
.form-card:last-child { margin-bottom: 0; }
.card-header {
    display: flex; align-items: center; gap: 10px;
    padding: 18px 22px 14px; border-bottom: 1px solid #f0f0f0;
}
.ch-icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.ch-icon.teal   { background: #e3faf8; }
.ch-icon.amber  { background: #fef3c7; }
.ch-icon.purple { background: #f0eeff; }
.card-header h3 { font-size: 14px; font-weight: 700; color: #1a1a1a; margin: 0 0 2px; }
.card-header p  { font-size: 11px; color: #aaa; margin: 0; }
.card-body { padding: 18px 22px; }

/* Form groups */
.fg { margin-bottom: 16px; }
.fg:last-child { margin-bottom: 0; }
.fg label {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 700; color: #555;
    text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px;
}
.req { color: #ef4444; font-size: 13px; }
.opt { font-size: 10px; color: #bbb; font-weight: 400; text-transform: none; letter-spacing: 0; }

.fc {
    width: 100%; padding: 10px 12px;
    border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13px; font-family: inherit; color: #1a1a1a;
    background: #fff; box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.fc:focus { outline: none; border-color: #279685; box-shadow: 0 0 0 3px rgba(39,150,133,.1); }
.fc::placeholder { color: #ccc; }
.fc:disabled { background: #f8f9fa; color: #888; cursor: not-allowed; }
.fc.err { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
textarea.fc { resize: vertical; min-height: 78px; }
select.fc { cursor: pointer; }

.field-hint { font-size: 11px; color: #bbb; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
.field-err  { font-size: 11px; color: #ef4444; margin-top: 4px; display: none; align-items: center; gap: 4px; }
.field-err.show { display: flex; }

.form-row { display: flex; gap: 12px; }
.form-row .fg { flex: 1; min-width: 0; }

/* Matkul status bar */
.matkul-status {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 12px; border-radius: 8px;
    font-size: 12px; font-weight: 600; margin-top: 6px;
}
.ms-loading { background: #f3f4f6; color: #888; border: 1px solid #e5e7eb; }
.ms-locked  { background: #e3faf8; color: #0f6e56; border: 1px solid #c0ede8; }
.ms-error   { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.ms-hidden  { display: none !important; }

/* Spinner */
.spin {
    width: 13px; height: 13px; border: 2px solid #ddd;
    border-top-color: #279685; border-radius: 50%;
    animation: spin .6s linear infinite; flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Toggle */
.toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; padding: 13px 14px;
    background: #f8fafa; border-radius: 10px;
    border: 1px solid #e5e7eb; cursor: pointer;
    transition: border-color .15s; margin-bottom: 16px;
}
.toggle-row:hover { border-color: #279685; }
.toggle-info strong { display: block; font-size: 13px; font-weight: 700; color: #1a1a1a; margin-bottom: 2px; }
.toggle-info small  { font-size: 11px; color: #aaa; }
.tw { position: relative; width: 42px; height: 23px; flex-shrink: 0; }
.tw input { opacity:0; width:0; height:0; position:absolute; }
.t-track { position:absolute; inset:0; background:#e5e7eb; border-radius:99px; transition:background .25s; }
.tw input:checked + .t-track { background:#279685; }
.t-thumb { position:absolute; top:2px; left:2px; width:19px; height:19px; background:#fff; border-radius:50%; box-shadow:0 1px 3px rgba(0,0,0,.2); pointer-events:none; transition:transform .25s; }
.tw input:checked ~ .t-thumb { transform:translateX(19px); }

/* Schedule section */
.schedule-box {
    background: #fffbeb; border: 1px solid #fde68a;
    border-radius: 12px; padding: 15px 18px;
}
.schedule-box .sh { display:flex; align-items:center; gap:7px; font-size:13px; font-weight:700; color:#92400e; margin-bottom:3px; }
.schedule-box .sd { font-size:11px; color:#b45309; margin-bottom:12px; }

/* Submit */
.submit-wrap { padding: 16px 22px; border-top: 1px solid #f0f0f0; }
.btn-submit {
    width: 100%; padding: 12px;
    background: #279685; color: #fff; border: none;
    border-radius: 10px; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: background .18s;
    font-family: inherit; display: flex;
    align-items: center; justify-content: center; gap: 8px;
}
.btn-submit:hover { background: #1f7a6c; }
.btn-submit:disabled { background: #9ca3af; cursor: not-allowed; }

/* Sidebar */
.checklist-card, .tips-card {
    background: #fff; border-radius: 16px; border: 1px solid #eee;
    margin-bottom: 14px; overflow: hidden;
}
.checklist-card:last-child, .tips-card:last-child { margin-bottom: 0; }
.check-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; color: #bbb; margin-bottom: 8px;
    transition: color .2s;
}
.check-item:last-child { margin-bottom: 0; }
.check-item.done { color: #279685; font-weight: 600; }
.check-dot {
    width: 17px; height: 17px; border-radius: 50%;
    border: 1.5px solid #ddd; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; transition: all .2s; color: transparent;
}
.check-item.done .check-dot {
    background: #279685; border-color: #279685; color: #fff;
}
.tip-item { display: flex; gap: 8px; margin-bottom: 10px; font-size: 12px; }
.tip-item:last-child { margin-bottom: 0; }
.tip-dot { width: 5px; height: 5px; border-radius: 50%; background:#279685; flex-shrink:0; margin-top:5px; }
.tip-item p { color:#666; margin:0; line-height:1.5; }
.tip-item strong { color:#1a1a1a; }

/* Toast */
.toast {
    position: fixed; bottom: 24px; right: 24px;
    padding: 11px 16px; border-radius: 10px;
    font-size: 13px; font-weight: 600; z-index: 9999;
    transform: translateY(80px); opacity: 0;
    transition: all .28s cubic-bezier(.34,1.56,.64,1);
    color: #fff; pointer-events: none;
    max-width: 300px; box-shadow: 0 8px 24px rgba(0,0,0,.18);
}
.toast.show { transform: translateY(0); opacity: 1; }
.toast.ok  { background: #279685; }
.toast.err { background: #ef4444; }

/* Loading overlay */
.overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(255,255,255,.75); z-index: 200;
    align-items: center; justify-content: center;
    flex-direction: column; gap: 12px;
    font-size: 13px; font-weight: 600; color: #555;
}
.overlay.show { display: flex; }
.overlay .spin { width: 26px; height: 26px; border-width: 3px; }
</style>

{{-- Loading overlay --}}
<div class="overlay" id="overlay">
    <div class="spin"></div>
    Memuat data kuis...
</div>

{{-- Back link --}}
<a href="/kuis" class="back-link">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="m15 18-6-6 6-6"/>
    </svg>
    Kembali ke Daftar Kuis
</a>

<div class="form-layout">

    {{-- ===== KOLOM KIRI ===== --}}
    <div>
        {{-- Card: Info dasar --}}
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon teal">📝</div>
                <div>
                    <h3 id="cardTitle">Buat Kuis Baru</h3>
                    <p>Informasi dasar tentang kuis ini</p>
                </div>
            </div>
            <div class="card-body">
                <div class="fg">
                    <label>Judul Kuis <span class="req">*</span></label>
                    <input type="text" id="f-title" class="fc"
                        placeholder="Contoh: UTS Pemrograman Web Semester Genap"
                        maxlength="255" oninput="liveCheck('f-title')">
                    <div class="field-err" id="err-title">Judul kuis wajib diisi.</div>
                </div>

                <div class="fg">
                    <label>Deskripsi <span class="opt">(opsional)</span></label>
                    <textarea id="f-desc" class="fc" rows="3"
                        placeholder="Jelaskan topik, materi yang diuji, atau petunjuk pengerjaan..."></textarea>
                </div>

                <div class="fg">
                    <label>Mata Kuliah <span class="req">*</span></label>
                    <select id="f-course" class="fc" oninput="liveCheck('f-course')">
                        <option value="">— Memuat... —</option>
                    </select>
                    <div class="matkul-status ms-loading" id="matkulStatus">
                        <div class="spin"></div> Memuat mata kuliah...
                    </div>
                    <div class="field-err" id="err-course">Mata kuliah wajib dipilih.</div>
                </div>

                <div class="form-row">
                    <div class="fg">
                        <label>Durasi <span class="req">*</span></label>
                        <input type="number" id="f-duration" class="fc"
                            value="60" min="1" max="300"
                            oninput="liveCheck('f-duration')">
                        <div class="field-hint">1 – 300 menit</div>
                        <div class="field-err" id="err-duration">Durasi harus antara 1–300 menit.</div>
                    </div>
                    <div class="fg">
                        <label>Nilai Kelulusan <span class="opt">(opsional)</span></label>
                        <input type="number" id="f-passing" class="fc"
                            value="70" min="0" max="100">
                        <div class="field-hint">0 – 100 poin</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Status & Jadwal --}}
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon amber">📅</div>
                <div>
                    <h3>Status &amp; Jadwal</h3>
                    <p>Kapan mahasiswa bisa mengakses kuis ini</p>
                </div>
            </div>
            <div class="card-body">
                <label class="toggle-row" for="f-active">
                    <div class="toggle-info">
                        <strong>Status Aktif</strong>
                        <small id="activeHint">Kuis aktif — mahasiswa bisa melihat dan mengerjakan.</small>
                    </div>
                    <label class="tw">
                        <input type="checkbox" id="f-active" checked onchange="refreshActiveHint()">
                        <div class="t-track"></div>
                        <div class="t-thumb"></div>
                    </label>
                </label>

                <div class="schedule-box">
                    <div class="sh">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd"/></svg>
                        Jadwal Kuis
                        <span style="font-weight:400;color:#b45309;font-size:11px;">(opsional)</span>
                    </div>
                    <div class="sd">Kosongkan untuk kuis tanpa batas waktu.</div>
                    <div class="form-row">
                        <div class="fg">
                            <label>Mulai</label>
                            <input type="datetime-local" id="f-start" class="fc" onchange="checkSchedule()">
                            <div class="field-hint">Mahasiswa bisa mulai dari sini</div>
                        </div>
                        <div class="fg">
                            <label>Selesai</label>
                            <input type="datetime-local" id="f-end" class="fc" onchange="checkSchedule()">
                            <div class="field-hint">Kuis otomatis ditutup setelahnya</div>
                        </div>
                    </div>
                    <div class="field-err" id="err-schedule" style="margin-top:6px;">
                        Jadwal selesai harus setelah jadwal mulai.
                    </div>
                </div>
            </div>
            <div class="submit-wrap">
                <button class="btn-submit" id="btnSubmit" type="button" onclick="submitForm()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 0 1 1.04-.208Z" clip-rule="evenodd"/></svg>
                    <span id="btnLabel">Simpan &amp; Kelola Soal</span>
                </button>
                <p style="font-size:11px;color:#bbb;text-align:center;margin:8px 0 0;">
                    Atau tekan <kbd style="background:#f3f4f6;padding:1px 5px;border-radius:4px;font-size:10px;border:1px solid #e5e7eb;">Ctrl+Enter</kbd>
                </p>
            </div>
        </div>
    </div>

    {{-- ===== KOLOM KANAN (sidebar) ===== --}}
    <div>
        {{-- Checklist --}}
        <div class="checklist-card">
            <div class="card-header">
                <div class="ch-icon purple">✅</div>
                <div>
                    <h3>Kelengkapan Form</h3>
                    <p>Field wajib sebelum bisa simpan</p>
                </div>
            </div>
            <div class="card-body">
                <div class="check-item" id="ck-title">
                    <div class="check-dot">✓</div><span>Judul kuis diisi</span>
                </div>
                <div class="check-item" id="ck-course">
                    <div class="check-dot">✓</div><span>Mata kuliah dipilih</span>
                </div>
                <div class="check-item" id="ck-duration">
                    <div class="check-dot">✓</div><span>Durasi valid (1–300 menit)</span>
                </div>
                <div class="check-item" id="ck-schedule">
                    <div class="check-dot">✓</div><span>Jadwal konsisten</span>
                </div>
            </div>
        </div>

        {{-- Tips --}}
        <div class="tips-card">
            <div class="card-header">
                <div class="ch-icon amber">💡</div>
                <div><h3>Tips</h3><p>Buat kuis yang efektif</p></div>
            </div>
            <div class="card-body">
                <div class="tip-item">
                    <div class="tip-dot"></div>
                    <p>Judul yang jelas membantu mahasiswa mengetahui topik sebelum mulai.</p>
                </div>
                <div class="tip-item">
                    <div class="tip-dot"></div>
                    <p><strong>Nonaktif</strong> = draft tersimpan, tidak muncul di app mahasiswa. Aktifkan saat siap.</p>
                </div>
                <div class="tip-item">
                    <div class="tip-dot"></div>
                    <p>Setelah simpan, kamu langsung diarahkan ke <strong>halaman soal</strong> untuk menambahkan pertanyaan.</p>
                </div>
                <div class="tip-item">
                    <div class="tip-dot"></div>
                    <p>Jadwal opsional — tanpa jadwal, kuis tersedia terus selama aktif.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="toast" id="toast"></div>

@endsection

@push('scripts')
<script>
(function () {
    const MODE  = '{{ $mode ?? "create" }}';
    const QUID  = '{{ $quiz_id ?? "" }}';
    const API   = window.apiBaseUrl;
    const token = window.token;
    const role  = window.role || 'dosen';
    const isAdmin = role === 'admin' || role === 'superadmin';

    const $ = id => document.getElementById(id);
    let matkulOk      = false;
    let scheduleOk    = true;

    /* ── Init UI mode ────────────────────────────────────────── */
    if (MODE === 'edit') {
        $('cardTitle').textContent = 'Edit Kuis';
        $('btnLabel').textContent  = 'Simpan Perubahan';
        $('overlay').classList.add('show');
    }
    updateChecklist();

    /* ── Toast ───────────────────────────────────────────────── */
    function toast(msg, type = 'ok') {
        const el = $('toast');
        el.textContent = (type === 'ok' ? '✓  ' : '✕  ') + msg;
        el.className   = 'toast ' + type + ' show';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3400);
    }

    /* ── Active toggle hint ──────────────────────────────────── */
    window.refreshActiveHint = function () {
        const on = $('f-active').checked;
        $('activeHint').textContent = on
            ? 'Kuis aktif — mahasiswa bisa melihat dan mengerjakan.'
            : 'Kuis nonaktif — tersembunyi dari mahasiswa.';
    };

    /* ── Checklist ───────────────────────────────────────────── */
    function updateChecklist() {
        setDone('ck-title',    $('f-title').value.trim().length > 0);
        setDone('ck-course',   !!$('f-course').value && matkulOk);
        const dur = parseInt($('f-duration').value, 10);
        setDone('ck-duration', !isNaN(dur) && dur >= 1 && dur <= 300);
        setDone('ck-schedule', scheduleOk);
    }
    function setDone(id, done) {
        const el = $(id); if (!el) return;
        el.classList.toggle('done', done);
    }

    /* ── Live field check ────────────────────────────────────── */
    window.liveCheck = function (id) {
        const el  = $(id);
        const err = $('err-' + id.replace('f-', ''));
        let ok = false;
        if (id === 'f-title')    ok = el.value.trim().length > 0;
        if (id === 'f-course')   ok = !!el.value;
        if (id === 'f-duration') { const n = parseInt(el.value,10); ok = !isNaN(n) && n>=1 && n<=300; }
        el.classList.toggle('err', !ok);
        if (err) err.classList.toggle('show', !ok);
        updateChecklist();
    };

    /* ── Schedule check ──────────────────────────────────────── */
    window.checkSchedule = function () {
        const s = $('f-start').value, e = $('f-end').value;
        const bad = s && e && e <= s;
        scheduleOk = !bad;
        $('f-end').classList.toggle('err', bad);
        $('err-schedule').classList.toggle('show', bad);
        updateChecklist();
    };

    /* ── Load mata kuliah ────────────────────────────────────── */
    /*
     * FIX ROOT CAUSE:
     * Kode lama punya 3 fallback (course_id langsung, GET /courses, GET /public/courses)
     * yang bisa saling race dan menghasilkan select kosong.
     *
     * Solusi bersih: SELALU pakai GET /courses.
     * - Backend sudah filter per role (dosen hanya dapat matkul yang dosen_id-nya match).
     * - Untuk dosen: kalau ada 1 hasil → auto-select + disable (read-only, informatif).
     * - Kalau 0 hasil → error actionable, bukan silent kosong.
     * - Mode edit: preselect dengan course_id dari data kuis.
     */
    async function loadMatkul(preId) {
        const sel = $('f-course');
        const st  = $('matkulStatus');

        sel.innerHTML = '<option value="">— Memuat... —</option>';
        sel.disabled  = true;
        st.className  = 'matkul-status ms-loading';
        st.innerHTML  = '<div class="spin"></div> Memuat mata kuliah...';

        try {
            const res  = await fetch(API + '/courses', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);

            const list = (await res.json()).data || [];

            if (list.length === 0) {
                sel.innerHTML = '<option value="">— Tidak ada mata kuliah —</option>';
                st.className  = 'matkul-status ms-error';
                st.innerHTML  = `⚠ ${isAdmin
                    ? 'Tambahkan mata kuliah terlebih dahulu di menu Mata Kuliah.'
                    : 'Kamu belum ditugaskan ke mata kuliah. Hubungi admin.'}`;
                matkulOk = false; updateChecklist(); return;
            }

            // Isi options
            sel.innerHTML = '<option value="">— Pilih mata kuliah —</option>';
            list.forEach(c => {
                const id  = c._id || c.id;
                const opt = document.createElement('option');
                opt.value = id; opt.textContent = c.title;
                if (preId && String(id) === String(preId)) opt.selected = true;
                sel.appendChild(opt);
            });

            if (list.length === 1 && !preId) {
                // Dosen 1 matkul → auto-select + lock (bukan blocking)
                sel.value    = list[0]._id || list[0].id;
                sel.disabled = true;
                st.className = 'matkul-status ms-locked';
                st.innerHTML = `🔒 Terikat ke: <strong style="margin-left:4px;">${list[0].title}</strong>`;
            } else if (preId && sel.value === String(preId)) {
                // Edit: preselect berhasil
                sel.disabled = false;
                st.className = 'matkul-status ms-hidden';
            } else {
                // Admin banyak pilihan
                sel.disabled = false;
                st.className = 'matkul-status ms-hidden';
            }

            matkulOk = true;
            liveCheck('f-course');

        } catch (e) {
            const pid = preId || '';
            sel.innerHTML = '<option value="">— Gagal memuat —</option>';
            st.className  = 'matkul-status ms-error';
            st.innerHTML  = `⚠ Gagal koneksi. <button type="button" onclick="retryMatkul('${pid}')"
                style="font-weight:700;color:#92400e;background:none;border:none;cursor:pointer;text-decoration:underline;padding:0;font-family:inherit;font-size:inherit;">
                Coba lagi</button>`;
            matkulOk = false; updateChecklist();
        }
    }

    window.retryMatkul = loadMatkul;

    /* ── Load quiz data (edit mode) ──────────────────────────── */
    async function loadQuizData() {
        try {
            const res  = await fetch(`${API}/admin/quizzes/${QUID}`, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (!res.ok) throw new Error('Kuis tidak ditemukan (HTTP ' + res.status + ')');
            const q = (await res.json()).data;
            if (!q) throw new Error('Data kuis kosong dari server');

            $('f-title').value    = q.title       || '';
            $('f-desc').value     = q.description || '';
            $('f-duration').value = q.duration_minutes ?? 60;
            $('f-passing').value  = q.passing_score    ?? 70;
            $('f-active').checked = q.is_active !== false;
            $('f-start').value    = toLocal(q.start_at_iso || q.start_at);
            $('f-end').value      = toLocal(q.end_at_iso   || q.end_at);
            refreshActiveHint();

            // Load matkul dengan preselect course_id kuis
            await loadMatkul(q.course_id || '');
            updateChecklist();

        } catch (e) {
            toast(e.message, 'err');
        } finally {
            $('overlay').classList.remove('show');
        }
    }

    function toLocal(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        if (isNaN(d)) return '';
        const p = n => String(n).padStart(2,'0');
        return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
    }

    /* ── Validate all ────────────────────────────────────────── */
    function validateAll() {
        let ok = true;

        if (!$('f-title').value.trim()) {
            $('f-title').classList.add('err');
            $('err-title').classList.add('show');
            ok = false;
        }
        if (!$('f-course').value) {
            $('f-course').classList.add('err');
            $('err-course').classList.add('show');
            ok = false;
        }
        const dur = parseInt($('f-duration').value, 10);
        if (isNaN(dur) || dur < 1 || dur > 300) {
            $('f-duration').classList.add('err');
            $('err-duration').classList.add('show');
            ok = false;
        }
        if (!scheduleOk) ok = false;

        return ok;
    }

    /* ── Submit ──────────────────────────────────────────────── */
    window.submitForm = async function () {
        if (!validateAll()) {
            toast('Lengkapi semua field wajib.', 'err');
            const first = document.querySelector('.fc.err');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const btn = $('btnSubmit'), lbl = $('btnLabel');
        btn.disabled = true; lbl.textContent = 'Menyimpan...';

        const payload = {
            title:            $('f-title').value.trim(),
            description:      $('f-desc').value.trim() || null,
            course_id:        $('f-course').value,
            duration_minutes: parseInt($('f-duration').value, 10),
            passing_score:    parseInt($('f-passing').value, 10) || 70,
            is_active:        $('f-active').checked,
            start_at:         $('f-start').value || null,
            end_at:           $('f-end').value   || null,
        };

        try {
            const url    = MODE === 'edit' ? `${API}/admin/quizzes/${QUID}` : `${API}/admin/quizzes`;
            const method = MODE === 'edit' ? 'PUT' : 'POST';

            const res  = await fetch(url, {
                method,
                headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();

            if (res.ok) {
                if (MODE === 'create') {
                    const newId = data.data?._id || data.data?.id;
                    toast('Kuis berhasil dibuat!');
                    setTimeout(() => { window.location.href = `/kuis/${newId}/soal`; }, 900);
                } else {
                    toast('Kuis berhasil diperbarui!');
                    setTimeout(() => { window.location.href = '/kuis'; }, 900);
                }
            } else {
                let msg = data.message || 'Gagal menyimpan kuis.';
                if (data.errors) {
                    const key = Object.keys(data.errors)[0];
                    msg = data.errors[key]?.[0] || msg;
                    // Highlight field error dari backend
                    const fieldMap = { title:'f-title', course_id:'f-course', duration_minutes:'f-duration' };
                    if (fieldMap[key]) {
                        $(fieldMap[key]).classList.add('err');
                        const eEl = $('err-' + fieldMap[key].replace('f-',''));
                        if (eEl) { eEl.textContent = msg; eEl.classList.add('show'); }
                    }
                }
                toast(msg, 'err');
                btn.disabled = false;
                lbl.textContent = MODE === 'edit' ? 'Simpan Perubahan' : 'Simpan & Kelola Soal';
            }
        } catch (e) {
            toast('Koneksi bermasalah: ' + e.message, 'err');
            btn.disabled = false;
            lbl.textContent = MODE === 'edit' ? 'Simpan Perubahan' : 'Simpan & Kelola Soal';
        }
    };

    /* ── Keyboard shortcut ───────────────────────────────────── */
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') submitForm();
    });

    /* ── Init ────────────────────────────────────────────────── */
    (async function init() {
        if (MODE === 'edit' && QUID) {
            await loadQuizData();   // loadQuizData memanggil loadMatkul(course_id)
        } else {
            await loadMatkul();     // create: load matkul tanpa preselect
        }
        updateChecklist();
    })();
})();
</script>
@endpush