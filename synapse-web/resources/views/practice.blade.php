@extends('layouts.app')
@section('title','Kelola Latihan Soal - Synapse')
@section('header_title','Kelola Latihan')

@section('content')
<style>
.back-link { display:inline-flex; align-items:center; gap:7px; color:#9ca3af; font-size:13px; font-weight:600; text-decoration:none; margin-bottom:20px; transition:color .15s; }
.back-link:hover { color:#279685; }
.back-link svg { width:15px; height:15px; transition:transform .15s; }
.back-link:hover svg { transform:translateX(-3px); }

.page-header { margin-bottom:24px; }
.page-header h2 { font-size:20px; font-weight:800; color:#111827; margin:0 0 4px; letter-spacing:-.3px; }
.page-header .matkul-name { color:#279685; font-weight:700; }

/* ── AI Panel ──────────────────────────────────────────────── */
.ai-panel {
    background:linear-gradient(135deg,#e6f4f2,#f0fdfb);
    border:1.5px solid #a7f3d0; border-radius:16px;
    padding:20px 24px; margin-bottom:24px;
}
.ai-panel-header { display:flex; align-items:center; gap:10px; margin-bottom:0; }
.ai-panel-header h3 { font-size:15px; font-weight:700; color:#065f46; margin:0; }
.ai-badge { display:inline-flex; align-items:center; gap:5px; background:#279685; color:#fff; padding:2px 10px; border-radius:99px; font-size:11px; font-weight:700; }
.ai-badge svg { width:12px; height:12px; }
.ai-collapse { margin-left:auto; background:none; border:none; cursor:pointer; color:#0f6e56; display:flex; align-items:center; padding:4px 8px; border-radius:7px; transition:background .15s; }
.ai-collapse:hover { background:rgba(39,150,133,.1); }
.ai-collapse svg { width:16px; height:16px; transition:transform .2s; }
.ai-collapse.collapsed svg { transform:rotate(180deg); }

.ai-body { margin-top:16px; }

/* Jumlah soal */
.ai-counts-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
.ai-count-box { flex:1; min-width:100px; background:#fff; border:1px solid #a7f3d0; border-radius:10px; padding:10px 12px; }
.ai-count-box label { display:block; font-size:10px; font-weight:700; color:#0f6e56; margin-bottom:6px; text-transform:uppercase; letter-spacing:.05em; }
.ai-count-box input { width:100%; padding:6px 8px; border:1.5px solid #e5e7eb; border-radius:6px; font-size:18px; font-weight:800; text-align:center; font-family:inherit; box-sizing:border-box; outline:none; color:#111827; }
.ai-count-box input:focus { border-color:#279685; }
.ai-count-box select { width:100%; padding:6px 8px; border:1.5px solid #e5e7eb; border-radius:6px; font-size:13px; font-family:inherit; box-sizing:border-box; outline:none; cursor:pointer; }
.ai-count-box select:focus { border-color:#279685; }

.btn-generate { width:100%; padding:12px; border:none; border-radius:10px; background:linear-gradient(135deg,#279685,#1a6b5e); color:#fff; font-size:14px; font-weight:700; cursor:pointer; transition:opacity .15s; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:16px; }
.btn-generate svg { width:16px; height:16px; }
.btn-generate:hover { opacity:.9; }
.btn-generate:disabled { opacity:.6; cursor:not-allowed; }

/* Preview soal AI */
.ai-preview { display:none; }
.ai-preview.open { display:block; }
.ai-preview-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.ai-preview-header h4 { font-size:13px; font-weight:700; color:#065f46; margin:0; }
.btn-save-all { display:inline-flex; align-items:center; gap:6px; padding:7px 16px; border:none; border-radius:8px; background:#279685; color:#fff; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; }
.btn-save-all svg { width:13px; height:13px; }

.ai-q-card { background:#fff; border:1.5px solid #d1fae5; border-radius:11px; padding:14px 16px; margin-bottom:10px; transition:border-color .2s; }
.ai-q-card.saved { border-color:#279685; background:#f0fdf9; opacity:.75; }
.ai-q-card.rejected { opacity:.35; pointer-events:none; }
.ai-q-num { font-size:11px; font-weight:700; color:#279685; margin-bottom:6px; }
.ai-q-text { font-size:13px; font-weight:600; color:#111827; margin-bottom:10px; line-height:1.45; }
.ai-q-opts { display:flex; flex-direction:column; gap:5px; margin-bottom:10px; }
.ai-q-opt { display:flex; align-items:center; gap:8px; padding:6px 10px; border-radius:8px; font-size:12px; color:#6b7280; background:#fafafa; border:1px solid #f0f0f0; }
.ai-q-opt .ol { width:20px; height:20px; border-radius:50%; background:#e5e7eb; color:#6b7280; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ai-q-opt.correct { background:#f0fdf9; border-color:#279685; color:#0f6e56; font-weight:600; }
.ai-q-opt.correct .ol { background:#279685; color:#fff; }
.ai-q-expl { font-size:11px; color:#6b7280; font-style:italic; background:#f9fafb; border-radius:6px; padding:6px 9px; margin-bottom:10px; }
.ai-q-actions { display:flex; gap:7px; }
.btn-qa-save   { flex:1; padding:6px; border:none; border-radius:7px; background:#279685; color:#fff; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; }
.btn-qa-reject { padding:6px 12px; border:1.5px solid #fecaca; border-radius:7px; background:#fff; color:#dc2626; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; }

/* ── Layout ────────────────────────────────────────────────── */
.layout-container { display:flex; gap:20px; flex-wrap:wrap; align-items:flex-start; }
.col-kiri  { flex:1; min-width:300px; max-width:400px; position:sticky; top:20px; }
.col-kanan { flex:2; min-width:340px; }

/* Card */
.card { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }
.card-header { display:flex; align-items:center; gap:10px; padding:16px 20px 13px; border-bottom:1px solid #f5f6f8; }
.ch-ico { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ch-ico svg { width:16px; height:16px; }
.ch-ico.teal   { background:rgba(39,150,133,.1); color:#279685; }
.ch-ico.purple { background:rgba(124,58,237,.1);  color:#7c3aed; }
.card-header h3 { font-size:13px; font-weight:700; color:#111827; margin:0 0 1px; }
.card-header p  { font-size:11px; color:#9ca3af; margin:0; }
.card-body { padding:16px 20px; }

/* Form */
.fg { margin-bottom:14px; }
.fg:last-child { margin-bottom:0; }
.fg label { display:block; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:5px; }
.req { color:#ef4444; }
.fc { width:100%; padding:9px 12px; border:1.5px solid #e5e7eb; border-radius:9px; font-size:13px; font-family:inherit; color:#111827; background:#fff; box-sizing:border-box; transition:border-color .15s,box-shadow .15s; }
.fc:focus { outline:none; border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.1); }
.fc::placeholder { color:#d1d5db; }
textarea.fc { resize:vertical; min-height:80px; }
select.fc { cursor:pointer; }

.btn-simpan { width:100%; padding:11px; background:#279685; color:#fff; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:background .15s; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:7px; margin-top:16px; }
.btn-simpan:hover { background:#1c6e60; }
.btn-simpan:disabled { background:#9ca3af; cursor:not-allowed; }
.btn-simpan svg { width:14px; height:14px; }

/* Soal list */
.soal-list { padding:14px 16px; display:flex; flex-direction:column; gap:10px; }
.soal-item { border:1px solid #eff0f2; background:#fafafa; border-radius:11px; padding:16px; position:relative; transition:box-shadow .2s; }
.soal-item:hover { border-color:#e5e7eb; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.soal-teks { font-weight:700; color:#111827; margin:0 0 12px; font-size:14px; padding-right:56px; line-height:1.4; }
.opsi-list { list-style:none; padding:0; margin:0; font-size:13px; display:flex; flex-direction:column; gap:5px; }
.opsi-list li { padding:6px 10px; border-radius:7px; background:#fff; border:1px solid #eff0f2; color:#6b7280; display:flex; align-items:center; gap:7px; }
.opsi-list li .opt-letter { width:20px; height:20px; border-radius:50%; background:#f5f6f8; color:#9ca3af; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.opsi-benar { border-color:#279685 !important; background:#f0fdf9 !important; color:#0f6e56 !important; font-weight:600; }
.opsi-benar .opt-letter { background:#279685 !important; color:#fff !important; }

.btn-hapus { position:absolute; top:13px; right:13px; background:#fef2f2; border:1.5px solid #fecaca; color:#dc2626; padding:4px 9px; border-radius:7px; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; display:flex; align-items:center; gap:4px; transition:background .15s; }
.btn-hapus svg { width:11px; height:11px; }
.btn-hapus:hover { background:#fee2e2; }

/* Empty */
.empty-state { text-align:center; padding:48px 20px; }
.empty-ico { width:52px; height:52px; border-radius:14px; background:#f5f6f8; display:flex; align-items:center; justify-content:justify-content:center; margin:0 auto 14px; }
.empty-ico { align-items:center; justify-content:center; }
.empty-ico svg { width:26px; height:26px; color:#d1d5db; }
.empty-el { font-size:13px; font-weight:700; color:#374151; margin-bottom:5px; }
.empty-es { font-size:12px; color:#9ca3af; }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.2s infinite; border-radius:6px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
@keyframes spin { to{transform:rotate(360deg)} }
</style>

<a href="/mata-kuliah/{{ $course_id }}/materi" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Kembali ke Materi
</a>

<div class="page-header">
    <h2>Latihan: <span class="matkul-name" id="judulMateri">Memuat...</span></h2>
</div>

{{-- ── AI GENERATE PANEL ── --}}
<div class="ai-panel" id="aiPanel">
    <div class="ai-panel-header">
        <div style="width:34px;height:34px;border-radius:9px;background:#279685;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        </div>
        <h3>Generate Soal Otomatis</h3>
        <!-- <span class="ai-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
            Gemini AI
        </span> -->
        <button class="ai-collapse" id="aiCollapseBtn" onclick="toggleAiPanel()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
        </button>
    </div>

    <div class="ai-body" id="aiBody">
        <p style="font-size:12px;color:#0f6e56;margin:10px 0 14px;background:rgba(39,150,133,.08);padding:8px 12px;border-radius:8px;display:flex;align-items:center;gap:6px">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Synapse akan membaca isi e-modul materi ini dan membuat soal latihan yang relevan secara otomatis.
        </p>

        <div class="ai-counts-row">
            <div class="ai-count-box">
                <label>Pilihan Ganda</label>
                <input type="number" id="cntMC" value="3" min="0" max="10">
            </div>
            <div class="ai-count-box">
                <label>Benar / Salah</label>
                <input type="number" id="cntTF" value="2" min="0" max="10">
            </div>
            <div class="ai-count-box">
                <label>Kesulitan</label>
                <select id="aiDiff">
                    <option value="mudah">Mudah</option>
                    <option value="sedang" selected>Sedang</option>
                    <option value="sulit">Sulit</option>
                </select>
            </div>
        </div>

        <button class="btn-generate" id="btnGenerate" onclick="generateAI()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Generate Soal dari Materi Ini
        </button>

        <div class="ai-preview" id="aiPreview">
            <div class="ai-preview-header">
                <h4 id="aiPreviewTitle">—</h4>
                <button class="btn-save-all" onclick="saveAllAI()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Simpan Semua
                </button>
            </div>
            <div id="aiPreviewList"></div>
        </div>
    </div>
</div>

{{-- ── LAYOUT MANUAL ── --}}
<div class="layout-container">
    <div class="col-kiri">
        <div class="card">
            <div class="card-header">
                <div class="ch-ico teal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
                <div><h3>Tambah Manual</h3><p>Pilihan ganda 4 opsi</p></div>
            </div>
            <div class="card-body">
                <form id="formTambahSoal">
                    <div class="fg">
                        <label>Pertanyaan <span class="req">*</span></label>
                        <textarea id="question_text" class="fc" rows="3" placeholder="Tulis pertanyaan..." required></textarea>
                    </div>
                    @foreach(['a','b','c','d'] as $l)
                    <div class="fg">
                        <label>Opsi {{ strtoupper($l) }} <span class="req">*</span></label>
                        <input type="text" id="option_{{ $l }}" class="fc" placeholder="Pilihan {{ strtoupper($l) }}" required>
                    </div>
                    @endforeach
                    <div class="fg">
                        <label>Kunci Jawaban <span class="req">*</span></label>
                        <select id="correct_answer" class="fc" required>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-simpan" id="btnSimpanSoal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Simpan Soal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-kanan">
        <div class="card">
            <div class="card-header">
                <div class="ch-ico purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <div><h3>Daftar Soal</h3><p id="listSubtitle">Memuat...</p></div>
            </div>
            <div id="daftarSoalContainer" class="soal-list">
                <div class="empty-state">
                    <div class="empty-ico" style="display:flex;align-items:center;justify-content:center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="empty-el">Memuat data soal...</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const token=window.token||localStorage.getItem('token');
    if(!token){window.location.href='/';return;}

    const materialId="{{ $materi_id }}";
    if(!materialId){toast('Error: ID Materi tidak valid.','err');return;}

    const API=window.apiBaseUrl;
    const baseUrl=`${API}/materials/${materialId}/questions`;

    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

    const SVG={
        check:`<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>`,
        trash:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`,
        inbox:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>`,
        spin:`<div style="width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0"></div>`,
    };

    // Load judul materi
    fetch(`${API}/materials/${materialId}`,{headers:{'Authorization':'Bearer '+token,'Accept':'application/json'}})
        .then(r=>r.json()).then(d=>{const m=d.data||d;if(m.title)document.getElementById('judulMateri').textContent=m.title;}).catch(_=>{});

    // ── AI Panel collapse ─────────────────────────────────────
    window.toggleAiPanel=function(){
        const body=document.getElementById('aiBody');
        const btn=document.getElementById('aiCollapseBtn');
        const open=body.style.display!=='none';
        body.style.display=open?'none':'block';
        btn.classList.toggle('collapsed',open);
    };

    // ── AI Generate ───────────────────────────────────────────
    let aiQuestions=[];

    window.generateAI=async function(){
        const mc=parseInt(document.getElementById('cntMC').value)||0;
        const tf=parseInt(document.getElementById('cntTF').value)||0;
        const diff=document.getElementById('aiDiff').value;
        const total=mc+tf;

        if(total===0){toast('Jumlah soal minimal 1.','err');return;}
        if(total>15){toast('Maksimal 15 soal per generate.','err');return;}

        const btn=document.getElementById('btnGenerate');
        btn.disabled=true;
        btn.innerHTML=SVG.spin+' Generating dari isi materi...';

        // Reset preview lama
        document.getElementById('aiPreview').classList.remove('open');
        document.getElementById('aiPreviewList').innerHTML='';

        try {
            const res=await fetch(`${API}/ai/generate-questions`,{
                method:'POST',
                headers:{Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'},
                body:JSON.stringify({
                    material_id: materialId,
                    counts:{ multiple_choice:mc, true_false:tf, multiple_answer:0 },
                    difficulty: diff
                })
            });
            const data=await res.json();
            if(!res.ok){toast(data.message||'Gagal generate soal.','err');return;}

            aiQuestions=data.questions||[];
            if(!aiQuestions.length){toast('AI tidak menghasilkan soal. Coba lagi.','err');return;}

            renderAIPreview();
            toast(`${aiQuestions.length} soal berhasil di-generate!`);
        } catch(_){toast('Koneksi bermasalah.','err');}
        finally{
            btn.disabled=false;
            btn.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg> Generate Soal dari Materi Ini`;
        }
    };

    function renderAIPreview(){
        const preview=document.getElementById('aiPreview');
        const list=document.getElementById('aiPreviewList');
        document.getElementById('aiPreviewTitle').textContent=`${aiQuestions.length} soal siap disimpan`;
        preview.classList.add('open');

        list.innerHTML=aiQuestions.map((q,i)=>{
            // Normalise: AI generate-questions return field yg mungkin beda dgn practice
            const qtext=q.question||q.question_text||'';
            const correct=(q.correct_answer||'A').toUpperCase();
            const opts=['a','b','c','d'].map(l=>{
                const v=q['option_'+l];
                if(!v)return'';
                const isC=correct===l.toUpperCase();
                return`<div class="ai-q-opt${isC?' correct':''}">
                    <span class="ol">${l.toUpperCase()}</span>${esc(v)}
                    ${isC?`<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-left:auto"><polyline points="20 6 9 17 4 12"/></svg>`:''}
                </div>`;
            }).join('');
            const expl=q.explanation?`<div class="ai-q-expl">${esc(q.explanation)}</div>`:'';
            const typeBadge=q.question_type==='true_false'
                ?`<span style="font-size:10px;font-weight:700;background:#fef3c7;color:#92400e;padding:1px 7px;border-radius:5px;margin-bottom:6px;display:inline-block">Benar/Salah</span>`
                :`<span style="font-size:10px;font-weight:700;background:#dbeafe;color:#1e40af;padding:1px 7px;border-radius:5px;margin-bottom:6px;display:inline-block">Pilihan Ganda</span>`;

            return`<div class="ai-q-card" id="aiq-${i}">
                ${typeBadge}
                <div class="ai-q-num">Soal ${i+1}</div>
                <div class="ai-q-text">${esc(qtext)}</div>
                <div class="ai-q-opts">${opts}</div>
                ${expl}
                <div class="ai-q-actions">
                    <button class="btn-qa-save" id="aiq-btn-${i}" onclick="saveOneAI(${i})">Simpan</button>
                    <button class="btn-qa-reject" onclick="rejectAI(${i})">Tolak</button>
                </div>
            </div>`;
        }).join('');
    }

    window.rejectAI=function(i){
        const card=document.getElementById(`aiq-${i}`);
        if(card)card.classList.add('rejected');
        aiQuestions[i]._rejected=true;
    };

    window.saveOneAI=async function(i){
        const q=aiQuestions[i];
        if(!q||q._saved||q._rejected)return;
        const btn=document.getElementById(`aiq-btn-${i}`);
        btn.disabled=true;btn.textContent='Menyimpan...';

        // Practice soal hanya support pilihan ganda 4 opsi + true/false
        // Normalise untuk endpoint practice
        let payload;
        if(q.question_type==='true_false'){
            // True/false: option_a=Benar, option_b=Salah
            payload={
                material_id:materialId,
                question_text:q.question||q.question_text||'',
                option_a:'Benar',
                option_b:'Salah',
                option_c:'—',
                option_d:'—',
                correct_answer:(q.correct_answer||'A').toLowerCase()
            };
        } else {
            payload={
                material_id:materialId,
                question_text:q.question||q.question_text||'',
                option_a:q.option_a||'',
                option_b:q.option_b||'',
                option_c:q.option_c||'—',
                option_d:q.option_d||'—',
                correct_answer:(q.correct_answer||'A').toLowerCase()
            };
        }

        const ok=await simpanSoalPractice(payload);
        if(ok){
            aiQuestions[i]._saved=true;
            const card=document.getElementById(`aiq-${i}`);
            if(card)card.classList.add('saved');
            btn.textContent='✓ Tersimpan';
            fetchQuestions();
        } else {
            btn.disabled=false;btn.textContent='Simpan';
        }
    };

    window.saveAllAI=async function(){
        const toSave=aiQuestions.filter(q=>!q._saved&&!q._rejected);
        if(!toSave.length){toast('Semua soal sudah disimpan atau ditolak.');return;}
        const allBtn=document.querySelector('.btn-save-all');
        allBtn.disabled=true;
        let saved=0;
        for(let i=0;i<aiQuestions.length;i++){
            const q=aiQuestions[i];
            if(q._saved||q._rejected)continue;
            const btn=document.getElementById(`aiq-btn-${i}`);
            if(btn){btn.disabled=true;btn.textContent='Menyimpan...';}
            let payload;
            if(q.question_type==='true_false'){
                payload={material_id:materialId,question_text:q.question||q.question_text||'',option_a:'Benar',option_b:'Salah',option_c:'—',option_d:'—',correct_answer:(q.correct_answer||'A').toLowerCase()};
            } else {
                payload={material_id:materialId,question_text:q.question||q.question_text||'',option_a:q.option_a||'',option_b:q.option_b||'',option_c:q.option_c||'—',option_d:q.option_d||'—',correct_answer:(q.correct_answer||'A').toLowerCase()};
            }
            const ok=await simpanSoalPractice(payload);
            if(ok){
                aiQuestions[i]._saved=true;saved++;
                const card=document.getElementById(`aiq-${i}`);
                if(card)card.classList.add('saved');
                if(btn){btn.textContent='✓ Tersimpan';}
            } else {
                if(btn){btn.disabled=false;btn.textContent='Simpan';}
            }
        }
        allBtn.disabled=false;
        toast(`${saved} soal berhasil disimpan!`);
        fetchQuestions();
    };

    async function simpanSoalPractice(payload){
        try {
            const res=await fetch(baseUrl,{method:'POST',headers:{'Authorization':'Bearer '+token,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(payload)});
            return res.ok;
        } catch(_){toast('Koneksi bermasalah.','err');return false;}
    }

    // ── Fetch & render soal ───────────────────────────────────
    async function fetchQuestions(){
        try {
            const res=await fetch(baseUrl,{headers:{'Authorization':'Bearer '+token,'Accept':'application/json'}});
            const data=await res.json();
            renderQuestions(data.data||data);
        } catch(_){
            document.getElementById('daftarSoalContainer').innerHTML=`<div class="empty-state"><div class="empty-ico" style="display:flex;align-items:center;justify-content:center">${SVG.inbox}</div><div class="empty-el">Gagal memuat soal</div></div>`;
        }
    }

    function renderQuestions(questions){
        const container=document.getElementById('daftarSoalContainer');
        document.getElementById('listSubtitle').textContent=`${questions?.length||0} soal tersimpan`;
        if(!questions||!questions.length){
            container.innerHTML=`<div class="empty-state"><div class="empty-ico" style="display:flex;align-items:center;justify-content:center">${SVG.inbox}</div><div class="empty-el">Belum ada soal</div><div class="empty-es">Generate dengan AI atau tambah manual.</div></div>`;
            return;
        }
        container.innerHTML='';
        questions.forEach((q,i)=>{
            const qId=q._id||q.id;
            const div=document.createElement('div');
            div.className='soal-item';div.id='soal-'+qId;
            const opts=['a','b','c','d'].map(l=>{
                const isC=q.correct_answer===l;
                return`<li class="${isC?'opsi-benar':''}">
                    <span class="opt-letter">${l.toUpperCase()}</span>
                    ${esc(q['option_'+l]||'—')}
                    ${isC?`<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-left:auto"><polyline points="20 6 9 17 4 12"/></svg>`:''}
                </li>`;
            }).join('');
            div.innerHTML=`
                <button class="btn-hapus" onclick="hapusSoal('${qId}',${i+1})">${SVG.trash} Hapus</button>
                <p class="soal-teks">${i+1}. ${esc(q.question_text||q.question||'')}</p>
                <ul class="opsi-list">${opts}</ul>`;
            container.appendChild(div);
        });
    }

    // ── Form manual ───────────────────────────────────────────
    document.getElementById('formTambahSoal').addEventListener('submit',async function(e){
        e.preventDefault();
        const btn=document.getElementById('btnSimpanSoal');
        const payload={
            material_id:materialId,
            question_text:document.getElementById('question_text').value,
            option_a:document.getElementById('option_a').value,
            option_b:document.getElementById('option_b').value,
            option_c:document.getElementById('option_c').value,
            option_d:document.getElementById('option_d').value,
            correct_answer:document.getElementById('correct_answer').value
        };
        btn.disabled=true;btn.innerHTML=SVG.spin+' Menyimpan...';
        const ok=await simpanSoalPractice(payload);
        if(ok){toast('Soal berhasil ditambahkan!');this.reset();fetchQuestions();}
        else toast('Gagal menyimpan soal.','err');
        btn.disabled=false;btn.innerHTML=`${SVG.check} Simpan Soal`;
    });

    // ── Hapus ─────────────────────────────────────────────────
    window.hapusSoal=function(id,nomor){
        showDialog({icon:'err',title:`Hapus Soal #${nomor}`,msg:'Yakin ingin menghapus soal ini?',confirmText:'Hapus',confirmClass:'confirm-err',onConfirm:()=>doHapus(id)});
    };
    window.doHapus=async function(id){
        const card=document.getElementById('soal-'+id);
        if(card){card.style.transition='opacity .2s,transform .2s';card.style.opacity='0';card.style.transform='translateX(16px)';}
        try {
            const res=await fetch(`${API}/questions/${id}`,{method:'DELETE',headers:{'Authorization':'Bearer '+token,'Accept':'application/json'}});
            if(res.ok){toast('Soal dihapus.');setTimeout(()=>fetchQuestions(),200);}
            else{if(card){card.style.opacity='1';card.style.transform='';}toast('Gagal menghapus.','err');}
        } catch(_){if(card){card.style.opacity='1';card.style.transform='';}toast('Koneksi bermasalah.','err');}
    };

    fetchQuestions();
})();
</script>
@endpush