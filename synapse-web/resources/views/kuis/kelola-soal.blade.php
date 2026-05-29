@extends('layouts.app')

@section('title')
Kelola Soal - Synapse
@endsection

@section('header_title')
Kelola Soal
@endsection

@section('content')
<style>
/* =========================================================
   KELOLA SOAL — modernized
   ========================================================= */

.back-link {
    display: inline-flex; align-items: center; gap: 7px;
    color: #888; font-size: 13px; font-weight: 600;
    text-decoration: none; margin-bottom: 18px; transition: color .15s;
}
.back-link:hover { color: #279685; }
.back-link:hover svg { transform: translateX(-3px); }
.back-link svg { transition: transform .15s; }

/* Quiz info banner */
.quiz-banner {
    background: linear-gradient(135deg, #279685, #1a6b5e);
    border-radius: 14px; padding: 16px 22px;
    margin-bottom: 22px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 14px; flex-wrap: wrap;
}
.qb-left  { flex: 1; min-width: 0; }
.qb-title { font-size: 15px; font-weight: 700; color: #fff; margin: 0 0 8px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.qb-chips { display: flex; gap: 7px; flex-wrap: wrap; }
.qb-chip  {
    background: rgba(255,255,255,.15); color: rgba(255,255,255,.9);
    font-size: 11px; font-weight: 600; padding: 3px 10px;
    border-radius: 99px; border: 1px solid rgba(255,255,255,.2);
}
.qb-right { text-align: right; flex-shrink: 0; }
.qb-count { font-size: 36px; font-weight: 700; color: #fff; line-height: 1; transition: all .3s; }
.qb-count-label { font-size: 11px; color: rgba(255,255,255,.65); }

/* Layout */
.soal-layout {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 20px; align-items: start;
}
@media (max-width: 920px) { .soal-layout { grid-template-columns: 1fr; } }

/* Cards */
.card { background: #fff; border-radius: 16px; border: 1px solid #eee; overflow: hidden; }

.card-header {
    display: flex; align-items: center; gap: 10px;
    padding: 15px 20px 12px; border-bottom: 1px solid #f0f0f0;
    position: sticky; top: 0; background: #fff; z-index: 5;
}
.ch-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}
.ch-icon.teal   { background: #e3faf8; }
.ch-icon.purple { background: #f0eeff; }
.card-header h3 { font-size: 13px; font-weight: 700; color: #1a1a1a; margin: 0 0 1px; }
.card-header p  { font-size: 11px; color: #aaa; margin: 0; }
.card-body { padding: 16px 20px; }

/* Type tabs */
.type-tabs {
    display: flex; gap: 4px; margin-bottom: 16px;
    padding: 4px; background: #f3f4f6; border-radius: 10px;
}
.type-tab {
    flex: 1; padding: 8px 4px; text-align: center; cursor: pointer;
    border-radius: 7px; font-size: 11px; font-weight: 700;
    color: #888; border: none; background: transparent;
    transition: all .15s; font-family: inherit;
}
.type-tab.active { background: #fff; color: #279685; box-shadow: 0 1px 4px rgba(0,0,0,.08); }

/* Form groups */
.fg { margin-bottom: 13px; }
.fg:last-child { margin-bottom: 0; }
.fg label {
    display: block; font-size: 11px; font-weight: 700;
    color: #555; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 5px;
}
.req { color: #ef4444; }
.opt { color: #bbb; font-weight: 400; text-transform: none; letter-spacing: 0; font-size: 10px; }
.form-row-2 { display: flex; gap: 10px; }
.form-row-2 .fg { flex: 1; min-width: 0; }

.fc {
    width: 100%; padding: 9px 11px;
    border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13px; font-family: inherit; color: #1a1a1a;
    background: #fff; box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.fc:focus { outline: none; border-color: #279685; box-shadow: 0 0 0 3px rgba(39,150,133,.1); }
.fc::placeholder { color: #ccc; }
textarea.fc { resize: vertical; min-height: 72px; }

/* Option rows */
.option-row {
    display: flex; align-items: center; gap: 8px; margin-bottom: 7px;
}
.opt-letter {
    width: 24px; height: 24px; border-radius: 50%;
    background: #f3f4f6; color: #888;
    font-size: 11px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .15s;
}
.opt-input {
    flex: 1; padding: 8px 10px;
    border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: 12px; font-family: inherit; background: #fff;
    box-sizing: border-box; transition: border-color .15s;
}
.opt-input:focus { outline: none; border-color: #279685; }
.opt-input:disabled { background: #f8fafa; color: #888; cursor: not-allowed; }
.opt-radio, .opt-check {
    width: 16px; height: 16px; accent-color: #279685;
    cursor: pointer; flex-shrink: 0;
}
/* Highlight option letter when radio/check selected */
.opt-radio:checked ~ .opt-letter,
.opt-check:checked ~ .opt-letter { background: #279685; color: #fff; }

.option-hint {
    font-size: 11px; color: #92400e; background: #fef3c7;
    padding: 7px 10px; border-radius: 7px; margin-top: 5px;
    border-left: 3px solid #f59e0b;
}

/* True/false row */
.tf-row { display: flex; gap: 8px; }
.tf-btn {
    flex: 1; padding: 10px; border: 1.5px solid #e5e7eb;
    border-radius: 9px; font-size: 12px; font-weight: 700;
    cursor: pointer; font-family: inherit; background: #fff; color: #888;
    transition: all .15s;
}
.tf-btn:hover { border-color: #279685; }
.tf-btn.sel-benar { background: #d1fae5; border-color: #279685; color: #065f46; }
.tf-btn.sel-salah { background: #fee2e2; border-color: #ef4444; color: #991b1b; }

/* Difficulty pills */
.diff-row { display: flex; gap: 6px; }
.diff-pill {
    flex: 1; padding: 7px; text-align: center;
    border: 1.5px solid #e5e7eb; border-radius: 8px;
    font-size: 11px; font-weight: 700; cursor: pointer;
    font-family: inherit; background: #fff; color: #888;
    transition: all .15s;
}
.diff-pill.dm.active { background: #d1fae5; border-color: #279685; color: #065f46; }
.diff-pill.ds.active { background: #fef3c7; border-color: #f59e0b; color: #92400e; }
.diff-pill.dd.active { background: #fee2e2; border-color: #ef4444; color: #991b1b; }

/* Image uploader */
.img-drop {
    border: 2px dashed #e5e7eb; border-radius: 10px;
    padding: 16px; text-align: center; cursor: pointer;
    background: #fafafa; transition: border-color .15s, background .15s;
    position: relative;
}
.img-drop:hover { border-color: #279685; background: #f0fdfb; }
.img-drop input { display: none; }
.img-drop-icon { font-size: 26px; margin-bottom: 4px; }
.img-drop-label { font-size: 12px; font-weight: 700; color: #555; margin-bottom: 2px; }
.img-drop-hint  { font-size: 10px; color: #bbb; }
.img-preview-wrap { position: relative; }
.img-preview-wrap img {
    width: 100%; border-radius: 8px; display: block;
    max-height: 160px; object-fit: cover;
}
.img-remove {
    position: absolute; top: 6px; right: 6px;
    background: rgba(0,0,0,.55); color: #fff; border: none;
    padding: 4px 9px; border-radius: 6px; font-size: 11px;
    cursor: pointer; font-weight: 700; font-family: inherit;
    transition: background .15s;
}
.img-remove:hover { background: rgba(239,68,68,.9); }

/* Submit button */
.btn-submit {
    width: 100%; padding: 11px;
    background: #279685; color: #fff; border: none;
    border-radius: 10px; font-size: 13px; font-weight: 700;
    cursor: pointer; transition: background .18s;
    font-family: inherit;
    display: flex; align-items: center; justify-content: center; gap: 7px;
}
.btn-submit:hover { background: #1f7a6c; }
.btn-submit:disabled { background: #9ca3af; cursor: not-allowed; }

/* Hidden section helper */
.sec-hidden { display: none !important; }

/* =========================================================
   DAFTAR SOAL (kanan)
   ========================================================= */

/* Soal list area */
.soal-list { padding: 14px 18px; display: flex; flex-direction: column; gap: 10px; }

/* Soal card */
.soal-card {
    border: 1px solid #eee; border-radius: 12px;
    background: #fff; overflow: hidden;
    transition: box-shadow .2s, opacity .25s, transform .25s;
}
.soal-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.06); }
/* Exiting state for optimistic delete */
.soal-card.deleting {
    opacity: 0; transform: translateX(20px) scale(.97);
    pointer-events: none;
}

/* Card header row (nomor + badges + actions) */
.sc-header {
    display: flex; align-items: center; gap: 8px;
    padding: 11px 14px; background: #fafafa;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer; user-select: none;
}
.sc-num {
    width: 24px; height: 24px; border-radius: 50%;
    background: #279685; color: #fff;
    font-size: 11px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sc-badges { display: flex; gap: 5px; flex-wrap: wrap; flex: 1; }
.sc-badge {
    font-size: 10px; font-weight: 700; padding: 2px 8px;
    border-radius: 99px;
}
.b-type   { background: #e3faf8; color: #0f6e56; }
.b-mudah  { background: #d1fae5; color: #065f46; }
.b-sedang { background: #fef3c7; color: #92400e; }
.b-sulit  { background: #fee2e2; color: #991b1b; }
.b-pts    { background: #f0eeff; color: #534ab7; }

.sc-actions { display: flex; gap: 5px; align-items: center; flex-shrink: 0; }
.sc-toggle {
    font-size: 11px; color: #bbb; padding: 4px 8px;
    border-radius: 6px; border: 1px solid #e5e7eb;
    background: #fff; cursor: pointer; font-family: inherit;
    transition: all .15s; font-weight: 600;
}
.sc-toggle:hover { border-color: #279685; color: #279685; }
.sc-toggle.open { background: #e3faf8; border-color: #c0ede8; color: #0f6e56; }
.btn-del-soal {
    display: flex; align-items: center; gap: 4px;
    padding: 5px 10px; border-radius: 7px;
    font-size: 11px; font-weight: 700;
    border: none; background: #fee2e2; color: #991b1b;
    cursor: pointer; font-family: inherit; transition: background .15s;
}
.btn-del-soal:hover { background: #fecaca; }

/* Card preview (collapsed by default, expand on click) */
.sc-preview {
    display: none; padding: 12px 14px;
    border-top: 1px solid #f5f5f5;
    animation: fadeIn .18s ease;
}
.sc-preview.open { display: block; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }

/* Question text */
.sc-qtext {
    font-size: 13px; font-weight: 700; color: #1a1a1a;
    margin-bottom: 10px; line-height: 1.5;
}
.sc-qimg {
    width: 100%; max-height: 180px; object-fit: cover;
    border-radius: 8px; margin-bottom: 10px;
    border: 1px solid #eee;
}

/* Options list */
.sc-opts { list-style: none; padding: 0; margin: 0 0 10px; display: flex; flex-direction: column; gap: 5px; }
.sc-opts li {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 11px; border-radius: 8px;
    font-size: 12px; color: #555;
    border: 1px solid #eee; background: #fafafa;
}
.sc-opts li.correct {
    border-color: #279685; background: #e3faf8;
    color: #0f6e56; font-weight: 700;
}
.sc-opts li .opt-dot {
    width: 18px; height: 18px; border-radius: 50%;
    background: #e5e7eb; color: #888;
    font-size: 9px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sc-opts li.correct .opt-dot { background: #279685; color: #fff; }

/* Explanation */
.sc-explanation {
    background: #f0fdfb; border-left: 3px solid #279685;
    border-radius: 6px; padding: 8px 12px;
    font-size: 12px; color: #0f6e56;
}

/* Skeleton */
.skeleton {
    background: linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 8px;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Empty state */
.empty-soal {
    text-align: center; padding: 44px 20px; color: #bbb;
}
.empty-soal .ei { font-size: 40px; margin-bottom: 10px; }
.empty-soal .el { font-size: 14px; font-weight: 700; color: #888; margin-bottom: 5px; }
.empty-soal .es { font-size: 12px; }

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

/* ── AI Generate Panel ─────────────────────────────── */
.ai-panel {
    background: linear-gradient(135deg, #e6f4f2 0%, #f0fdfb 100%);
    border: 1.5px solid #a7f3d0;
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 24px;
}
.ai-panel-header {
    display: flex; align-items: center; gap: 10px; margin-bottom: 16px;
}
.ai-panel-header h3 {
    font-size: 15px; font-weight: 700; color: #065f46; margin: 0;
}
.ai-badge {
    display: inline-flex; align-items: center; gap: 4px;
    background: #279685; color: #fff; padding: 2px 10px;
    border-radius: 99px; font-size: 11px; font-weight: 700;
}
.ai-mode-tabs {
    display: flex; gap: 0; margin-bottom: 16px;
    border: 1px solid #a7f3d0; border-radius: 10px; overflow: hidden;
}
.ai-mode-tab {
    flex: 1; padding: 8px 12px; text-align: center;
    font-size: 12px; font-weight: 600; cursor: pointer; border: none;
    background: transparent; color: #0f6e56;
    transition: background .15s;
    font-family: inherit;
}
.ai-mode-tab.active { background: #279685; color: #fff; }
.ai-input-row {
    display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px;
}
.ai-input-row input, .ai-input-row select {
    flex: 1; min-width: 160px; padding: 8px 12px;
    border: 1px solid #a7f3d0; border-radius: 8px;
    font-size: 13px; font-family: inherit; background: #fff;
    outline: none;
}
.ai-input-row input:focus, .ai-input-row select:focus {
    border-color: #279685;
}
.ai-counts-row {
    display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px;
}
.ai-count-box {
    flex: 1; min-width: 120px; background: #fff;
    border: 1px solid #a7f3d0; border-radius: 10px; padding: 10px 12px;
}
.ai-count-box label {
    display: block; font-size: 10px; font-weight: 700;
    color: #0f6e56; margin-bottom: 6px; text-transform: uppercase;
}
.ai-count-box input {
    width: 100%; padding: 6px 8px; border: 1px solid #e5e7eb;
    border-radius: 6px; font-size: 16px; font-weight: 700;
    text-align: center; font-family: inherit; box-sizing: border-box;
    outline: none;
}
.btn-generate {
    width: 100%; padding: 11px; border: none; border-radius: 10px;
    background: linear-gradient(135deg, #279685, #1a6b5e);
    color: #fff; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: opacity .15s;
    font-family: inherit;
}
.btn-generate:hover { opacity: .9; }
.btn-generate:disabled { opacity: .6; cursor: not-allowed; }

/* Preview soal yang di-generate */
.ai-preview { margin-top: 16px; }
.ai-preview-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 10px;
}
.ai-preview-header h4 { font-size: 13px; font-weight: 700; color: #065f46; margin: 0; }
.btn-accept-all {
    padding: 7px 16px; border: none; border-radius: 8px;
    background: #279685; color: #fff; font-size: 12px;
    font-weight: 700; cursor: pointer; font-family: inherit;
}
.ai-q-card {
    background: #fff; border: 1px solid #d1fae5;
    border-radius: 10px; padding: 14px 16px; margin-bottom: 10px;
    position: relative;
}
.ai-q-card.accepted { border-color: #279685; background: #f0fdfb; }
.ai-q-type-badge {
    display: inline-block; padding: 1px 8px; border-radius: 4px;
    font-size: 10px; font-weight: 700; margin-bottom: 8px;
}
.badge-mc  { background: #dbeafe; color: #1e40af; }
.badge-tf  { background: #fef3c7; color: #92400e; }
.badge-ma  { background: #ede9fe; color: #4c1d95; }
.ai-q-text { font-size: 13px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; }
.ai-q-opts { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; margin-bottom: 8px; }
.ai-q-opt  { font-size: 12px; color: #555; padding: 3px 6px; border-radius: 4px; }
.ai-q-opt.correct { background: #d1fae5; color: #065f46; font-weight: 700; }
.ai-q-explanation {
    font-size: 11px; color: #6b7280; font-style: italic;
    padding: 6px 8px; background: #f9fafb; border-radius: 6px;
    margin-bottom: 8px;
}
.ai-q-actions { display: flex; gap: 8px; }
.btn-edit-q {
    flex: 1; padding: 5px; border: 1px solid #e5e7eb;
    border-radius: 6px; background: #fff; font-size: 11px;
    font-weight: 600; cursor: pointer; font-family: inherit;
}
.btn-accept-q {
    flex: 1; padding: 5px; border: none; border-radius: 6px;
    background: #279685; color: #fff; font-size: 11px;
    font-weight: 700; cursor: pointer; font-family: inherit;
}
.btn-reject-q {
    padding: 5px 10px; border: 1px solid #fee2e2; border-radius: 6px;
    background: #fff; color: #ef4444; font-size: 11px;
    font-weight: 600; cursor: pointer; font-family: inherit;
}
.ai-edit-area {
    margin-bottom: 8px;
    display: none;
}
.ai-edit-area.open { display: block; }
.ai-edit-area textarea, .ai-edit-area input {
    width: 100%; padding: 6px 8px; border: 1px solid #e5e7eb;
    border-radius: 6px; font-size: 12px; font-family: inherit;
    box-sizing: border-box; margin-bottom: 4px; outline: none;
}
.ai-edit-area textarea { resize: vertical; min-height: 60px; }
</style>

{{-- Back link --}}
<a href="/kuis" class="back-link">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="m15 18-6-6 6-6"/>
    </svg>
    Kembali ke Daftar Kuis
</a>

{{-- Quiz banner --}}
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

{{-- ── AI Generate Panel ── --}}
<div class="ai-panel" id="aiPanel">
    <div class="ai-panel-header">
        <span style="font-size:20px;">✨</span>
        <h3>Generate Soal dengan AI</h3>
        <span class="ai-badge">⚡ Gemini AI</span>
        <button onclick="toggleAiPanel()"
            style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:18px;color:#0f6e56;">
            ▲
        </button>
    </div>

    <div id="aiPanelBody">
        {{-- Mode tabs --}}
        <div class="ai-mode-tabs">
            <button class="ai-mode-tab active" onclick="setAiMode('topic')" id="tab-topic">✏️ Dari Topik</button>
            <button class="ai-mode-tab" onclick="setAiMode('material')" id="tab-material">📄 Dari Materi</button>
            <button class="ai-mode-tab" onclick="setAiMode('quiz')" id="tab-quiz">📝 Dari Deskripsi Kuis</button>
        </div>

        {{-- Input konteks --}}
        <div class="ai-input-row" id="aiInputTopic">
            <input type="text" id="aiTopic"
                placeholder="Contoh: Jaringan komputer - protokol TCP/IP">
        </div>
        <div class="ai-input-row" id="aiInputMaterial" style="display:none;">
            <select id="aiMaterialId">
                <option value="">— Pilih materi —</option>
            </select>
        </div>

        {{-- Jumlah soal per tipe --}}
        <div class="ai-counts-row">
            <div class="ai-count-box">
                <label>🔵 Pilihan Ganda</label>
                <input type="number" id="cntMC" value="3" min="0" max="15">
            </div>
            <div class="ai-count-box">
                <label>🟡 Benar/Salah</label>
                <input type="number" id="cntTF" value="2" min="0" max="15">
            </div>
            <div class="ai-count-box">
                <label>🟣 Multi Jawaban</label>
                <input type="number" id="cntMA" value="1" min="0" max="15">
            </div>
            <div class="ai-count-box">
                <label>📊 Kesulitan</label>
                <select id="aiDifficulty"
                    style="width:100%;padding:6px 8px;border:1px solid #e5e7eb;border-radius:6px;font-size:13px;font-family:inherit;">
                    <option value="mudah">🟢 Mudah</option>
                    <option value="sedang" selected>🟡 Sedang</option>
                    <option value="sulit">🔴 Sulit</option>
                </select>
            </div>
        </div>

        <button class="btn-generate" id="btnGenerate" onclick="generateSoal()">
            ✨ Generate Soal Sekarang
        </button>

        {{-- Preview hasil --}}
        <div class="ai-preview" id="aiPreview" style="display:none;">
            <div class="ai-preview-header">
                <h4 id="aiPreviewTitle">— soal berhasil di-generate</h4>
                <button class="btn-accept-all" onclick="acceptAllSoal()">
                    ✓ Simpan Semua
                </button>
            </div>
            <div id="aiPreviewList"></div>
        </div>
    </div>
</div>

{{-- Layout --}}
<div class="soal-layout">

    {{-- ====== KOLOM KIRI: FORM TAMBAH SOAL ====== --}}
    <div class="card" style="position:sticky;top:20px;">
        <div class="card-header">
            <div class="ch-icon teal">➕</div>
            <div>
                <h3>Tambah Soal Baru</h3>
                <p>Isi detail soal lalu klik Simpan</p>
            </div>
        </div>
        <div class="card-body">

            {{-- Type tabs --}}
            <div class="type-tabs" id="typeTabs">
                <button type="button" class="type-tab active" data-type="multiple_choice">📝 Pilihan Ganda</button>
                <button type="button" class="type-tab" data-type="true_false">✓✗ True/False</button>
                <button type="button" class="type-tab" data-type="multiple_answer">☑ Multi Jawab</button>
            </div>

            <input type="hidden" id="q_type" value="multiple_choice">

            {{-- Pertanyaan --}}
            <div class="fg">
                <label>Pertanyaan <span class="req">*</span></label>
                <textarea id="q_text" class="fc" rows="3" placeholder="Tulis pertanyaan di sini..."></textarea>
            </div>

            {{-- Gambar opsional --}}
            <div class="fg">
                <label>Gambar <span class="opt">(opsional, maks 2MB)</span></label>
                <div class="img-drop" id="imgDrop" onclick="document.getElementById('q_image').click()"
                    ondragover="event.preventDefault();this.style.borderColor='#279685'"
                    ondragleave="this.style.borderColor=''"
                    ondrop="handleImgDrop(event)">
                    <input type="file" id="q_image" accept="image/jpeg,image/png,image/jpg,image/webp"
                        onchange="onImgSelected(this.files[0])">
                    <div id="imgPlaceholder">
                        <div class="img-drop-icon">🖼️</div>
                        <div class="img-drop-label">Klik atau seret gambar</div>
                        <div class="img-drop-hint">PNG · JPG · WEBP</div>
                    </div>
                    <div class="img-preview-wrap" id="imgPreviewWrap" style="display:none;">
                        <img id="imgPreview" src="" alt="preview">
                        <button type="button" class="img-remove"
                            onclick="event.stopPropagation();removeImg()">🗑 Hapus</button>
                    </div>
                </div>
            </div>

            {{-- Section: Multiple Choice --}}
            <div id="sec_multiple_choice">
                <div class="fg">
                    <label>Pilihan Jawaban <span class="req">*</span></label>
                    @foreach(['a','b','c','d'] as $l)
                    <div class="option-row">
                        <input type="radio" name="mc_ans" id="mc_{{ $l }}"
                            value="{{ strtoupper($l) }}" class="opt-radio"
                            {{ $l === 'a' ? 'checked' : '' }}>
                        <span class="opt-letter">{{ strtoupper($l) }}</span>
                        <input type="text" id="opt_mc_{{ $l }}" class="opt-input"
                            placeholder="Pilihan {{ strtoupper($l) }}">
                    </div>
                    @endforeach
                    <div class="option-hint">💡 Klik radio di kiri untuk tandai jawaban benar</div>
                </div>
            </div>

            {{-- Section: True/False --}}
            <div id="sec_true_false" class="sec-hidden">
                <div class="fg">
                    <label>Pilih Jawaban yang Benar <span class="req">*</span></label>
                    <div class="tf-row">
                        <button type="button" class="tf-btn sel-benar" id="tf_benar"
                            onclick="setTF('benar')">✅ Benar / True</button>
                        <button type="button" class="tf-btn" id="tf_salah"
                            onclick="setTF('salah')">❌ Salah / False</button>
                    </div>
                    <input type="hidden" id="tf_ans" value="A">
                </div>
            </div>

            {{-- Section: Multiple Answer --}}
            <div id="sec_multiple_answer" class="sec-hidden">
                <div class="fg">
                    <label>Pilihan Jawaban <span class="req">*</span> <span class="opt">(centang semua yang benar)</span></label>
                    @foreach(['a','b','c','d'] as $l)
                    <div class="option-row">
                        <input type="checkbox" id="ma_chk_{{ $l }}"
                            value="{{ strtoupper($l) }}" class="opt-check">
                        <span class="opt-letter">{{ strtoupper($l) }}</span>
                        <input type="text" id="opt_ma_{{ $l }}" class="opt-input"
                            placeholder="Pilihan {{ strtoupper($l) }}">
                    </div>
                    @endforeach
                    <div class="option-hint">💡 Bisa pilih lebih dari satu jawaban benar</div>
                </div>
            </div>

            {{-- Explanation --}}
            <div class="fg">
                <label>Penjelasan Jawaban <span class="opt">(opsional)</span></label>
                <textarea id="q_explanation" class="fc" rows="2"
                    placeholder="Ditampilkan ke mahasiswa setelah submit..."></textarea>
            </div>

            {{-- Points + Difficulty --}}
            <div class="form-row-2">
                <div class="fg">
                    <label>Bobot Poin</label>
                    <input type="number" id="q_points" class="fc" value="10" min="1" max="100">
                </div>
                <div class="fg">
                    <label>Kesulitan</label>
                    <div class="diff-row">
                        <button type="button" class="diff-pill dm" onclick="setDiff('mudah')">🟢 Mudah</button>
                        <button type="button" class="diff-pill ds active" onclick="setDiff('sedang')">🟡 Sedang</button>
                        <button type="button" class="diff-pill dd" onclick="setDiff('sulit')">🔴 Sulit</button>
                    </div>
                    <input type="hidden" id="q_difficulty" value="sedang">
                </div>
            </div>

            {{-- Submit --}}
            <div class="fg" style="margin-top:16px;">
                <button class="btn-submit" id="btnSimpan" type="button" onclick="submitSoal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 0 1 1.04-.208Z" clip-rule="evenodd"/></svg>
                    <span id="btnLabel">Simpan Soal</span>
                </button>
            </div>

        </div>
    </div>

    {{-- ====== KOLOM KANAN: DAFTAR SOAL ====== --}}
    <div class="card">
        <div class="card-header">
            <div class="ch-icon purple">📋</div>
            <div>
                <h3>Daftar Soal</h3>
                <p id="listSubtitle">Memuat...</p>
            </div>
        </div>

        <div id="soalList" class="soal-list">
            {{-- Skeleton --}}
            @for ($i = 0; $i < 4; $i++)
            <div style="border:1px solid #eee;border-radius:12px;overflow:hidden;">
                <div style="padding:11px 14px;background:#fafafa;display:flex;align-items:center;gap:8px;">
                    <div class="skeleton" style="width:24px;height:24px;border-radius:50%;flex-shrink:0;"></div>
                    <div class="skeleton" style="width:70px;height:18px;border-radius:99px;"></div>
                    <div class="skeleton" style="width:55px;height:18px;border-radius:99px;"></div>
                    <div style="margin-left:auto;display:flex;gap:5px;">
                        <div class="skeleton" style="width:50px;height:26px;border-radius:6px;"></div>
                        <div class="skeleton" style="width:55px;height:26px;border-radius:6px;"></div>
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

</div>

<div class="toast" id="toast"></div>

@endsection

@push('scripts')
<script>
(function () {
    /* ── Globals ─────────────────────────────────────────────── */
    const API   = window.apiBaseUrl;
    const token = window.token;
    const QUIZ  = '{{ $quiz_id ?? "" }}';

    if (!QUIZ) { alert('Quiz ID tidak valid'); window.location.href = '/kuis'; return; }

    const $ = id => document.getElementById(id);

    /* State */
    let allSoal   = [];      // array soal yang sudah di-fetch
    let selImg    = null;    // File gambar yang dipilih
    let tfChoice  = 'A';     // True/False choice
    let diffChoice= 'sedang';

    /* ── Toast ───────────────────────────────────────────────── */
    function toast(msg, type = 'ok') {
        const el = $('toast');
        el.textContent = (type === 'ok' ? '✓  ' : '✕  ') + msg;
        el.className   = 'toast ' + type + ' show';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3200);
    }

    /* ── Escape helper ───────────────────────────────────────── */
    function esc(s) {
        return String(s||'')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ═══════════════════════════════════════════════════════════
       QUIZ INFO BANNER
    ═══════════════════════════════════════════════════════════ */
    async function loadQuizInfo() {
        try {
            const res  = await fetch(`${API}/admin/quizzes/${QUIZ}`, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            const q = (await res.json()).data;
            if (!q) return;

            $('bannerTitle').textContent = q.title || 'Kuis';

            const statusMap = {
                aktif:'🟢 Aktif', nonaktif:'🔴 Nonaktif',
                belum_mulai:'🟡 Belum Mulai', sudah_selesai:'⚪ Selesai'
            };
            const chips = [
                q.course?.title ? `📚 ${esc(q.course.title)}` : null,
                `⏱ ${q.duration_minutes || 0} mnt`,
                `🎯 KKM: ${q.passing_score || 70}`,
                statusMap[q.status] || q.status,
            ].filter(Boolean);

            $('bannerChips').innerHTML = chips
                .map(c => `<span class="qb-chip">${c}</span>`)
                .join('');
        } catch (e) {
            $('bannerTitle').textContent = 'Kuis';
        }
    }

    /* ═══════════════════════════════════════════════════════════
       FETCH SOAL
    ═══════════════════════════════════════════════════════════ */
    async function fetchSoal() {
        try {
            const res  = await fetch(`${API}/admin/quizzes/${QUIZ}/questions`, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();
            allSoal = data.data || [];
            renderSoal();
        } catch (e) {
            $('soalList').innerHTML = `<div class="empty-soal">
                <div class="ei">⚠️</div>
                <div class="el">Gagal memuat soal</div>
                <div class="es">${esc(e.message)}</div>
                <button onclick="fetchSoal()" style="margin-top:12px;padding:7px 16px;background:#279685;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:12px;">Coba lagi</button>
            </div>`;
        }
    }

    /* Update counter di banner */
    function updateCounter() {
        $('soalCount').textContent = allSoal.length;
        $('listSubtitle').textContent = allSoal.length + ' soal tersimpan';
    }

    /* ═══════════════════════════════════════════════════════════
       RENDER DAFTAR SOAL
    ═══════════════════════════════════════════════════════════ */
    function renderSoal() {
        updateCounter();
        const list = $('soalList');

        if (allSoal.length === 0) {
            list.innerHTML = `<div class="empty-soal">
                <div class="ei">📝</div>
                <div class="el">Belum ada soal</div>
                <div class="es">Tambahkan soal pertama lewat form di kiri.</div>
            </div>`;
            return;
        }

        list.innerHTML = allSoal.map((q, idx) => buildCard(q, idx)).join('');
    }

    /* Build single soal card HTML */
    function buildCard(q, idx) {
        const id   = q._id || q.id;
        const type = q.question_type || 'multiple_choice';
        const diff = q.difficulty || 'sedang';
        const pts  = q.points || 10;

        const typeLabel = {
            multiple_choice: '📝 Pilihan Ganda',
            true_false:      '✓✗ True/False',
            multiple_answer: '☑ Multi Jawab',
        }[type] || type;

        const diffBadge = {
            mudah: 'b-mudah', sedang: 'b-sedang', sulit: 'b-sulit'
        }[diff] || 'b-sedang';

        const diffLabel = { mudah:'🟢 Mudah', sedang:'🟡 Sedang', sulit:'🔴 Sulit' }[diff] || diff;

        /* Build options list */
        let optsHtml = '<ul class="sc-opts">';
        if (type === 'true_false') {
            const c = (q.correct_answer || 'A').toUpperCase();
            optsHtml += optItem('A', 'Benar / True', c === 'A');
            optsHtml += optItem('B', 'Salah / False', c === 'B');
        } else if (type === 'multiple_answer') {
            const correctSet = (q.correct_answers || []).map(x => x.toUpperCase());
            ['A','B','C','D'].forEach(l => {
                const val = q['option_' + l.toLowerCase()];
                if (val) optsHtml += optItem(l, val, correctSet.includes(l));
            });
        } else {
            const c = (q.correct_answer || '').toUpperCase();
            ['A','B','C','D'].forEach(l => {
                const val = q['option_' + l.toLowerCase()];
                if (val) optsHtml += optItem(l, val, c === l);
            });
        }
        optsHtml += '</ul>';

        const imgHtml = q.image_url
            ? `<img class="sc-qimg" src="${esc(q.image_url)}" alt="soal-img" loading="lazy">`
            : '';

        const expHtml = q.explanation
            ? `<div class="sc-explanation">💡 ${esc(q.explanation)}</div>`
            : '';

        return `
        <div class="soal-card" id="soal-${id}">
            <div class="sc-header" onclick="togglePreview('${id}')">
                <span class="sc-num">${idx + 1}</span>
                <div class="sc-badges">
                    <span class="sc-badge b-type">${typeLabel}</span>
                    <span class="sc-badge ${diffBadge}">${diffLabel}</span>
                    <span class="sc-badge b-pts">⭐ ${pts} poin</span>
                </div>
                <div class="sc-actions" onclick="event.stopPropagation()">
                    <button class="sc-toggle" id="toggle-${id}" onclick="togglePreview('${id}')">
                        Lihat ▾
                    </button>
                    <button class="btn-del-soal" onclick="hapusSoal('${id}',${idx + 1})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/></svg>
                        Hapus
                    </button>
                </div>
            </div>
            <div class="sc-preview" id="preview-${id}">
                <div class="sc-qtext">${esc(q.question || '')}</div>
                ${imgHtml}
                ${optsHtml}
                ${expHtml}
            </div>
        </div>`;
    }

    function optItem(letter, text, correct) {
        return `<li class="${correct ? 'correct' : ''}">
            <div class="opt-dot">${letter}</div>
            ${esc(text)}
        </li>`;
    }

    /* ═══════════════════════════════════════════════════════════
       TOGGLE PREVIEW
    ═══════════════════════════════════════════════════════════ */
    window.togglePreview = function (id) {
        const preview = $(`preview-${id}`);
        const btn     = $(`toggle-${id}`);
        if (!preview) return;
        const isOpen = preview.classList.toggle('open');
        if (btn) {
            btn.textContent = isOpen ? 'Tutup ▴' : 'Lihat ▾';
            btn.classList.toggle('open', isOpen);
        }
    };

    /* ═══════════════════════════════════════════════════════════
       HAPUS SOAL — optimistic (langsung hilang dari DOM)
       FIX: Kode lama memanggil fetchQuestions() setelah delete
       → seluruh list re-render → ada delay & flash. 
       Solusi: hapus card dari DOM dulu (optimistic), lalu request.
       Kalau gagal, rollback dengan re-render dari allSoal[].
    ═══════════════════════════════════════════════════════════ */
    window.hapusSoal = async function (id, nomor) {
        if (!confirm(`Hapus soal #${nomor}?\n\nGambar terkait juga akan terhapus permanen.`)) return;

        const card = $(`soal-${id}`);
        if (!card) return;

        /* 1. Optimistic: animasi keluar + hapus dari DOM */
        card.classList.add('deleting');
        await new Promise(r => setTimeout(r, 260));   // tunggu animasi selesai
        card.remove();

        /* 2. Update local state + counter SEBELUM request selesai */
        const prevSoal = [...allSoal];
        allSoal = allSoal.filter(q => (q._id || q.id) !== id && (q._id || q.id) != id);
        updateCounter();

        /* 3. Kalau list kosong, tampilkan empty state */
        if (allSoal.length === 0) {
            $('soalList').innerHTML = `<div class="empty-soal">
                <div class="ei">📝</div>
                <div class="el">Belum ada soal</div>
                <div class="es">Tambahkan soal pertama lewat form di kiri.</div>
            </div>`;
        } else {
            /* Renumber semua nomor yang tersisa */
            renumberCards();
        }

        /* 4. Request ke server */
        try {
            const res = await fetch(`${API}/admin/quiz-questions/${id}`, {
                method: 'DELETE',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });

            if (res.ok) {
                toast('Soal berhasil dihapus.');
            } else {
                /* Rollback: kembalikan allSoal dan re-render */
                allSoal = prevSoal;
                renderSoal();
                toast('Gagal menghapus soal — perubahan dibatalkan.', 'err');
            }
        } catch (e) {
            allSoal = prevSoal;
            renderSoal();
            toast('Koneksi bermasalah — perubahan dibatalkan.', 'err');
        }
    };

    /* Renumber nomor lingkaran di semua soal card yang tersisa */
    function renumberCards() {
        document.querySelectorAll('.soal-card .sc-num').forEach((el, i) => {
            el.textContent = i + 1;
        });
    }

    /* ═══════════════════════════════════════════════════════════
       TYPE TABS
    ═══════════════════════════════════════════════════════════ */
    document.querySelectorAll('.type-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.type-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const type = tab.dataset.type;
            $('q_type').value = type;
            ['multiple_choice','true_false','multiple_answer'].forEach(t => {
                const el = $(`sec_${t}`);
                if (el) el.classList.toggle('sec-hidden', t !== type);
            });
        });
    });

    /* ═══════════════════════════════════════════════════════════
       TRUE/FALSE CHOICE
    ═══════════════════════════════════════════════════════════ */
    window.setTF = function (choice) {
        tfChoice = choice === 'benar' ? 'A' : 'B';
        $('tf_ans').value = tfChoice;
        $('tf_benar').className = 'tf-btn' + (choice === 'benar' ? ' sel-benar' : '');
        $('tf_salah').className = 'tf-btn' + (choice === 'salah' ? ' sel-salah' : '');
    };

    /* ═══════════════════════════════════════════════════════════
       DIFFICULTY PILLS
    ═══════════════════════════════════════════════════════════ */
    window.setDiff = function (d) {
        diffChoice = d;
        $('q_difficulty').value = d;
        document.querySelectorAll('.diff-pill').forEach(p => p.classList.remove('active'));
        const map = { mudah:'dm', sedang:'ds', sulit:'dd' };
        document.querySelectorAll('.' + map[d]).forEach(p => p.classList.add('active'));
    };

    /* ═══════════════════════════════════════════════════════════
       IMAGE UPLOADER
    ═══════════════════════════════════════════════════════════ */
    window.handleImgDrop = function (e) {
        e.preventDefault();
        $('imgDrop').style.borderColor = '';
        const file = e.dataTransfer.files[0];
        if (file) onImgSelected(file);
    };

    window.onImgSelected = function (file) {
        if (!file) return;
        if (!['image/jpeg','image/png','image/jpg','image/webp'].includes(file.type)) {
            toast('Format harus PNG, JPG, atau WEBP.', 'err'); return;
        }
        if (file.size > 2 * 1024 * 1024) {
            toast('Ukuran gambar maks 2MB.', 'err'); return;
        }
        selImg = file;
        const reader = new FileReader();
        reader.onload = ev => {
            $('imgPreview').src = ev.target.result;
            $('imgPlaceholder').style.display    = 'none';
            $('imgPreviewWrap').style.display     = 'block';
        };
        reader.readAsDataURL(file);
    };

    window.removeImg = function () {
        selImg = null;
        $('q_image').value = '';
        $('imgPreview').src = '';
        $('imgPlaceholder').style.display    = 'block';
        $('imgPreviewWrap').style.display     = 'none';
    };

    /* ═══════════════════════════════════════════════════════════
       SUBMIT SOAL — optimistic prepend ke daftar
    ═══════════════════════════════════════════════════════════ */
    window.submitSoal = async function () {
        const type = $('q_type').value;
        const text = $('q_text').value.trim();

        if (!text) { toast('Pertanyaan wajib diisi.', 'err'); $('q_text').focus(); return; }

        const fd = new FormData();
        fd.append('question',      text);
        fd.append('question_type', type);
        fd.append('points',        $('q_points').value || 10);
        fd.append('difficulty',    $('q_difficulty').value);
        fd.append('explanation',   $('q_explanation').value.trim());
        if (selImg) fd.append('image', selImg);

        /* Validasi & extract per tipe */
        if (type === 'multiple_choice') {
            const opts = ['a','b','c','d'].map(l => ({
                key: l, val: $(`opt_mc_${l}`).value.trim()
            }));
            if (opts.some(o => !o.val)) {
                toast('Semua 4 pilihan wajib diisi.', 'err'); return;
            }
            const ans = document.querySelector('input[name="mc_ans"]:checked')?.value;
            if (!ans) { toast('Pilih jawaban yang benar.', 'err'); return; }
            opts.forEach(o => fd.append(`option_${o.key}`, o.val));
            fd.append('correct_answer', ans);

        } else if (type === 'true_false') {
            fd.append('correct_answer', $('tf_ans').value);

        } else if (type === 'multiple_answer') {
            const opts = ['a','b','c','d'].map(l => ({
                key: l, val: $(`opt_ma_${l}`).value.trim()
            }));
            if (opts.some(o => !o.val)) {
                toast('Semua 4 pilihan wajib diisi.', 'err'); return;
            }
            const checked = ['a','b','c','d']
                .filter(l => $(`ma_chk_${l}`)?.checked)
                .map(l => l.toUpperCase());
            if (checked.length === 0) {
                toast('Centang minimal 1 jawaban benar.', 'err'); return;
            }
            opts.forEach(o => fd.append(`option_${o.key}`, o.val));
            fd.append('correct_answers', JSON.stringify(checked));
        }

        /* UI loading */
        const btn = $('btnSimpan'), lbl = $('btnLabel');
        btn.disabled = true; lbl.textContent = 'Menyimpan...';

        try {
            const res  = await fetch(`${API}/admin/quizzes/${QUIZ}/questions`, {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
                body: fd,
            });
            const data = await res.json();

            if (res.ok) {
                const newQ = data.data;

                /* Optimistic prepend — tidak perlu re-fetch */
                allSoal.unshift(newQ);
                renderSoal();
                resetForm();
                toast('Soal berhasil disimpan!');

                /* Scroll ke soal baru (atas list) */
                const firstCard = $('soalList').querySelector('.soal-card');
                if (firstCard) firstCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            } else {
                let msg = data.message || 'Gagal menyimpan soal.';
                if (data.errors) msg = Object.values(data.errors).flat()[0] || msg;
                toast(msg, 'err');
            }
        } catch (e) {
            toast('Koneksi bermasalah: ' + e.message, 'err');
        } finally {
            btn.disabled = false;
            lbl.textContent = 'Simpan Soal';
        }
    };

    /* ── Reset form setelah simpan ───────────────────────────── */
    function resetForm() {
        $('q_text').value        = '';
        $('q_explanation').value = '';
        $('q_points').value      = 10;
        ['a','b','c','d'].forEach(l => {
            const mc = $(`opt_mc_${l}`); if (mc) mc.value = '';
            const ma = $(`opt_ma_${l}`); if (ma) ma.value = '';
            const chk= $(`ma_chk_${l}`);if (chk) chk.checked = false;
        });
        /* Reset MC radio ke A */
        const mcA = $('mc_a'); if (mcA) mcA.checked = true;
        /* Reset TF ke benar */
        setTF('benar');
        /* Reset image */
        removeImg();
        /* Tetap di tipe dan difficulty yang sama */
    }

    /* ═══════════════════════════════════════════════════════
    AI GENERATE SOAL
    ═══════════════════════════════════════════════════════ */
    let aiMode        = 'topic';
    let aiQuestions   = [];   // soal hasil generate
    let acceptedCount = 0;

    // Toggle panel
    window.toggleAiPanel = function() {
        const body = $('aiPanelBody');
        const btn  = document.querySelector('#aiPanel button[onclick="toggleAiPanel()"]');
        const isOpen = body.style.display !== 'none';
        body.style.display = isOpen ? 'none' : '';
        btn.textContent = isOpen ? '▼' : '▲';
    };

    // Set mode konteks
    window.setAiMode = function(mode) {
        aiMode = mode;
        document.querySelectorAll('.ai-mode-tab').forEach(t => t.classList.remove('active'));
        $('tab-' + mode).classList.add('active');
        $('aiInputTopic').style.display    = mode === 'topic'    ? '' : 'none';
        $('aiInputMaterial').style.display = mode === 'material' ? '' : 'none';
        // mode 'quiz' tidak perlu input tambahan — pakai QUIZ id yang sudah ada di halaman
    };

    // Load daftar materi untuk dropdown
    async function loadMaterials() {
        try {
            // Ambil dari course yang sama dengan quiz ini
            const quizRes  = await fetch(`${API}/admin/quizzes/${QUIZ}`, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            const quizData = await quizRes.json();
            const courseId = quizData.data?.course_id ?? quizData.data?.course?.id;
            if (!courseId) return;

            const res  = await fetch(`${API}/courses/${courseId}/materials`, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            const data = await res.json();
            const list = data.data || data;
            const sel  = $('aiMaterialId');
            list.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m._id || m.id;
                opt.textContent = m.title;
                sel.appendChild(opt);
            });
        } catch (e) { console.warn('loadMaterials:', e); }
    }
    loadMaterials();

    // Generate soal
    window.generateSoal = async function() {
        const mcCount = parseInt($('cntMC').value) || 0;
        const tfCount = parseInt($('cntTF').value) || 0;
        const maCount = parseInt($('cntMA').value) || 0;
        const total   = mcCount + tfCount + maCount;

        if (total === 0) { toast('Jumlah soal minimal 1.', 'err'); return; }
        if (total > 20)  { toast('Maksimal 20 soal per generate.', 'err'); return; }

        const body = {
            counts: {
                multiple_choice: mcCount,
                true_false:      tfCount,
                multiple_answer: maCount,
            },
            difficulty: $('aiDifficulty').value,
        };

        if (aiMode === 'topic') {
            const topic = $('aiTopic').value.trim();
            if (!topic) { toast('Masukkan topik terlebih dahulu.', 'err'); return; }
            body.topic = topic;
        } else if (aiMode === 'material') {
            const matId = $('aiMaterialId').value;
            if (!matId) { toast('Pilih materi terlebih dahulu.', 'err'); return; }
            body.material_id = matId;
        } else {
            body.quiz_id = QUIZ;
        }

        const btn = $('btnGenerate');
        btn.disabled = true;
        btn.textContent = '⏳ Generating...';

        try {
            const res  = await fetch(`${API}/ai/generate-questions`, {
                method: 'POST',
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept:        'application/json',
                    'Content-Type':'application/json',
                },
                body: JSON.stringify(body),
            });
            const data = await res.json();

            if (!res.ok) {
                toast(data.message || 'Gagal generate soal.', 'err'); return;
            }

            aiQuestions = data.questions || [];
            renderAiPreview();
            toast(`${aiQuestions.length} soal berhasil di-generate! Review sebelum disimpan.`);

        } catch (e) {
            toast('Koneksi bermasalah.', 'err');
        } finally {
            btn.disabled = false;
            btn.textContent = '✨ Generate Soal Sekarang';
        }
    };

    function typeLabel(t) {
        return { multiple_choice:'Pilihan Ganda', true_false:'Benar/Salah', multiple_answer:'Multi Jawaban' }[t] || t;
    }
    function typeBadgeClass(t) {
        return { multiple_choice:'badge-mc', true_false:'badge-tf', multiple_answer:'badge-ma' }[t] || 'badge-mc';
    }

    function renderAiPreview() {
        const list = $('aiPreviewList');
        $('aiPreviewTitle').textContent = `${aiQuestions.length} soal berhasil di-generate`;
        $('aiPreview').style.display = '';

        list.innerHTML = aiQuestions.map((q, i) => {
            const opts = ['a','b','c','d'].map(x => {
                const val = q['option_' + x];
                if (!val) return '';
                const isCorrect = q.question_type === 'multiple_answer'
                    ? (q.correct_answers || []).includes(x.toUpperCase())
                    : q.correct_answer === x.toUpperCase();
                return `<div class="ai-q-opt${isCorrect ? ' correct' : ''}">
                    <strong>${x.toUpperCase()}.</strong> ${esc(val)}
                </div>`;
            }).join('');

            return `<div class="ai-q-card" id="aiq-${i}">
                <span class="ai-q-type-badge ${typeBadgeClass(q.question_type)}">${typeLabel(q.question_type)}</span>
                <div class="ai-q-text">${i+1}. ${esc(q.question)}</div>
                <div class="ai-q-opts">${opts}</div>
                ${q.explanation ? `<div class="ai-q-explanation">💡 ${esc(q.explanation)}</div>` : ''}

                {{-- Edit area --}}
                <div class="ai-edit-area" id="editArea-${i}">
                    <textarea id="editQ-${i}" rows="2">${esc(q.question)}</textarea>
                    <input id="editOptA-${i}" placeholder="Opsi A" value="${esc(q.option_a)}">
                    <input id="editOptB-${i}" placeholder="Opsi B" value="${esc(q.option_b)}">
                    <input id="editOptC-${i}" placeholder="Opsi C" value="${esc(q.option_c || '')}">
                    <input id="editOptD-${i}" placeholder="Opsi D" value="${esc(q.option_d || '')}">
                    <input id="editExpl-${i}" placeholder="Penjelasan" value="${esc(q.explanation || '')}">
                    <button onclick="applyEdit(${i})"
                        style="padding:5px 12px;background:#279685;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;">
                        ✓ Terapkan Edit
                    </button>
                </div>

                <div class="ai-q-actions">
                    <button class="btn-edit-q" onclick="toggleEditArea(${i})">✏️ Edit</button>
                    <button class="btn-accept-q" onclick="acceptSatu(${i})" id="acceptBtn-${i}">✓ Simpan</button>
                    <button class="btn-reject-q" onclick="rejectSatu(${i})">✕</button>
                </div>
            </div>`;
        }).join('');
    }

    window.toggleEditArea = function(i) {
        const area = $(`editArea-${i}`);
        area.classList.toggle('open');
    };

    window.applyEdit = function(i) {
        aiQuestions[i].question  = $(`editQ-${i}`).value.trim()    || aiQuestions[i].question;
        aiQuestions[i].option_a  = $(`editOptA-${i}`).value.trim() || aiQuestions[i].option_a;
        aiQuestions[i].option_b  = $(`editOptB-${i}`).value.trim() || aiQuestions[i].option_b;
        aiQuestions[i].option_c  = $(`editOptC-${i}`).value.trim();
        aiQuestions[i].option_d  = $(`editOptD-${i}`).value.trim();
        aiQuestions[i].explanation = $(`editExpl-${i}`).value.trim();
        $(`editArea-${i}`).classList.remove('open');
        renderAiPreview();
        toast('Edit diterapkan.');
    };

    window.rejectSatu = function(i) {
        const card = $(`aiq-${i}`);
        if (card) { card.style.opacity = '0.3'; card.style.pointerEvents = 'none'; }
        aiQuestions[i]._rejected = true;
    };

    window.acceptSatu = async function(i) {
        const q   = aiQuestions[i];
        const btn = $(`acceptBtn-${i}`);
        if (!q || q._rejected) return;

        btn.disabled    = true;
        btn.textContent = '⏳';

        const ok = await simpanSatuSoalAI(q);
        if (ok) {
            const card = $(`aiq-${i}`);
            if (card) card.classList.add('accepted');
            btn.textContent = '✓ Tersimpan';
            aiQuestions[i]._saved = true;
            toast('Soal disimpan!');
            await fetchSoal();
        } else {
            btn.disabled    = false;
            btn.textContent = '✓ Simpan';
        }
    };

    window.acceptAllSoal = async function() {
        const toSave = aiQuestions.filter(q => !q._saved && !q._rejected);
        if (toSave.length === 0) { toast('Semua soal sudah disimpan atau ditolak.'); return; }

        const allBtn = document.querySelector('.btn-accept-all');
        allBtn.disabled    = true;
        allBtn.textContent = `⏳ Menyimpan ${toSave.length} soal...`;

        let saved = 0;
        for (let i = 0; i < aiQuestions.length; i++) {
            const q = aiQuestions[i];
            if (q._saved || q._rejected) continue;
            const ok = await simpanSatuSoalAI(q);
            if (ok) {
                q._saved = true;
                saved++;
                const card = $(`aiq-${i}`);
                if (card) card.classList.add('accepted');
                const btn = $(`acceptBtn-${i}`);
                if (btn) btn.textContent = '✓ Tersimpan';
            }
        }

        allBtn.disabled    = false;
        allBtn.textContent = '✓ Simpan Semua';
        toast(`${saved} soal berhasil disimpan!`);
        if (saved > 0) await fetchSoal();
    };

    async function simpanSatuSoalAI(q) {
        try {
            const res = await fetch(`${API}/admin/quizzes/${QUIZ}/questions`, {
                method: 'POST',
                headers: {
                    Authorization:  'Bearer ' + token,
                    Accept:         'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    question:        q.question,
                    question_type:   q.question_type,
                    option_a:        q.option_a,
                    option_b:        q.option_b,
                    option_c:        q.option_c || '',
                    option_d:        q.option_d || '',
                    correct_answer:  q.correct_answer,
                    correct_answers: q.correct_answers || [],
                    explanation:     q.explanation || '',
                    difficulty:      q.difficulty || 'sedang',
                    points:          q.points || 10,
                }),
            });
            return res.ok;
        } catch (e) {
            toast('Gagal menyimpan soal: ' + e.message, 'err');
            return false;
        }
    }

    /* ── Init ────────────────────────────────────────────────── */
    Promise.all([loadQuizInfo(), fetchSoal()]);

    // Expose untuk tombol retry
    window.fetchSoal = fetchSoal;
})();
</script>
@endpush