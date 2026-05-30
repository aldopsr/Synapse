@extends('layouts.app')

@section('title')
{{ $mode === 'edit' ? 'Edit Kuis' : 'Buat Kuis Baru' }} - Synapse
@endsection

@section('header_title')
{{ $mode === 'edit' ? 'Edit Kuis' : 'Buat Kuis Baru' }}
@endsection

@section('content')
<style>
.back-link { display:inline-flex; align-items:center; gap:7px; color:#9ca3af; font-size:13px; font-weight:600; text-decoration:none; margin-bottom:22px; transition:color .15s; }
.back-link:hover { color:#279685; }
.back-link svg { width:15px; height:15px; transition:transform .15s; }
.back-link:hover svg { transform:translateX(-3px); }

.form-layout { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; }
@media(max-width:820px) { .form-layout { grid-template-columns:1fr; } }

.form-card { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; margin-bottom:16px; }
.form-card:last-child { margin-bottom:0; }
.card-header { display:flex; align-items:center; gap:12px; padding:18px 22px 14px; border-bottom:1px solid #f5f6f8; }
.ch-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ch-icon svg { width:18px; height:18px; }
.ch-icon.teal   { background:rgba(39,150,133,.1); color:#279685; }
.ch-icon.amber  { background:rgba(245,158,11,.1);  color:#d97706; }
.ch-icon.purple { background:rgba(124,58,237,.1);  color:#7c3aed; }
.card-header h3 { font-size:14px; font-weight:700; color:#111827; margin:0 0 2px; }
.card-header p  { font-size:11px; color:#9ca3af; margin:0; }
.card-body { padding:18px 22px; }

.fg { margin-bottom:16px; }
.fg:last-child { margin-bottom:0; }
.fg label { display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:7px; }
.req { color:#ef4444; }
.opt { font-size:10px; color:#c4c8d0; font-weight:400; text-transform:none; letter-spacing:0; }

.fc { width:100%; padding:10px 13px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:13px; font-family:inherit; color:#111827; background:#fff; box-sizing:border-box; transition:border-color .15s, box-shadow .15s; }
.fc:focus { outline:none; border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.12); }
.fc::placeholder { color:#d1d5db; }
.fc:disabled { background:#f9fafb; color:#9ca3af; cursor:not-allowed; }
.fc.err { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.1); }
textarea.fc { resize:vertical; min-height:80px; }
select.fc { cursor:pointer; }

.field-hint { font-size:11px; color:#c4c8d0; margin-top:5px; }
.field-err  { font-size:11px; color:#ef4444; margin-top:5px; display:none; }
.field-err.show { display:block; }

.form-row { display:flex; gap:12px; }
.form-row .fg { flex:1; min-width:0; }

/* Matkul status */
.matkul-status { display:flex; align-items:center; gap:7px; padding:8px 12px; border-radius:9px; font-size:12px; font-weight:600; margin-top:7px; }
.ms-loading { background:#f5f6f8; color:#9ca3af; border:1px solid #e5e7eb; }
.ms-locked  { background:#f0fdf9; color:#0f6e56;  border:1px solid #b2e8e0; }
.ms-error   { background:#fffbeb; color:#92400e;  border:1px solid #fde68a; }
.ms-hidden  { display:none!important; }

/* Spinner */
.spin { width:13px; height:13px; border:2px solid #e5e7eb; border-top-color:#279685; border-radius:50%; animation:spin .6s linear infinite; flex-shrink:0; }
@keyframes spin { to{transform:rotate(360deg)} }

/* Toggle row */
.toggle-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px; background:#f9fafb; border-radius:11px; border:1.5px solid #e5e7eb; cursor:pointer; transition:border-color .15s; margin-bottom:16px; }
.toggle-row:hover { border-color:#279685; }
.toggle-info strong { display:block; font-size:13px; font-weight:700; color:#111827; margin-bottom:2px; }
.toggle-info small  { font-size:11px; color:#9ca3af; }
.tw { position:relative; width:42px; height:23px; flex-shrink:0; }
.tw input { opacity:0; width:0; height:0; position:absolute; }
.t-track { position:absolute; inset:0; background:#e5e7eb; border-radius:99px; transition:background .25s; }
.tw input:checked + .t-track { background:#279685; }
.t-thumb { position:absolute; top:2px; left:2px; width:19px; height:19px; background:#fff; border-radius:50%; box-shadow:0 1px 3px rgba(0,0,0,.2); pointer-events:none; transition:transform .25s; }
.tw input:checked ~ .t-thumb { transform:translateX(19px); }

/* Visibility */
.vis-toggle { display:flex; align-items:center; justify-content:space-between; padding:13px 14px; border:2px solid #e5e7eb; border-radius:11px; background:#f9fafb; cursor:pointer; user-select:none; transition:border-color .2s,background .2s; }
.vis-toggle:hover { border-color:#9ca3af; }
.vis-toggle.is-umum { border-color:#279685; background:#f0fdf9; }
.vis-toggle.is-umum .vis-trk { background:#279685; }
.vis-toggle.is-umum .vis-knb { left:22px; }
.vis-icon-box { width:34px; height:34px; border-radius:9px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.vis-icon-box svg { width:17px; height:17px; color:#6b7280; }
.vis-toggle.is-umum .vis-icon-box { background:rgba(39,150,133,.1); }
.vis-toggle.is-umum .vis-icon-box svg { color:#279685; }
.vis-info-txt { flex:1; }
.vis-main { font-size:13px; font-weight:700; color:#111827; }
.vis-sub  { font-size:11px; color:#9ca3af; margin-top:1px; }
.vis-trk { width:44px; height:24px; background:#e5e7eb; border-radius:12px; position:relative; transition:background .2s; flex-shrink:0; }
.vis-knb { position:absolute; top:2px; left:2px; width:20px; height:20px; background:#fff; border-radius:50%; transition:left .2s; box-shadow:0 1px 3px rgba(0,0,0,.25); }
.vis-info { display:flex; align-items:center; gap:10px; }

/* Schedule box */
.schedule-box { background:#fffbeb; border:1.5px solid #fde68a; border-radius:13px; padding:16px 18px; }
.schedule-head { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#92400e; margin-bottom:4px; }
.schedule-head svg { width:16px; height:16px; }
.schedule-sub { font-size:11px; color:#b45309; margin-bottom:14px; }

/* Submit */
.submit-wrap { padding:16px 22px; border-top:1px solid #f5f6f8; }
.btn-submit { width:100%; padding:12px; background:#279685; color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; transition:background .15s; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:8px; }
.btn-submit:hover { background:#1c6e60; }
.btn-submit:disabled { background:#9ca3af; cursor:not-allowed; }
.btn-submit svg { width:15px; height:15px; }

/* Checklist */
.check-item { display:flex; align-items:center; gap:9px; font-size:13px; color:#9ca3af; margin-bottom:9px; transition:color .2s; }
.check-item:last-child { margin-bottom:0; }
.check-item.done { color:#111827; font-weight:600; }
.check-dot { width:18px; height:18px; border-radius:50%; border:2px solid #e5e7eb; flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:all .2s; }
.check-dot svg { width:10px; height:10px; color:transparent; }
.check-item.done .check-dot { background:#279685; border-color:#279685; }
.check-item.done .check-dot svg { color:#fff; }

/* Tips */
.tip-item { display:flex; gap:9px; margin-bottom:11px; font-size:12px; }
.tip-item:last-child { margin-bottom:0; }
.tip-dot { width:5px; height:5px; border-radius:50%; background:#279685; flex-shrink:0; margin-top:5px; }
.tip-item p { color:#6b7280; margin:0; line-height:1.55; }
.tip-item strong { color:#111827; }

/* Loading overlay */
.overlay { display:none; position:fixed; inset:0; background:rgba(255,255,255,.8); z-index:200; align-items:center; justify-content:center; flex-direction:column; gap:12px; font-size:13px; font-weight:600; color:#6b7280; }
.overlay.show { display:flex; }
.overlay .spin { width:26px; height:26px; border-width:3px; }
</style>

<div class="overlay" id="overlay"><div class="spin"></div> Memuat data kuis...</div>

<a href="/kuis" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Kembali ke Daftar Kuis
</a>

<div class="form-layout">
    {{-- KOLOM KIRI --}}
    <div>
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon teal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div><h3 id="cardTitle">Buat Kuis Baru</h3><p>Informasi dasar kuis</p></div>
            </div>
            <div class="card-body">
                <div class="fg">
                    <label>Judul Kuis <span class="req">*</span></label>
                    <input type="text" id="f-title" class="fc" placeholder="Contoh: UTS Pemrograman Web" maxlength="255" oninput="liveCheck('f-title')">
                    <div class="field-err" id="err-title">Judul kuis wajib diisi.</div>
                </div>
                <div class="fg">
                    <label>Deskripsi <span class="opt">(opsional)</span></label>
                    <textarea id="f-desc" class="fc" rows="3" placeholder="Jelaskan topik atau petunjuk pengerjaan..."></textarea>
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
                        <input type="number" id="f-duration" class="fc" value="60" min="1" max="300" oninput="liveCheck('f-duration')">
                        <div class="field-hint">1 – 300 menit</div>
                        <div class="field-err" id="err-duration">Durasi harus 1–300 menit.</div>
                    </div>
                    <div class="fg">
                        <label>Nilai Kelulusan <span class="opt">(opsional)</span></label>
                        <input type="number" id="f-passing" class="fc" value="70" min="0" max="100">
                        <div class="field-hint">0 – 100 poin</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon amber">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div><h3>Status &amp; Jadwal</h3><p>Kapan mahasiswa bisa mengakses</p></div>
            </div>
            <div class="card-body">
                <label class="toggle-row" for="f-active">
                    <div class="toggle-info">
                        <strong>Status Kuis</strong>
                        <small id="activeHint">Kuis aktif — mahasiswa bisa melihat dan mengerjakan.</small>
                    </div>
                    <label class="tw">
                        <input type="checkbox" id="f-active" checked onchange="refreshActiveHint()">
                        <div class="t-track"></div><div class="t-thumb"></div>
                    </label>
                </label>

                <div class="fg">
                    <label>Hak Akses</label>
                    <div class="vis-toggle" id="visToggle" onclick="toggleVis()">
                        <div class="vis-info">
                            <div class="vis-icon-box" id="visIconBox">
                                <svg id="visIconSvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <div class="vis-info-txt">
                                <div class="vis-main" id="visMain">Hanya Mahasiswa</div>
                                <div class="vis-sub"  id="visSub">Tersembunyi untuk pengguna umum</div>
                            </div>
                        </div>
                        <div class="vis-trk"><div class="vis-knb"></div></div>
                    </div>
                    <input type="hidden" id="f-visibility" value="mahasiswa">
                    <div class="field-hint" style="margin-top:6px">Aktifkan untuk akses semua pengguna terdaftar</div>
                </div>

                <div class="schedule-box">
                    <div class="schedule-head">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Jadwal Kuis <span style="font-weight:400;color:#b45309;font-size:11px">(opsional)</span>
                    </div>
                    <div class="schedule-sub">Kosongkan untuk kuis tanpa batas waktu.</div>
                    <div class="form-row">
                        <div class="fg">
                            <label>Mulai</label>
                            <input type="datetime-local" id="f-start" class="fc" onchange="checkSchedule()">
                        </div>
                        <div class="fg">
                            <label>Selesai</label>
                            <input type="datetime-local" id="f-end" class="fc" onchange="checkSchedule()">
                        </div>
                    </div>
                    <div class="field-err" id="err-schedule" style="margin-top:6px">Jadwal selesai harus setelah jadwal mulai.</div>
                </div>
            </div>
            <div class="submit-wrap">
                <button class="btn-submit" id="btnSubmit" type="button" onclick="submitForm()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span id="btnLabel">Simpan &amp; Kelola Soal</span>
                </button>
                <p style="font-size:11px;color:#c4c8d0;text-align:center;margin:8px 0 0">Tekan <kbd style="background:#f5f6f8;padding:1px 5px;border-radius:4px;font-size:10px;border:1px solid #e5e7eb">Ctrl+Enter</kbd> untuk simpan</p>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN --}}
    <div>
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <div><h3>Kelengkapan Form</h3><p>Field wajib sebelum simpan</p></div>
            </div>
            <div class="card-body">
                <div class="check-item" id="ck-title">
                    <div class="check-dot"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <span>Judul kuis diisi</span>
                </div>
                <div class="check-item" id="ck-course">
                    <div class="check-dot"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <span>Mata kuliah dipilih</span>
                </div>
                <div class="check-item" id="ck-duration">
                    <div class="check-dot"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <span>Durasi valid (1–300 menit)</span>
                </div>
                <div class="check-item" id="ck-schedule">
                    <div class="check-dot"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <span>Jadwal konsisten</span>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon amber">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div><h3>Tips</h3><p>Buat kuis yang efektif</p></div>
            </div>
            <div class="card-body">
                <div class="tip-item"><div class="tip-dot"></div><p>Judul yang jelas membantu mahasiswa mengetahui topik sebelum mulai.</p></div>
                <div class="tip-item"><div class="tip-dot"></div><p><strong>Nonaktif</strong> = draft tersimpan, tidak muncul di app mahasiswa. Aktifkan saat siap.</p></div>
                <div class="tip-item"><div class="tip-dot"></div><p>Setelah simpan, kamu langsung diarahkan ke <strong>halaman soal</strong>.</p></div>
                <div class="tip-item"><div class="tip-dot"></div><p>Jadwal opsional — tanpa jadwal, kuis tersedia terus selama aktif.</p></div>
            </div>
        </div>
    </div>
</div>
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
    let matkulOk = false, scheduleOk = true;

    if (MODE === 'edit') { $('cardTitle').textContent = 'Edit Kuis'; $('btnLabel').textContent = 'Simpan Perubahan'; $('overlay').classList.add('show'); }
    updateChecklist();

    window.refreshActiveHint = function() {
        $('activeHint').textContent = $('f-active').checked
            ? 'Kuis aktif — mahasiswa bisa melihat dan mengerjakan.'
            : 'Kuis nonaktif — tersembunyi dari mahasiswa.';
    };

    const lockSVG  = `<svg id="visIconSvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`;
    const globeSVG = `<svg id="visIconSvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>`;

    function setVisVal(val) {
        $('f-visibility').value = val;
        const tog=$('visToggle');
        if (val==='umum') {
            tog.classList.add('is-umum');
            $('visMain').textContent='Umum — Semua Pengguna Terdaftar';
            $('visSub').textContent='Dapat diakses non-mahasiswa yang sudah login';
            $('visIconSvg').outerHTML=globeSVG;
        } else {
            tog.classList.remove('is-umum');
            $('visMain').textContent='Hanya Mahasiswa';
            $('visSub').textContent='Tersembunyi untuk pengguna umum';
            $('visIconSvg').outerHTML=lockSVG;
        }
    }
    window.toggleVis = function() { setVisVal($('f-visibility').value==='mahasiswa'?'umum':'mahasiswa'); };

    function updateChecklist() {
        setDone('ck-title',    $('f-title').value.trim().length>0);
        setDone('ck-course',   !!$('f-course').value && matkulOk);
        const dur=parseInt($('f-duration').value,10);
        setDone('ck-duration', !isNaN(dur)&&dur>=1&&dur<=300);
        setDone('ck-schedule', scheduleOk);
    }
    function setDone(id,done) { const el=$(id); if(!el)return; el.classList.toggle('done',done); }

    window.liveCheck = function(id) {
        const el=$(id), err=$('err-'+id.replace('f-',''));
        let ok=false;
        if(id==='f-title')    ok=el.value.trim().length>0;
        if(id==='f-course')   ok=!!el.value;
        if(id==='f-duration') { const n=parseInt(el.value,10); ok=!isNaN(n)&&n>=1&&n<=300; }
        el.classList.toggle('err',!ok);
        if(err) err.classList.toggle('show',!ok);
        updateChecklist();
    };

    window.checkSchedule = function() {
        const s=$('f-start').value, e=$('f-end').value;
        const bad=s&&e&&e<=s;
        scheduleOk=!bad;
        $('f-end').classList.toggle('err',bad);
        $('err-schedule').classList.toggle('show',bad);
        updateChecklist();
    };

    async function loadMatkul(preId) {
        const sel=$('f-course'), st=$('matkulStatus');
        sel.innerHTML='<option value="">— Memuat... —</option>'; sel.disabled=true;
        st.className='matkul-status ms-loading'; st.innerHTML='<div class="spin"></div> Memuat mata kuliah...';
        try {
            const res=await fetch(API+'/courses',{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(!res.ok) throw new Error('HTTP '+res.status);
            const list=(await res.json()).data||[];
            if(!list.length){
                sel.innerHTML='<option value="">— Tidak ada mata kuliah —</option>';
                st.className='matkul-status ms-error';
                st.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/></svg> ${isAdmin?'Tambahkan mata kuliah terlebih dahulu.':'Kamu belum ditugaskan ke matkul. Hubungi admin.'}`;
                matkulOk=false; updateChecklist(); return;
            }
            sel.innerHTML='<option value="">— Pilih mata kuliah —</option>';
            list.forEach(c=>{const id=c._id||c.id;const o=document.createElement('option');o.value=id;o.textContent=c.title;if(preId&&String(id)===String(preId))o.selected=true;sel.appendChild(o);});
            if(list.length===1&&!preId){sel.value=list[0]._id||list[0].id;sel.disabled=true;st.className='matkul-status ms-locked';st.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Terikat ke: <strong style="margin-left:4px">${list[0].title}</strong>`;}
            else{sel.disabled=false;st.className='matkul-status ms-hidden';}
            matkulOk=true; liveCheck('f-course');
        } catch(e){
            const pid=preId||'';
            sel.innerHTML='<option value="">— Gagal memuat —</option>';
            st.className='matkul-status ms-error';
            st.innerHTML=`Gagal koneksi. <button type="button" onclick="retryMatkul('${pid}')" style="font-weight:700;color:#92400e;background:none;border:none;cursor:pointer;text-decoration:underline;padding:0;font-family:inherit">Coba lagi</button>`;
            matkulOk=false; updateChecklist();
        }
    }
    window.retryMatkul = loadMatkul;

    async function loadQuizData() {
        try {
            const res=await fetch(`${API}/admin/quizzes/${QUID}`,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(!res.ok) throw new Error('Kuis tidak ditemukan (HTTP '+res.status+')');
            const q=(await res.json()).data;
            if(!q) throw new Error('Data kuis kosong');
            $('f-title').value=$('f-title').value||q.title||'';
            $('f-desc').value=q.description||'';
            $('f-duration').value=q.duration_minutes??60;
            $('f-passing').value=q.passing_score??70;
            $('f-active').checked=q.is_active!==false;
            setVisVal(q.visibility||'mahasiswa');
            $('f-start').value=toLocal(q.start_at_iso||q.start_at);
            $('f-end').value=toLocal(q.end_at_iso||q.end_at);
            refreshActiveHint();
            await loadMatkul(q.course_id||'');
            updateChecklist();
        } catch(e){ toast(e.message,'err'); }
        finally { $('overlay').classList.remove('show'); }
    }

    function toLocal(iso){if(!iso)return'';const d=new Date(iso);if(isNaN(d))return'';const p=n=>String(n).padStart(2,'0');return`${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;}

    function validateAll(){
        let ok=true;
        if(!$('f-title').value.trim()){$('f-title').classList.add('err');$('err-title').classList.add('show');ok=false;}
        if(!$('f-course').value){$('f-course').classList.add('err');$('err-course').classList.add('show');ok=false;}
        const dur=parseInt($('f-duration').value,10);
        if(isNaN(dur)||dur<1||dur>300){$('f-duration').classList.add('err');$('err-duration').classList.add('show');ok=false;}
        if(!scheduleOk) ok=false;
        return ok;
    }

    window.submitForm = async function() {
        if(!validateAll()){toast('Lengkapi semua field wajib.','err');const f=document.querySelector('.fc.err');if(f)f.scrollIntoView({behavior:'smooth',block:'center'});return;}
        const btn=$('btnSubmit'),lbl=$('btnLabel');
        btn.disabled=true; lbl.textContent='Menyimpan...';
        const payload={title:$('f-title').value.trim(),description:$('f-desc').value.trim()||null,course_id:$('f-course').value,duration_minutes:parseInt($('f-duration').value,10),passing_score:parseInt($('f-passing').value,10)||70,is_active:$('f-active').checked,start_at:$('f-start').value||null,end_at:$('f-end').value||null,visibility:$('f-visibility').value};
        try {
            const url=MODE==='edit'?`${API}/admin/quizzes/${QUID}`:`${API}/admin/quizzes`;
            const res=await fetch(url,{method:MODE==='edit'?'PUT':'POST',headers:{Authorization:'Bearer '+token,'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify(payload)});
            const data=await res.json();
            if(res.ok){
                if(MODE==='create'){toast('Kuis berhasil dibuat!');setTimeout(()=>{window.location.href=`/kuis/${data.data?._id||data.data?.id}/soal`;},900);}
                else{toast('Kuis berhasil diperbarui!');setTimeout(()=>{window.location.href='/kuis';},900);}
            } else {
                let msg=data.message||'Gagal menyimpan.';
                if(data.errors){const k=Object.keys(data.errors)[0];msg=data.errors[k]?.[0]||msg;const fm={title:'f-title',course_id:'f-course',duration_minutes:'f-duration'};if(fm[k]){$(fm[k]).classList.add('err');const e=$('err-'+fm[k].replace('f-',''));if(e){e.textContent=msg;e.classList.add('show');}}}
                toast(msg,'err'); btn.disabled=false; lbl.textContent=MODE==='edit'?'Simpan Perubahan':'Simpan & Kelola Soal';
            }
        } catch(e){ toast('Koneksi bermasalah.','err'); btn.disabled=false; lbl.textContent=MODE==='edit'?'Simpan Perubahan':'Simpan & Kelola Soal'; }
    };

    document.addEventListener('keydown',e=>{if((e.ctrlKey||e.metaKey)&&e.key==='Enter')submitForm();});

    (async function(){
        if(MODE==='edit'&&QUID) await loadQuizData();
        else await loadMatkul();
        updateChecklist();
    })();
})();
</script>
@endpush