<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Kuis: ') }} <span class="text-purple-600">{{ $material->title }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
            
            {{-- KOLOM KIRI: FORM TAMBAH SOAL --}}
            <div class="w-full md:w-1/3 bg-white shadow-sm sm:rounded-lg p-6 h-fit">
                <h3 class="text-lg font-bold text-gray-700 mb-4">➕ Tambah Soal Baru</h3>
                
                <form action="{{ route('questions.store', $material->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="block text-sm font-bold mb-1">Pertanyaan</label>
                        <textarea name="question_text" rows="3" class="w-full border-gray-300 rounded focus:ring-purple-500 focus:border-purple-500" required></textarea>
                    </div>

                    <div class="mb-2"><label class="text-sm">Opsi A</label><input type="text" name="option_a" class="w-full border-gray-300 rounded text-sm" required></div>
                    <div class="mb-2"><label class="text-sm">Opsi B</label><input type="text" name="option_b" class="w-full border-gray-300 rounded text-sm" required></div>
                    <div class="mb-2"><label class="text-sm">Opsi C</label><input type="text" name="option_c" class="w-full border-gray-300 rounded text-sm" required></div>
                    <div class="mb-3"><label class="text-sm">Opsi D</label><input type="text" name="option_d" class="w-full border-gray-300 rounded text-sm" required></div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1">Kunci Jawaban</label>
                        <select name="correct_answer" class="w-full border-gray-300 rounded focus:ring-purple-500 focus:border-purple-500" required>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded shadow">Simpan Soal</button>
                </form>
            </div>

            {{-- KOLOM KANAN: DAFTAR SOAL --}}
            <div class="w-full md:w-2/3 bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">📋 Daftar Soal ({{ $questions->count() }})</h3>
                
                @forelse ($questions as $index => $q)
                <div class="mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50 relative">
                    <p class="font-bold text-gray-800 mb-2">{{ $index + 1 }}. {{ $q->question_text }}</p>
                    <ul class="text-sm text-gray-600 mb-2">
                        <li class="{{ $q->correct_answer == 'a' ? 'text-green-600 font-bold' : '' }}">A. {{ $q->option_a }}</li>
                        <li class="{{ $q->correct_answer == 'b' ? 'text-green-600 font-bold' : '' }}">B. {{ $q->option_b }}</li>
                        <li class="{{ $q->correct_answer == 'c' ? 'text-green-600 font-bold' : '' }}">C. {{ $q->option_c }}</li>
                        <li class="{{ $q->correct_answer == 'd' ? 'text-green-600 font-bold' : '' }}">D. {{ $q->option_d }}</li>
                    </ul>
                    
                    {{-- Tombol Hapus Soal --}}
                    <form action="{{ route('questions.destroy', $q->id) }}" method="POST" class="absolute top-4 right-4" onsubmit="return confirm('Hapus soal ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-bold">❌ Hapus</button>
                    </form>
                </div>
                @empty
                <p class="text-gray-500 italic">Belum ada soal untuk materi ini. Silakan buat di form sebelah kiri.</p>
                @endforelse

                <a href="{{ route('materials.index') }}" class="inline-block mt-4 text-gray-600 hover:underline">⬅️ Kembali ke Daftar Materi</a>
            </div>

        </div>
    </div>
</x-app-layout>