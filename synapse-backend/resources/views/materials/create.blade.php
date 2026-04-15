<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tulis Materi Baru') }}
        </h2>
    </x-slot>

    <style>
        .ck-editor__editable_inline {
            min-height: 300px; 
        }
    </style>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                
                <h3 class="text-xl font-bold text-gray-700 mb-6">📝 Form E-Modul</h3>

                {{-- Wajib tambahkan enctype="multipart/form-data" agar form bisa mengirim File Foto! --}}
                <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf 

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Judul Materi</label>
                        <input type="text" name="title" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Contoh: Pengenalan API" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Deskripsi Singkat</label>
                        <input type="text" name="description" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Muncul di Kartu Dashboard Flutter" required>
                    </div>

                    {{-- Fitur Baru: Upload Foto Sampul --}}
                    <div class="mb-4 p-4 border border-dashed border-gray-400 rounded-md bg-gray-50">
                        <label class="block text-gray-700 font-bold mb-2">🖼️ Foto Sampul (Opsional)</label>
                        <input type="file" name="image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*">
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, WEBP (Maks: 2MB)</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Isi Materi E-Modul</label>
                        <textarea id="editor" name="content" placeholder="Tuliskan isi materi..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('materials.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow">Batal</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">💾 Simpan E-Modul</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Script pemanggil CKEditor & Adapter Upload Gambar --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        // 1. Membuat Adapter Kustom untuk menghubungkan CKEditor dengan Laravel
        class MyUploadAdapter {
            constructor(loader) {
                this.loader = loader;
            }

            upload() {
                return this.loader.file
                    .then(file => new Promise((resolve, reject) => {
                        const data = new FormData();
                        data.append('upload', file);
                        data.append('_token', '{{ csrf_token() }}'); // Satpam Laravel

                        // Kirim gambar ke rute yang baru kita buat
                        fetch('{{ route('ckeditor.upload') }}', {
                            method: 'POST',
                            body: data
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.error) {
                                reject(result.error.message);
                            } else {
                                // Jika sukses, berikan URL gambar kembali ke CKEditor
                                resolve({ default: result.url });
                            }
                        })
                        .catch(error => {
                            reject('Upload gagal: ' + error.message);
                        });
                    }));
            }

            abort() {
                // Fungsi untuk membatalkan upload (wajib ada meski kosong)
            }
        }

        function MyCustomUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                return new MyUploadAdapter(loader);
            };
        }

        // 2. Inisialisasi CKEditor dengan Plugin Adapter kita
        ClassicEditor
            .create(document.querySelector('#editor'), {
                extraPlugins: [MyCustomUploadAdapterPlugin], // Aktifkan plugin
            })
            .catch(error => {
                console.error(error);
            });
    </script>
</x-app-layout>