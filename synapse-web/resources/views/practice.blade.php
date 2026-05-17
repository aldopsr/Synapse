@extends('layouts.app')

@section('title', 'Kelola Latihan Soal - Synapse')

@section('content')
    <style>
        .top-navigation { margin-bottom: 20px; }
        .btn-kembali { background-color: #f1f1f1; color: #333; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; display: inline-block; border: 1px solid #ddd; }
        .btn-kembali:hover { background-color: #e2e2e2; }

        .header-title { margin-bottom: 20px; font-size: 22px; color: #333; }
        .text-purple { color: #279685; } /* Disesuaikan dengan tema Synapse */

        .layout-container { display: flex; gap: 25px; flex-wrap: wrap; align-items: flex-start; }
        
        /* Card Kiri & Kanan */
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; padding: 25px; box-sizing: border-box; }
        .col-kiri { flex: 1; min-width: 300px; max-width: 400px; }
        .col-kanan { flex: 2; min-width: 400px; }

        .card-title { font-size: 16px; font-weight: bold; color: #333; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }

        /* Form Styling (Mirip dengan Buat Akun) */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #555; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-family: 'Inter', Arial, sans-serif; transition: 0.2s; }
        .form-control:focus { border-color: #279685; outline: none; box-shadow: 0 0 0 3px rgba(39, 150, 133, 0.1); }
        
        .btn-simpan { background-color: #279685; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.2s; font-size: 14px; }
        .btn-simpan:hover { background-color: #1f7a6c; transform: translateY(-1px); }

        /* List Soal Styling */
        .soal-item { border: 1px solid #eee; background-color: #FAFAFA; border-radius: 8px; padding: 18px; margin-bottom: 15px; position: relative; transition: 0.2s; }
        .soal-item:hover { border-color: #ddd; background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .soal-teks { font-weight: bold; color: #333; margin-top: 0; margin-bottom: 12px; font-size: 15px; padding-right: 60px; }
        .opsi-list { list-style-type: none; padding: 0; margin: 0; font-size: 14px; color: #666; }
        .opsi-list li { margin-bottom: 6px; padding: 6px 10px; border-radius: 6px; background: #fff; border: 1px solid #eee; }
        .opsi-benar { border-color: #279685 !important; background-color: #E3FAF8 !important; color: #1f7a6c; font-weight: bold; }

        .btn-hapus { position: absolute; top: 15px; right: 15px; background: none; border: none; color: #E74C3C; font-weight: bold; font-size: 13px; cursor: pointer; }
        .btn-hapus:hover { text-decoration: underline; }

        .empty-state { text-align: center; color: #888; font-style: italic; padding: 20px; }
    </style>

    <div class="top-navigation">
        <a href="/mata-kuliah/{{ $course_id }}/materi" class="btn-kembali" id="btnKembali">⬅ Kembali ke Materi</a>
    </div>
    
    <h2 class="header-title">Kelola Latihan: <span class="text-purple" id="judulMateri">Memuat...</span></h2>

    <div class="layout-container">
        
        <div class="card col-kiri">
            <h3 class="card-title">➕ Tambah Soal Baru</h3>
            <form id="formTambahSoal">
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea id="question_text" class="form-control" rows="3" placeholder="Masukkan pertanyaan..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Opsi A</label>
                    <input type="text" id="option_a" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Opsi B</label>
                    <input type="text" id="option_b" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Opsi C</label>
                    <input type="text" id="option_c" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Opsi D</label>
                    <input type="text" id="option_d" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Kunci Jawaban</label>
                    <select id="correct_answer" class="form-control" required>
                        <option value="a">A</option>
                        <option value="b">B</option>
                        <option value="c">C</option>
                        <option value="d">D</option>
                    </select>
                </div>

                <button type="submit" class="btn-simpan" id="btnSimpanSoal">Simpan Soal</button>
            </form>
        </div>

        <div class="card col-kanan">
            <h3 class="card-title">📋 Daftar Soal</h3>
            <div id="daftarSoalContainer">
                <div class="empty-state">Loading data soal...</div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    (function() {
        // Sistem Token Synapse Kapten
        const token = window.token || localStorage.getItem('token');
        if (!token) { window.location.href = '/'; return; }

        const materialId = "{{ $materi_id }}"; 

// Tambahkan pengaman ini agar kalau kosong ketahuan
if (!materialId) {
    alert("Error: ID Materi gagal dimuat dari PHP ke JavaScript!");
}
        const baseUrl = `${window.apiBaseUrl}/materials/${materialId}/questions`;

        // 1. Ambil Data Detail Materi (Opsional: untuk menampilkan Judul)
        fetch(`${window.apiBaseUrl}/materials/${materialId}`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            const materi = data.data || data;
            if(materi.title) document.getElementById('judulMateri').innerText = materi.title;
        }).catch(err => console.log(err));

        // 2. Fetch Data Soal
        fetchQuestions();

        async function fetchQuestions() {
            try {
                const response = await fetch(baseUrl, {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const data = await response.json();
                renderQuestions(data.data || data);
            } catch (error) {
                document.getElementById('daftarSoalContainer').innerHTML = '<div class="empty-state" style="color:red;">Gagal memuat soal.</div>';
            }
        }

        function renderQuestions(questions) {
            const container = document.getElementById('daftarSoalContainer');
            container.innerHTML = '';

            if (!questions || questions.length === 0) {
                container.innerHTML = '<div class="empty-state">Belum ada soal untuk materi ini. Silakan buat di form sebelah kiri.</div>';
                return;
            }

            questions.forEach((q, index) => {
                const qId = q._id || q.id;
                
                const div = document.createElement('div');
                div.className = 'soal-item';
                div.innerHTML = `
                    <button class="btn-hapus" onclick="hapusSoal('${qId}')">❌ Hapus</button>
                    <p class="soal-teks">${index + 1}. ${q.question_text}</p>
                    <ul class="opsi-list">
                        <li class="${q.correct_answer === 'a' ? 'opsi-benar' : ''}">A. ${q.option_a}</li>
                        <li class="${q.correct_answer === 'b' ? 'opsi-benar' : ''}">B. ${q.option_b}</li>
                        <li class="${q.correct_answer === 'c' ? 'opsi-benar' : ''}">C. ${q.option_c}</li>
                        <li class="${q.correct_answer === 'd' ? 'opsi-benar' : ''}">D. ${q.option_d}</li>
                    </ul>
                `;
                container.appendChild(div);
            });
        }

        // 3. Tambah Soal Baru
        document.getElementById('formTambahSoal').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btnSimpan = document.getElementById('btnSimpanSoal');
            
            const payload = {
                material_id: materialId, // Sesuai DB MongoDB Kapten
                question_text: document.getElementById('question_text').value,
                option_a: document.getElementById('option_a').value,
                option_b: document.getElementById('option_b').value,
                option_c: document.getElementById('option_c').value,
                option_d: document.getElementById('option_d').value,
                correct_answer: document.getElementById('correct_answer').value
            };

            btnSimpan.innerText = 'Menyimpan...';
            btnSimpan.disabled = true;

            try {
                const response = await fetch(baseUrl, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    alert("Soal berhasil ditambahkan!");
                    document.getElementById('formTambahSoal').reset();
                    fetchQuestions(); // Refresh list soal
                } else {
                    const errorData = await response.json();
                    alert("Gagal menyimpan: " + (errorData.message || 'Cek API Laravel Anda'));
                }
            } catch (error) {
                alert("Terjadi kesalahan jaringan.");
            } finally {
                btnSimpan.innerText = 'Simpan Soal';
                btnSimpan.disabled = false;
            }
        });

        // 4. Hapus Soal
        window.hapusSoal = async function(idSoal) {
            if(confirm("Apakah Anda yakin ingin menghapus soal ini?")) {
                try {
                    const deleteUrl = `${window.apiBaseUrl}/questions/${idSoal}`;
                    const response = await fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });

                    if(response.ok) {
                        fetchQuestions();
                    } else {
                        alert('Gagal menghapus soal.');
                    }
                } catch (error) {
                    alert('Terjadi kesalahan saat menghapus.');
                }
            }
        };

    })();
</script>
@endpush