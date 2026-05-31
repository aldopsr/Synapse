@extends('layouts.app')
@section('title','Kelola Soal - Synapse')
@section('header_title','Kelola Soal')

@section('content')
<style>
.back-link { display:inline-flex; align-items:center; gap:7px; color:#9ca3af; font-size:13px; font-weight:600; text-decoration:none; margin-bottom:18px; transition:color .15s; }
.back-link:hover { color:#279685; }
.back-link svg { width:15px; height:15px; transition:transform .15s; }
.back-link:hover svg { transform:translateX(-3px); }

/* Banner */
.quiz-banner { background:linear-gradient(135deg,#279685,#1a6b5e); border-radius:16px; padding:18px 24px; margin-bottom:22px; display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; }
.qb-title { font-size:15px; font-weight:700; color:#fff; margin:0 0 8px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.qb-chips { display:flex; gap:7px; flex-wrap:wrap; }
.qb-chip  { background:rgba(255,255,255,.15); color:rgba(255,255,255,.9); font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; border:1px solid rgba(255,255,255,.2); }
.qb-count { font-size:36px; font-weight:800; color:#fff; line-height:1; }
.qb-count-label { font-size:11px; color:rgba(255,255,255,.6); }

/* Layout */
.soal-layout { display:grid; grid-template-columns:380px 1fr; gap:20px; align-items:start; }
@media(max-width:920px) { .soal-layout { grid-template-columns:1fr; } }

/* Card */
.sc { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }
.sc-head { display:flex; align-items:center; gap:10px; padding:15px 20px 12px; border-bottom:1px solid #f5f6f8; position:sticky; top:0; background:#fff; z-index:5; }
.ch-ico { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ch-ico svg { width:16px; height:16px; }
.ch-ico.teal   { background:rgba(39,150,133,.1); color:#279685; }
.ch-ico.purple { background:rgba(124,58,237,.1);  color:#7c3aed; }
.sc-head h3 { font-size:13px; font-weight:700; color:#111827; margin:0 0 1px; }
.sc-head p  { font-size:11px; color:#9ca3af; margin:0; }
.sc-body { padding:16px 20px; }

/* Type tabs */
.type-tabs { display:flex; gap:4px; margin-bottom:16px; padding:4px; background:#f5f6f8; border-radius:10px; }
.type-tab  { flex:1; padding:8px 4px; text-align:center; cursor:pointer; border-radius:7px; font-size:11px; font-weight:700; color:#9ca3af; border:none; background:transparent; transition:all .15s; font-family:inherit; }
.type-tab.active { background:#fff; color:#279685; box-shadow:0 1px 4px rgba(0,0,0,.08); }

/* Form */
.fg { margin-bottom:13px; }
.fg:last-child { margin-bottom:0; }
.fg label { display:block; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:5px; }
.req { color:#ef4444; }
.opt { color:#c4c8d0; font-weight:400; text-transform:none; letter-spacing:0; font-size:10px; }
.form-row-2 { display:flex; gap:10px; }
.form-row-2 .fg { flex:1; min-width:0; }
.fc { width:100%; padding:9px 11px; border:1.5px solid #e5e7eb; border-radius:9px; font-size:13px; font-family:inherit; color:#111827; background:#fff; box-sizing:border-box; transition:border-color .15s,box-shadow .15s; }
.fc:focus { outline:none; border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.1); }
.fc::placeholder { color:#d1d5db; }
textarea.fc { resize:vertical; min-height:72px; }

/* Option rows */
.option-row { display:flex; align-items:center; gap:8px; margin-bottom:7px; }
.opt-letter { width:24px; height:24px; border-radius:50%; background:#f5f6f8; color:#9ca3af; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.opt-input { flex:1; padding:8px 10px; border:1.5px solid #e5e7eb; border-radius:8px; font-size:12px; font-family:inherit; background:#fff; box-sizing:border-box; transition:border-color .15s; }
.opt-input:focus { outline:none; border-color:#279685; }
.opt-radio, .opt-check { width:16px; height:16px; accent-color:#279685; cursor:pointer; flex-shrink:0; }
.option-hint { font-size:11px; color:#92400e; background:#fef3c7; padding:7px 10px; border-radius:8px; margin-top:6px; display:flex; align-items:center; gap:6px; }
.option-hint svg { width:13px; height:13px; flex-shrink:0; }

/* True/false */
.tf-row { display:flex; gap:8px; }
.tf-btn { flex:1; padding:10px; border:1.5px solid #e5e7eb; border-radius:9px; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; background:#fff; color:#9ca3af; transition:all .15s; }
.tf-btn.sel-benar { background:#f0fdf9; border-color:#279685; color:#0f6e56; }
.tf-btn.sel-salah { background:#fef2f2; border-color:#ef4444; color:#dc2626; }

/* Difficulty */
.diff-row { display:flex; gap:6px; }
.diff-pill { flex:1; padding:7px; text-align:center; border:1.5px solid #e5e7eb; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; background:#fff; color:#9ca3af; transition:all .15s; }
.diff-pill.dm.active { background:#f0fdf9; border-color:#279685; color:#0f6e56; }
.diff-pill.ds.active { background:#fffbeb; border-color:#f59e0b; color:#92400e; }
.diff-pill.dd.active { background:#fef2f2; border-color:#ef4444; color:#dc2626; }

/* Image uploader */
.img-drop { border:2px dashed #e5e7eb; border-radius:10px; padding:16px; text-align:center; cursor:pointer; background:#fafafa; transition:border-color .15s,background .15s; position:relative; }
.img-drop:hover { border-color:#279685; background:#f0fdf9; }
.img-drop input { display:none; }
.img-ico { width:40px; height:40px; border-radius:11px; background:rgba(39,150,133,.1); display:flex; align-items:center; justify-content:center; margin:0 auto 8px; color:#279685; }
.img-ico svg { width:20px; height:20px; }
.img-drop-label { font-size:12px; font-weight:700; color:#374151; margin-bottom:2px; }
.img-drop-hint  { font-size:10px; color:#c4c8d0; }
.img-preview-wrap { position:relative; }
.img-preview-wrap img { width:100%; border-radius:8px; display:block; max-height:160px; object-fit:cover; }
.img-remove { position:absolute; top:6px; right:6px; background:rgba(0,0,0,.55); color:#fff; border:none; padding:4px 9px; border-radius:6px; font-size:11px; cursor:pointer; font-weight:700; font-family:inherit; display:flex; align-items:center; gap:4px; }
.img-remove:hover { background:rgba(239,68,68,.9); }
.img-remove svg { width:11px; height:11px; }

/* Submit */
.btn-submit { width:100%; padding:11px; background:#279685; color:#fff; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:background .15s; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:7px; }
.btn-submit:hover { background:#1c6e60; }
.btn-submit:disabled { background:#9ca3af; cursor:not-allowed; }
.btn-submit svg { width:14px; height:14px; }

.sec-hidden { display:none!important; }

/* Soal list */
.soal-list { padding:14px 18px; display:flex; flex-direction:column; gap:10px; }
.soal-card { border:1px solid #eff0f2; border-radius:12px; background:#fff; overflow:hidden; transition:box-shadow .2s,opacity .25s,transform .25s; }
.soal-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.06); }
.soal-card.deleting { opacity:0; transform:translateX(20px) scale(.97); pointer-events:none; }

.sc-header { display:flex; align-items:center; gap:8px; padding:11px 14px; background:#fafafa; border-bottom:1px solid #f5f6f8; cursor:pointer; user-select:none; }
.sc-num { width:24px; height:24px; border-radius:50%; background:#279685; color:#fff; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sc-badges { display:flex; gap:5px; flex-wrap:wrap; flex:1; }
.sc-badge { font-size:10px; font-weight:700; padding:2px 9px; border-radius:99px; }
.b-type   { background:#f0fdf9; color:#0f6e56; }
.b-mudah  { background:#d1fae5; color:#065f46; }
.b-sedang { background:#fef3c7; color:#92400e; }
.b-sulit  { background:#fee2e2; color:#991b1b; }
.b-pts    { background:#f5f3ff; color:#534ab7; }

.sc-actions { display:flex; gap:5px; align-items:center; flex-shrink:0; }
.sc-toggle { font-size:11px; color:#9ca3af; padding:4px 8px; border-radius:6px; border:1.5px solid #e5e7eb; background:#fff; cursor:pointer; font-family:inherit; transition:all .15s; font-weight:600; display:flex; align-items:center; gap:4px; }
.sc-toggle svg { width:11px; height:11px; }
.sc-toggle:hover { border-color:#279685; color:#279685; }
.sc-toggle.open { background:#f0fdf9; border-color:#b2e8e0; color:#0f6e56; }
.btn-del-soal { display:flex; align-items:center; gap:4px; padding:5px 10px; border-radius:7px; font-size:11px; font-weight:700; border:none; background:#fef2f2; color:#dc2626; cursor:pointer; font-family:inherit; transition:background .15s; }
.btn-del-soal svg { width:11px; height:11px; }
.btn-del-soal:hover { background:#fee2e2; }

/* Preview */
.sc-preview { display:none; padding:12px 14px; border-top:1px solid #f5f6f8; animation:fadeIn .18s ease; }
.sc-preview.open { display:block; }
@keyframes fadeIn { from{opacity:0;transform:translateY(-4px)} to{opacity:1;transform:none} }
.sc-qtext { font-size:13px; font-weight:700; color:#111827; margin-bottom:10px; line-height:1.5; }
.sc-qimg  { width:100%; max-height:180px; object-fit:cover; border-radius:8px; margin-bottom:10px; border:1px solid #eff0f2; }
.sc-opts  { list-style:none; padding:0; margin:0 0 10px; display:flex; flex-direction:column; gap:5px; }
.sc-opts li { display:flex; align-items:center; gap:8px; padding:7px 11px; border-radius:8px; font-size:12px; color:#6b7280; border:1px solid #eff0f2; background:#fafafa; }
.sc-opts li.correct { border-color:#279685; background:#f0fdf9; color:#0f6e56; font-weight:700; }
.opt-dot { width:18px; height:18px; border-radius:50%; background:#e5e7eb; color:#9ca3af; font-size:9px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sc-opts li.correct .opt-dot { background:#279685; color:#fff; }
.sc-expl { background:#f0fdf9; border-left:3px solid #279685; border-radius:6px; padding:8px 12px; font-size:12px; color:#0f6e56; display:flex; align-items:flex-start; gap:6px; }
.sc-expl svg { width:13px; height:13px; flex-shrink:0; margin-top:1px; }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:8px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Empty */
.empty-soal { text-align:center; padding:48px 20px; }
.empty-ico  { width:56px; height:56px; border-radius:16px; background:#f5f6f8; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
.empty-ico svg { width:28px; height:28px; color:#d1d5db; }
.empty-el { font-size:14px; font-weight:700; color:#374151; margin-bottom:5px; }
.empty-es { font-size:12px; color:#9ca3af; }

/* AI panel */
.ai-panel { background:linear-gradient(135deg,#e6f4f2,#f0fdfb); border:1.5px solid #a7f3d0; border-radius:16px; padding:20px 24px; margin-bottom:24px; }
.ai-panel-header { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
.ai-panel-header h3 { font-size:15px; font-weight:700; color:#065f46; margin:0; }
.ai-badge { display:inline-flex; align-items:center; gap:5px; background:#279685; color:#fff; padding:2px 10px; border-radius:99px; font-size:11px; font-weight:700; }
.ai-badge svg { width:12px; height:12px; }
.ai-collapse { margin-left:auto; background:none; border:none; cursor:pointer; color:#0f6e56; display:flex; align-items:center; padding:4px 8px; border-radius:7px; transition:background .15s; }
.ai-collapse:hover { background:rgba(39,150,133,.1); }
.ai-collapse svg { width:16px; height:16px; transition:transform .2s; }
.ai-collapse.collapsed svg { transform:rotate(180deg); }
.ai-mode-tabs { display:flex; margin-bottom:16px; border:1px solid #a7f3d0; border-radius:10px; overflow:hidden; }
.ai-mode-tab { flex:1; padding:8px 12px; text-align:center; font-size:12px; font-weight:600; cursor:pointer; border:none; background:transparent; color:#0f6e56; transition:background .15s; font-family:inherit; }
.ai-mode-tab.active { background:#279685; color:#fff; }
.ai-input-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
.ai-input-row input, .ai-input-row select { flex:1; min-width:160px; padding:8px 12px; border:1.5px solid #a7f3d0; border-radius:8px; font-size:13px; font-family:inherit; background:#fff; outline:none; }
.ai-input-row input:focus, .ai-input-row select:focus { border-color:#279685; }
.ai-counts-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
.ai-count-box { flex:1; min-width:120px; background:#fff; border:1px solid #a7f3d0; border-radius:10px; padding:10px 12px; }
.ai-count-box label { display:block; font-size:10px; font-weight:700; color:#0f6e56; margin-bottom:6px; text-transform:uppercase; }
.ai-count-box input { width:100%; padding:6px 8px; border:1.5px solid #e5e7eb; border-radius:6px; font-size:16px; font-weight:700; text-align:center; font-family:inherit; box-sizing:border-box; outline:none; }
.btn-generate { width:100%; padding:11px; border:none; border-radius:10px; background:linear-gradient(135deg,#279685,#1a6b5e); color:#fff; font-size:14px; font-weight:700; cursor:pointer; transition:opacity .15s; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:7px; }
.btn-generate svg { width:16px; height:16px; }
.btn-generate:hover { opacity:.9; }
.btn-generate:disabled { opacity:.6; cursor:not-allowed; }
.ai-preview { margin-top:16px; }
.ai-preview-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.ai-preview-header h4 { font-size:13px; font-weight:700; color:#065f46; margin:0; }
.btn-accept-all { padding:7px 16px; border:none; border-radius:8px; background:#279685; color:#fff; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; display:flex; align-items:center; gap:5px; }
.btn-accept-all svg { width:13px; height:13px; }
.ai-q-card { background:#fff; border:1px solid #d1fae5; border-radius:10px; padding:14px 16px; margin-bottom:10px; }
.ai-q-card.accepted { border-color:#279685; background:#f0fdf9; }
.ai-q-type-badge { display:inline-block; padding:1px 8px; border-radius:5px; font-size:10px; font-weight:700; margin-bottom:8px; }
.badge-mc { background:#dbeafe; color:#1e40af; }
.badge-tf { background:#fef3c7; color:#92400e; }
.badge-ma { background:#ede9fe; color:#4c1d95; }
.ai-q-text { font-size:13px; font-weight:600; color:#111827; margin-bottom:8px; }
.ai-q-opts { display:grid; grid-template-columns:1fr 1fr; gap:4px; margin-bottom:8px; }
.ai-q-opt  { font-size:12px; color:#6b7280; padding:3px 6px; border-radius:4px; }
.ai-q-opt.correct { background:#d1fae5; color:#065f46; font-weight:700; }
.ai-q-explanation { font-size:11px; color:#6b7280; font-style:italic; padding:6px 8px; background:#f9fafb; border-radius:6px; margin-bottom:8px; }
.ai-q-actions { display:flex; gap:8px; }
.btn-edit-q   { flex:1; padding:5px; border:1.5px solid #e5e7eb; border-radius:6px; background:#fff; font-size:11px; font-weight:600; cursor:pointer; font-family:inherit; }
.btn-accept-q { flex:1; padding:5px; border:none; border-radius:6px; background:#279685; color:#fff; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; }
.btn-reject-q { padding:5px 10px; border:1.5px solid #fecaca; border-radius:6px; background:#fff; color:#dc2626; font-size:11px; font-weight:600; cursor:pointer; font-family:inherit; }
.ai-edit-area { margin-bottom:8px; display:none; }
.ai-edit-area.open { display:block; }
.ai-edit-area textarea, .ai-edit-area input { width:100%; padding:6px 8px; border:1.5px solid #e5e7eb; border-radius:6px; font-size:12px; font-family:inherit; box-sizing:border-box; margin-bottom:4px; outline:none; }
.ai-edit-area textarea { resize:vertical; min-height:60px; }
</style>

<a href="/kuis" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Kembali ke Daftar Kuis
</a>

<div class="quiz-banner">
    <div class="qb-left">
        <div class="qb-title" id="bannerTitle">Memuat info kuis...</div>
        <div class="qb-chips" id="bannerChips"></div>
    </div>
    <div class="qb-right">
        <div class="qb-count" id="soalCount">—</div>
        <div class="qb-count-label">soal</div>
    </div>
</div>

{{-- AI Panel --}}
<div class="ai-panel" id="aiPanel">
    <div class="ai-panel-header">
        <div style="width:32px;height:32px;border-radius:9px;background:#279685;display:flex;align-items:center;justify-content:center">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        </div>
        <h3>Generate Soal Otomatis</h3>
        <button class="ai-collapse" id="aiCollapseBtn" onclick="toggleAiPanel()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
        </button>
    </div>
    <div id="aiPanelBody">
        <div class="ai-mode-tabs">
            <button class="ai-mode-tab active" onclick="setAiMode('topic')" id="tab-topic">Dari Topik</button>
            <button class="ai-mode-tab" onclick="setAiMode('material')" id="tab-material">Dari Materi</button>
            <button class="ai-mode-tab" onclick="setAiMode('quiz')" id="tab-quiz" style="display:none">Dari Kuis</button>
        </div>
        <div class="ai-input-row" id="aiInputTopic">
            <input type="text" id="aiTopic" placeholder="Contoh: Jaringan komputer — protokol TCP/IP">
        </div>
        <div class="ai-input-row" id="aiInputMaterial" style="display:none">
            <select id="aiMaterialId"><option value="">— Pilih materi —</option></select>
        </div>
        <div class="ai-counts-row">
            <div class="ai-count-box"><label>Pilihan Ganda</label><input type="number" id="cntMC" value="3" min="0" max="15"></div>
            <div class="ai-count-box"><label>Benar / Salah</label><input type="number" id="cntTF" value="2" min="0" max="15"></div>
            <div class="ai-count-box"><label>Multi Jawaban</label><input type="number" id="cntMA" value="1" min="0" max="15"></div>
            <div class="ai-count-box">
                <label>Kesulitan</label>
                <select id="aiDifficulty" style="width:100%;padding:6px 8px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;font-family:inherit">
                    <option value="mudah">Mudah</option>
                    <option value="sedang" selected>Sedang</option>
                    <option value="sulit">Sulit</option>
                </select>
            </div>
        </div>
        <button class="btn-generate" id="btnGenerate" onclick="generateSoal()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Generate Soal Sekarang
        </button>
        <div class="ai-preview" id="aiPreview" style="display:none">
            <div class="ai-preview-header">
                <h4 id="aiPreviewTitle">— soal berhasil di-generate</h4>
                <button class="btn-accept-all" onclick="acceptAllSoal()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Simpan Semua
                </button>
            </div>
            <div id="aiPreviewList"></div>
        </div>
    </div>
</div>

<div class="soal-layout">
    {{-- FORM TAMBAH --}}
    <div class="sc" style="position:sticky;top:20px">
        <div class="sc-head">
            <div class="ch-ico teal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div><h3>Tambah Soal Baru</h3><p>Isi detail soal lalu klik Simpan</p></div>
        </div>
        <div class="sc-body">
            <div class="type-tabs" id="typeTabs">
                <button type="button" class="type-tab active" data-type="multiple_choice">Pilihan Ganda</button>
                <button type="button" class="type-tab" data-type="true_false">True/False</button>
                <button type="button" class="type-tab" data-type="multiple_answer">Multi Jawab</button>
            </div>
            <input type="hidden" id="q_type" value="multiple_choice">

            <div class="fg">
                <label>Pertanyaan <span class="req">*</span></label>
                <textarea id="q_text" class="fc" rows="3" placeholder="Tulis pertanyaan di sini..."></textarea>
            </div>

            <div class="fg">
                <label>Gambar <span class="opt">(opsional, maks 2MB)</span></label>
                <div class="img-drop" id="imgDrop" onclick="document.getElementById('q_image').click()"
                    ondragover="event.preventDefault();this.style.borderColor='#279685'"
                    ondragleave="this.style.borderColor=''"
                    ondrop="handleImgDrop(event)">
                    <input type="file" id="q_image" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="onImgSelected(this.files[0])">
                    <div id="imgPlaceholder">
                        <div class="img-ico">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="img-drop-label">Klik atau seret gambar</div>
                        <div class="img-drop-hint">PNG · JPG · WEBP</div>
                    </div>
                    <div class="img-preview-wrap" id="imgPreviewWrap" style="display:none">
                        <img id="imgPreview" src="" alt="preview">
                        <button type="button" class="img-remove" onclick="event.stopPropagation();removeImg()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <div id="sec_multiple_choice">
                <div class="fg">
                    <label>Pilihan Jawaban <span class="req">*</span></label>
                    @foreach(['a','b','c','d'] as $l)
                    <div class="option-row">
                        <input type="radio" name="mc_ans" id="mc_{{ $l }}" value="{{ strtoupper($l) }}" class="opt-radio" {{ $l==='a'?'checked':'' }}>
                        <span class="opt-letter">{{ strtoupper($l) }}</span>
                        <input type="text" id="opt_mc_{{ $l }}" class="opt-input" placeholder="Pilihan {{ strtoupper($l) }}">
                    </div>
                    @endforeach
                    <div class="option-hint">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Klik radio di kiri untuk tandai jawaban benar
                    </div>
                </div>
            </div>

            <div id="sec_true_false" class="sec-hidden">
                <div class="fg">
                    <label>Pilih Jawaban Benar <span class="req">*</span></label>
                    <div class="tf-row">
                        <button type="button" class="tf-btn sel-benar" id="tf_benar" onclick="setTF('benar')">Benar / True</button>
                        <button type="button" class="tf-btn" id="tf_salah" onclick="setTF('salah')">Salah / False</button>
                    </div>
                    <input type="hidden" id="tf_ans" value="A">
                </div>
            </div>

            <div id="sec_multiple_answer" class="sec-hidden">
                <div class="fg">
                    <label>Pilihan Jawaban <span class="req">*</span> <span class="opt">(centang semua yang benar)</span></label>
                    @foreach(['a','b','c','d'] as $l)
                    <div class="option-row">
                        <input type="checkbox" id="ma_chk_{{ $l }}" value="{{ strtoupper($l) }}" class="opt-check">
                        <span class="opt-letter">{{ strtoupper($l) }}</span>
                        <input type="text" id="opt_ma_{{ $l }}" class="opt-input" placeholder="Pilihan {{ strtoupper($l) }}">
                    </div>
                    @endforeach
                    <div class="option-hint">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Bisa pilih lebih dari satu jawaban benar
                    </div>
                </div>
            </div>

            <div class="fg">
                <label>Penjelasan Jawaban <span class="opt">(opsional)</span></label>
                <textarea id="q_explanation" class="fc" rows="2" placeholder="Ditampilkan ke mahasiswa setelah submit..."></textarea>
            </div>

            <div class="form-row-2">
                <div class="fg">
                    <label>Bobot Poin</label>
                    <input type="number" id="q_points" class="fc" value="10" min="1" max="100">
                </div>
                <div class="fg">
                    <label>Kesulitan</label>
                    <div class="diff-row">
                        <button type="button" class="diff-pill dm" onclick="setDiff('mudah')">Mudah</button>
                        <button type="button" class="diff-pill ds active" onclick="setDiff('sedang')">Sedang</button>
                        <button type="button" class="diff-pill dd" onclick="setDiff('sulit')">Sulit</button>
                    </div>
                    <input type="hidden" id="q_difficulty" value="sedang">
                </div>
            </div>

            <div class="fg" style="margin-top:16px">
                <button class="btn-submit" id="btnSimpan" type="button" onclick="submitSoal()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span id="btnLabel">Simpan Soal</span>
                </button>
            </div>
        </div>
    </div>

    {{-- DAFTAR SOAL --}}
    <div class="sc">
        <div class="sc-head">
            <div class="ch-ico purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <div><h3>Daftar Soal</h3><p id="listSubtitle">Memuat...</p></div>
        </div>
        <div id="soalList" class="soal-list">
            @for($i=0;$i<4;$i++)
            <div style="border:1px solid #eff0f2;border-radius:12px;overflow:hidden">
                <div style="padding:11px 14px;background:#fafafa;display:flex;align-items:center;gap:8px">
                    <div class="skeleton" style="width:24px;height:24px;border-radius:50%;flex-shrink:0"></div>
                    <div class="skeleton" style="width:70px;height:18px;border-radius:99px"></div>
                    <div class="skeleton" style="width:55px;height:18px;border-radius:99px"></div>
                    <div style="margin-left:auto;display:flex;gap:5px">
                        <div class="skeleton" style="width:50px;height:26px;border-radius:6px"></div>
                        <div class="skeleton" style="width:55px;height:26px;border-radius:6px"></div>
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const API   = window.apiBaseUrl;
    const token = window.token;
    const QUIZ  = '{{ $quiz_id ?? "" }}';
    if (!QUIZ) { toast('Quiz ID tidak valid','err'); window.location.href='/kuis'; return; }
    const $ = id => document.getElementById(id);

    let allSoal=[], selImg=null, tfChoice='A', diffChoice='sedang';

    function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    const SVG = {
        eye:   `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`,
        eyeoff:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`,
        trash: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`,
        info:  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
        inbox: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>`,
    };

    async function loadQuizInfo() {
        try {
            const res=(await fetch(`${API}/admin/quizzes/${QUIZ}`,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}}));
            const q=(await res.json()).data;
            if(!q)return;
            $('bannerTitle').textContent=q.title||'Kuis';
            const statusMap={aktif:'Aktif',nonaktif:'Nonaktif',belum_mulai:'Belum Mulai',sudah_selesai:'Selesai'};
            const chips=[q.course?.title?`${esc(q.course.title)}`:null,`${q.duration_minutes||0} mnt`,`KKM: ${q.passing_score||70}`,statusMap[q.status]||q.status].filter(Boolean);
            $('bannerChips').innerHTML=chips.map(c=>`<span class="qb-chip">${c}</span>`).join('');
        } catch(_){ $('bannerTitle').textContent='Kuis'; }
    }

    async function fetchSoal() {
        try {
            const res=await fetch(`${API}/admin/quizzes/${QUIZ}/questions`,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.status===401){window.logout();return;}
            allSoal=(await res.json()).data||[];
            renderSoal();
        } catch(e){
            $('soalList').innerHTML=`<div class="empty-soal"><div class="empty-ico">${SVG.info}</div><div class="empty-el">Gagal memuat soal</div><div class="empty-es">${esc(e.message)}</div><button onclick="fetchSoal()" style="margin-top:12px;padding:7px 16px;background:#279685;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:12px">Coba lagi</button></div>`;
        }
    }

    function updateCounter() { $('soalCount').textContent=allSoal.length; $('listSubtitle').textContent=allSoal.length+' soal tersimpan'; }

    function renderSoal() {
        updateCounter();
        const list=$('soalList');
        if(!allSoal.length){
            list.innerHTML=`<div class="empty-soal"><div class="empty-ico">${SVG.inbox}</div><div class="empty-el">Belum ada soal</div><div class="empty-es">Tambahkan soal pertama lewat form di kiri.</div></div>`;
            return;
        }
        list.innerHTML=allSoal.map((q,i)=>buildCard(q,i)).join('');
    }

    function buildCard(q,idx) {
        const id=q._id||q.id, type=q.question_type||'multiple_choice', diff=q.difficulty||'sedang', pts=q.points||10;
        const typeLabel={multiple_choice:'Pilihan Ganda',true_false:'True/False',multiple_answer:'Multi Jawab'}[type]||type;
        const diffBadge={mudah:'b-mudah',sedang:'b-sedang',sulit:'b-sulit'}[diff]||'b-sedang';
        const diffLabel={mudah:'Mudah',sedang:'Sedang',sulit:'Sulit'}[diff]||diff;
        let optsHtml='<ul class="sc-opts">';
        if(type==='true_false'){
            const c=(q.correct_answer||'A').toUpperCase();
            optsHtml+=optItem('A','Benar / True',c==='A')+optItem('B','Salah / False',c==='B');
        } else if(type==='multiple_answer'){
            const cs=(q.correct_answers||[]).map(x=>x.toUpperCase());
            ['A','B','C','D'].forEach(l=>{const v=q['option_'+l.toLowerCase()];if(v)optsHtml+=optItem(l,v,cs.includes(l));});
        } else {
            const c=(q.correct_answer||'').toUpperCase();
            ['A','B','C','D'].forEach(l=>{const v=q['option_'+l.toLowerCase()];if(v)optsHtml+=optItem(l,v,c===l);});
        }
        optsHtml+='</ul>';
        const imgHtml=q.image_url?`<img class="sc-qimg" src="${esc(q.image_url)}" alt="soal" loading="lazy">`:'';
        const expHtml=q.explanation?`<div class="sc-expl">${SVG.info} ${esc(q.explanation)}</div>`:'';
        return `<div class="soal-card" id="soal-${id}">
            <div class="sc-header" onclick="togglePreview('${id}')">
                <span class="sc-num">${idx+1}</span>
                <div class="sc-badges">
                    <span class="sc-badge b-type">${typeLabel}</span>
                    <span class="sc-badge ${diffBadge}">${diffLabel}</span>
                    <span class="sc-badge b-pts">${pts} poin</span>
                </div>
                <div class="sc-actions" onclick="event.stopPropagation()">
                    <button class="sc-toggle" id="toggle-${id}" onclick="togglePreview('${id}')">${SVG.eye} Lihat</button>
                    <button class="btn-del-soal" onclick="hapusSoal('${id}',${idx+1})">${SVG.trash} Hapus</button>
                </div>
            </div>
            <div class="sc-preview" id="preview-${id}">
                <div class="sc-qtext">${esc(q.question||'')}</div>
                ${imgHtml}${optsHtml}${expHtml}
            </div>
        </div>`;
    }

    function optItem(letter,text,correct){
        return `<li class="${correct?'correct':''}"><div class="opt-dot">${letter}</div>${esc(text)}</li>`;
    }

    window.togglePreview=function(id){
        const preview=$(`preview-${id}`),btn=$(`toggle-${id}`);
        if(!preview)return;
        const isOpen=preview.classList.toggle('open');
        if(btn){btn.innerHTML=(isOpen?SVG.eyeoff+' Tutup':SVG.eye+' Lihat');btn.classList.toggle('open',isOpen);}
    };

    window.hapusSoal=function(id,nomor){
        showDialog({
            icon:'err', title:`Hapus Soal #${nomor}`,
            msg:'Gambar terkait juga akan terhapus permanen. Tindakan ini tidak bisa dibatalkan.',
            confirmText:'Hapus', confirmClass:'confirm-err',
            onConfirm:()=>doHapusSoal(id,nomor)
        });
    };

    window.doHapusSoal=async function(id,nomor){
        const card=$(`soal-${id}`);
        if(card){card.classList.add('deleting');await new Promise(r=>setTimeout(r,260));card.remove();}
        const prev=[...allSoal];
        allSoal=allSoal.filter(q=>(q._id||q.id)!==id&&(q._id||q.id)!=id);
        updateCounter();
        if(!allSoal.length){$('soalList').innerHTML=`<div class="empty-soal"><div class="empty-ico">${SVG.inbox}</div><div class="empty-el">Belum ada soal</div><div class="empty-es">Tambahkan soal lewat form di kiri.</div></div>`;}
        else{renumberCards();}
        try {
            const res=await fetch(`${API}/admin/quiz-questions/${id}`,{method:'DELETE',headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.ok){toast('Soal berhasil dihapus.');}
            else{allSoal=prev;renderSoal();toast('Gagal menghapus soal.','err');}
        } catch(_){allSoal=prev;renderSoal();toast('Koneksi bermasalah.','err');}
    };

    function renumberCards(){document.querySelectorAll('.soal-card .sc-num').forEach((el,i)=>{el.textContent=i+1;});}

    document.querySelectorAll('.type-tab').forEach(tab=>{
        tab.addEventListener('click',()=>{
            document.querySelectorAll('.type-tab').forEach(t=>t.classList.remove('active'));
            tab.classList.add('active');
            const type=tab.dataset.type;
            $('q_type').value=type;
            ['multiple_choice','true_false','multiple_answer'].forEach(t=>{const el=$(`sec_${t}`);if(el)el.classList.toggle('sec-hidden',t!==type);});
        });
    });

    window.setTF=function(choice){
        tfChoice=choice==='benar'?'A':'B';
        $('tf_ans').value=tfChoice;
        $('tf_benar').className='tf-btn'+(choice==='benar'?' sel-benar':'');
        $('tf_salah').className='tf-btn'+(choice==='salah'?' sel-salah':'');
    };

    window.setDiff=function(d){
        diffChoice=d; $('q_difficulty').value=d;
        document.querySelectorAll('.diff-pill').forEach(p=>p.classList.remove('active'));
        const map={mudah:'dm',sedang:'ds',sulit:'dd'};
        document.querySelectorAll('.'+map[d]).forEach(p=>p.classList.add('active'));
    };

    window.handleImgDrop=function(e){e.preventDefault();$('imgDrop').style.borderColor='';const f=e.dataTransfer.files[0];if(f)onImgSelected(f);};
    window.onImgSelected=function(file){
        if(!file)return;
        if(!['image/jpeg','image/png','image/jpg','image/webp'].includes(file.type)){toast('Format harus PNG, JPG, atau WEBP.','err');return;}
        if(file.size>2*1024*1024){toast('Ukuran gambar maks 2MB.','err');return;}
        selImg=file;
        const r=new FileReader();
        r.onload=ev=>{$('imgPreview').src=ev.target.result;$('imgPlaceholder').style.display='none';$('imgPreviewWrap').style.display='block';};
        r.readAsDataURL(file);
    };
    window.removeImg=function(){selImg=null;$('q_image').value='';$('imgPreview').src='';$('imgPlaceholder').style.display='block';$('imgPreviewWrap').style.display='none';};

    window.submitSoal=async function(){
        const type=$('q_type').value, text=$('q_text').value.trim();
        if(!text){toast('Pertanyaan wajib diisi.','err');$('q_text').focus();return;}
        const fd=new FormData();
        fd.append('question',text);fd.append('question_type',type);fd.append('points',$('q_points').value||10);fd.append('difficulty',$('q_difficulty').value);fd.append('explanation',$('q_explanation').value.trim());
        if(selImg)fd.append('image',selImg);
        if(type==='multiple_choice'){
            const opts=['a','b','c','d'].map(l=>({key:l,val:$(`opt_mc_${l}`).value.trim()}));
            if(opts.some(o=>!o.val)){toast('Semua 4 pilihan wajib diisi.','err');return;}
            const ans=document.querySelector('input[name="mc_ans"]:checked')?.value;
            if(!ans){toast('Pilih jawaban yang benar.','err');return;}
            opts.forEach(o=>fd.append(`option_${o.key}`,o.val));fd.append('correct_answer',ans);
        } else if(type==='true_false'){
            fd.append('correct_answer',$('tf_ans').value);
        } else if(type==='multiple_answer'){
            const opts=['a','b','c','d'].map(l=>({key:l,val:$(`opt_ma_${l}`).value.trim()}));
            if(opts.some(o=>!o.val)){toast('Semua 4 pilihan wajib diisi.','err');return;}
            const checked=['a','b','c','d'].filter(l=>$(`ma_chk_${l}`)?.checked).map(l=>l.toUpperCase());
            if(!checked.length){toast('Centang minimal 1 jawaban benar.','err');return;}
            opts.forEach(o=>fd.append(`option_${o.key}`,o.val));fd.append('correct_answers',JSON.stringify(checked));
        }
        const btn=$('btnSimpan'),lbl=$('btnLabel');
        btn.disabled=true;lbl.textContent='Menyimpan...';
        try {
            const res=await fetch(`${API}/admin/quizzes/${QUIZ}/questions`,{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json'},body:fd});
            const data=await res.json();
            if(res.ok){
                allSoal.unshift(data.data);renderSoal();resetForm();toast('Soal berhasil disimpan!');
                const fc=$('soalList').querySelector('.soal-card');if(fc)fc.scrollIntoView({behavior:'smooth',block:'nearest'});
            } else {
                let msg=data.message||'Gagal menyimpan.';if(data.errors)msg=Object.values(data.errors).flat()[0]||msg;toast(msg,'err');
            }
        } catch(e){toast('Koneksi bermasalah: '+e.message,'err');}
        finally{btn.disabled=false;lbl.textContent='Simpan Soal';}
    };

    function resetForm(){
        $('q_text').value='';$('q_explanation').value='';$('q_points').value=10;
        ['a','b','c','d'].forEach(l=>{const mc=$(`opt_mc_${l}`);if(mc)mc.value='';const ma=$(`opt_ma_${l}`);if(ma)ma.value='';const chk=$(`ma_chk_${l}`);if(chk)chk.checked=false;});
        const mcA=$('mc_a');if(mcA)mcA.checked=true;setTF('benar');removeImg();
    }

    /* AI Panel */
    let aiMode='topic', aiQuestions=[];
    window.toggleAiPanel=function(){
        const body=$('aiPanelBody'),btn=$('aiCollapseBtn');
        const isOpen=body.style.display!=='none';
        body.style.display=isOpen?'none':'';
        btn.classList.toggle('collapsed',isOpen);
    };
    window.setAiMode=function(mode){
        aiMode=mode;
        document.querySelectorAll('.ai-mode-tab').forEach(t=>t.classList.remove('active'));
        $('tab-'+mode).classList.add('active');
        $('aiInputTopic').style.display=mode==='topic'?'':'none';
        $('aiInputMaterial').style.display=mode==='material'?'':'none';
    };

    async function loadMaterials(){
        try {
            const qr  = await fetch(`${API}/admin/quizzes/${QUIZ}`, {headers:{Authorization:'Bearer '+token, Accept:'application/json'}});
            const quiz = (await qr.json()).data || {};
            // course_id bisa berupa string langsung atau nested object
            const cid = quiz.course_id || quiz.course?._id || quiz.course?.id;
            if (!cid) { console.warn('[loadMaterials] course_id tidak ditemukan', quiz); return; }

            const mr   = await fetch(`${API}/courses/${cid}/materials`, {headers:{Authorization:'Bearer '+token, Accept:'application/json'}});
            const data = await mr.json();
            const list = data.data || data || [];
            const sel  = $('aiMaterialId');

            // Clear existing options kecuali placeholder
            while (sel.options.length > 1) sel.remove(1);

            if (!list.length) {
                const o = document.createElement('option');
                o.disabled = true;
                o.textContent = '— Belum ada materi di matkul ini —';
                sel.appendChild(o);
                return;
            }

            list.forEach(m => {
                const o = document.createElement('option');
                o.value = m._id || m.id;
                o.textContent = m.title || '(tanpa judul)';
                sel.appendChild(o);
            });
        } catch(e) { console.warn('[loadMaterials] error:', e); }
    }
    loadMaterials();

    window.generateSoal=async function(){
        const mc=parseInt($('cntMC').value)||0,tf=parseInt($('cntTF').value)||0,ma=parseInt($('cntMA').value)||0;
        const total=mc+tf+ma;
        if(!total){toast('Jumlah soal minimal 1.','err');return;}
        if(total>20){toast('Maksimal 20 soal per generate.','err');return;}
        const body={counts:{multiple_choice:mc,true_false:tf,multiple_answer:ma},difficulty:$('aiDifficulty').value};
        if(aiMode==='topic'){const t=$('aiTopic').value.trim();if(!t){toast('Masukkan topik.','err');return;}body.topic=t;}
        else if(aiMode==='material'){const m=$('aiMaterialId').value;if(!m){toast('Pilih materi.','err');return;}body.material_id=m;}
        else body.quiz_id=QUIZ;
        const btn=$('btnGenerate');btn.disabled=true;
        try {
            const res=await fetch(`${API}/ai/generate-questions`,{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify(body)});
            const data=await res.json();
            if(!res.ok){toast(data.message||'Gagal generate.','err');return;}
            aiQuestions=data.questions||[];renderAiPreview();
            toast(`${aiQuestions.length} soal berhasil di-generate!`);
        } catch(_){toast('Koneksi bermasalah.','err');}
        finally{btn.disabled=false;}
    };

    function typeLabel(t){return{multiple_choice:'Pilihan Ganda',true_false:'Benar/Salah',multiple_answer:'Multi Jawaban'}[t]||t;}
    function typeBadgeClass(t){return{multiple_choice:'badge-mc',true_false:'badge-tf',multiple_answer:'badge-ma'}[t]||'badge-mc';}

    function renderAiPreview(){
        $('aiPreviewTitle').textContent=`${aiQuestions.length} soal berhasil di-generate`;
        $('aiPreview').style.display='';
        $('aiPreviewList').innerHTML=aiQuestions.map((q,i)=>{
            const opts=['a','b','c','d'].map(x=>{const v=q['option_'+x];if(!v)return'';const c=q.question_type==='multiple_answer'?(q.correct_answers||[]).includes(x.toUpperCase()):q.correct_answer===x.toUpperCase();return`<div class="ai-q-opt${c?' correct':''}"><strong>${x.toUpperCase()}.</strong> ${esc(v)}</div>`;}).join('');
            return`<div class="ai-q-card" id="aiq-${i}">
                <span class="ai-q-type-badge ${typeBadgeClass(q.question_type)}">${typeLabel(q.question_type)}</span>
                <div class="ai-q-text">${i+1}. ${esc(q.question)}</div>
                <div class="ai-q-opts">${opts}</div>
                ${q.explanation?`<div class="ai-q-explanation">${esc(q.explanation)}</div>`:''}
                <div class="ai-edit-area" id="editArea-${i}">
                    <textarea id="editQ-${i}" rows="2">${esc(q.question)}</textarea>
                    <input id="editOptA-${i}" placeholder="Opsi A" value="${esc(q.option_a)}">
                    <input id="editOptB-${i}" placeholder="Opsi B" value="${esc(q.option_b)}">
                    <input id="editOptC-${i}" placeholder="Opsi C" value="${esc(q.option_c||'')}">
                    <input id="editOptD-${i}" placeholder="Opsi D" value="${esc(q.option_d||'')}">
                    <input id="editExpl-${i}" placeholder="Penjelasan" value="${esc(q.explanation||'')}">
                    <button onclick="applyEdit(${i})" style="padding:5px 12px;background:#279685;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer">Terapkan Edit</button>
                </div>
                <div class="ai-q-actions">
                    <button class="btn-edit-q" onclick="toggleEditArea(${i})">Edit</button>
                    <button class="btn-accept-q" onclick="acceptSatu(${i})" id="acceptBtn-${i}">Simpan</button>
                    <button class="btn-reject-q" onclick="rejectSatu(${i})">Tolak</button>
                </div>
            </div>`;
        }).join('');
    }

    window.toggleEditArea=i=>{$(`editArea-${i}`).classList.toggle('open');};
    window.applyEdit=function(i){
        aiQuestions[i].question=$(`editQ-${i}`).value.trim()||aiQuestions[i].question;
        aiQuestions[i].option_a=$(`editOptA-${i}`).value.trim()||aiQuestions[i].option_a;
        aiQuestions[i].option_b=$(`editOptB-${i}`).value.trim()||aiQuestions[i].option_b;
        aiQuestions[i].option_c=$(`editOptC-${i}`).value.trim();
        aiQuestions[i].option_d=$(`editOptD-${i}`).value.trim();
        aiQuestions[i].explanation=$(`editExpl-${i}`).value.trim();
        $(`editArea-${i}`).classList.remove('open');renderAiPreview();toast('Edit diterapkan.');
    };
    window.rejectSatu=function(i){const c=$(`aiq-${i}`);if(c){c.style.opacity='.3';c.style.pointerEvents='none';}aiQuestions[i]._rejected=true;};
    window.acceptSatu=async function(i){
        const q=aiQuestions[i],btn=$(`acceptBtn-${i}`);
        if(!q||q._rejected)return;
        btn.disabled=true;btn.textContent='...';
        const ok=await simpanSatuSoalAI(q);
        if(ok){const c=$(`aiq-${i}`);if(c)c.classList.add('accepted');btn.textContent='Tersimpan';aiQuestions[i]._saved=true;toast('Soal disimpan!');await fetchSoal();}
        else{btn.disabled=false;btn.textContent='Simpan';}
    };
    window.acceptAllSoal=async function(){
        const toSave=aiQuestions.filter(q=>!q._saved&&!q._rejected);
        if(!toSave.length){toast('Semua soal sudah disimpan atau ditolak.');return;}
        const allBtn=document.querySelector('.btn-accept-all');allBtn.disabled=true;
        let saved=0;
        for(let i=0;i<aiQuestions.length;i++){
            const q=aiQuestions[i];if(q._saved||q._rejected)continue;
            const ok=await simpanSatuSoalAI(q);
            if(ok){q._saved=true;saved++;const c=$(`aiq-${i}`);if(c)c.classList.add('accepted');const b=$(`acceptBtn-${i}`);if(b)b.textContent='Tersimpan';}
        }
        allBtn.disabled=false;toast(`${saved} soal berhasil disimpan!`);if(saved>0)await fetchSoal();
    };
    async function simpanSatuSoalAI(q){
        try{const res=await fetch(`${API}/admin/quizzes/${QUIZ}/questions`,{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify({question:q.question,question_type:q.question_type,option_a:q.option_a,option_b:q.option_b,option_c:q.option_c||'',option_d:q.option_d||'',correct_answer:q.correct_answer,correct_answers:q.correct_answers||[],explanation:q.explanation||'',difficulty:q.difficulty||'sedang',points:q.points||10})});return res.ok;}
        catch(e){toast('Gagal menyimpan: '+e.message,'err');return false;}
    }

    Promise.all([loadQuizInfo(),fetchSoal()]);
    window.fetchSoal=fetchSoal;
})();
</script>
@endpush