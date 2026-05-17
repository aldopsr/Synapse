@extends('layouts.app')

@section('content')
    <div class="main">

        <a href="/kelolaAkunDosen" style="display: flex; gap: 0px 8px; text-decoration:none; width:fit-content;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                fill="none" stroke="#44474D" stroke-width="2.7"
                stroke-linecap="round" stroke-linejoin="round"
                style="width:35px; height: 28px;">
                <line x1="20" y1="12" x2="3" y2="12" />
                <polyline points="9 6 3 12 9 18" />
            </svg>
            <h2 style="color: #44474D;">Kembali</h2>
        </a>
    
        <div style="display: flex; flex-direction:column; align-items:center; width:100%; height:100%; padding-top:20px; gap: 50px;">
            <h1 style="color: #44474D; font-size: 40px; font-weight: 600;">Buat Akun Dosen</h1>
            
            <form id="formBuatDosen" class="profile-card">
                {{-- Input Nama --}}
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                    </svg>
                    <input type="text" name="name" placeholder="Nama Lengkap" class="profile-text-form" required>
                </div>

                {{-- Input Email --}}
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" />
                        <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />
                    </svg>
                    <input type="email" name="email" placeholder="Email" class="profile-text-form" required>
                </div>

                {{-- DROPDOWN PILIHAN MATKUL BARU --}}
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path fill-rule="evenodd" d="M3 6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Zm14.25 6a.75.75 0 0 1-.75.75h-9a.75.75 0 0 1 0-1.5h9a.75.75 0 0 1 .75.75Zm0 3.75a.75.75 0 0 1-.75.75h-9a.75.75 0 0 1 0-1.5h9a.75.75 0 0 1 .75.75ZM10.5 8.25a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9a.75.75 0 0 0-.75-.75h-.008Z" clip-rule="evenodd" />
                    </svg>
                    <select name="course_id" id="matkulSelect" class="profile-text-form" required style="border:none; width:100%; outline:none; background:transparent; font-size: 16px; color: #44474D; cursor: pointer;">
                        <option value="" disabled selected>-- Memuat Pilihan Matkul... --</option>
                        {{-- Opsi akan diisi otomatis oleh JavaScript di bawah --}}
                    </select>
                </div>

                {{-- Input Password --}}
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                    </svg>
                    <input type="password" name="password" placeholder="Masukan Sandi" class="profile-text-form" required>
                </div>

                {{-- Input Konfirmasi Password --}}
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                    </svg>
                    <input type="password" name="password_confirmation" placeholder="Konfirmasi Sandi" class="profile-text-form" required>
                </div>
                
                <button type="submit" class="button1" style="width: 100%; display:flex; flex-direction:row; justify-content:center; align-items:center; gap: 0px 8px; border:none; cursor:pointer; padding: 15px; border-radius: 8px;">
                    BUAT AKUN
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // 1. KODE UNTUK MENGAMBIL DAFTAR MATKUL SAAT HALAMAN DIBUKA
    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('token');
        const selectMatkul = document.getElementById('matkulSelect');
        
        // Ganti URL ini dengan rute API Backend yang menampilkan daftar matkul/course
        const apiMatkulUrl = window.apiBaseUrl + '/courses';

        try {
            const response = await fetch(apiMatkulUrl, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            const result = await response.json();

            if (response.ok && result.data) {
                // Bersihkan opsi "Memuat..."
                selectMatkul.innerHTML = '<option value="" disabled selected>-- Pilih Mata Kuliah --</option>';
                
                // Looping data matkul dari database dan masukkan ke dropdown
                result.data.forEach(matkul => {
                    const option = document.createElement('option');
                    // Catatan: Sesuaikan '.id' dan '.title' dengan struktur JSON dari backend API Kapten
                    option.value = matkul.id; 
                    option.textContent = matkul.title; 
                    selectMatkul.appendChild(option);
                });
            } else {
                selectMatkul.innerHTML = '<option value="" disabled selected>Gagal memuat matkul</option>';
            }
        } catch (error) {
            console.error('Error fetching matkul:', error);
            selectMatkul.innerHTML = '<option value="" disabled selected>Koneksi API Error</option>';
        }
    });

    // 2. KODE UNTUK MENGIRIM FORM (TERMASUK DATA MATKUL) KE BACKEND
    document.getElementById('formBuatDosen').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        // Memaksa role menjadi dosen secara otomatis
        data.role = 'dosen'; 
        
        const token = localStorage.getItem('token');
        const apiUserUrl = window.apiBaseUrl + '/dosen';

        try {
            const response = await fetch(apiUserUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                alert('Berhasil: ' + result.message);
                window.location.href = '/kelolaAkunDosen';
            } else {
                let errorMessage = result.message || 'Gagal membuat akun.';
                if (result.errors) {
                    const firstError = Object.values(result.errors)[0][0];
                    errorMessage += '\n' + firstError;
                }
                alert('Gagal: ' + errorMessage);
            }
        } catch (error) {
            console.error('Network Error:', error);
            alert('Terjadi kesalahan jaringan.');
        }
    });
</script>
@endpush