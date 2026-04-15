<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kelola Soal: {{ $quiz->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
            
            <div class="w-full md:w-1/3 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 h-fit">
                <h3 class="text-lg font-bold mb-4">Tambah Soal Baru</h3>
                
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('quizzes.questions.store', $quiz->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-bold mb-1">Pertanyaan</label>
                        <textarea name="question" rows="3" class="w-full border-gray-300 rounded shadow-sm" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-bold mb-1">Pilihan A</label>
                        <input type="text" name="option_a" class="w-full border-gray-300 rounded shadow-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-bold mb-1">Pilihan B</label>
                        <input type="text" name="option_b" class="w-full border-gray-300 rounded shadow-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-bold mb-1">Pilihan C</label>
                        <input type="text" name="option_c" class="w-full border-gray-300 rounded shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-bold mb-1">Pilihan D</label>
                        <input type="text" name="option_d" class="w-full border-gray-300 rounded shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1">Kunci Jawaban</label>
                        <select name="correct_answer" class="w-full border-gray-300 rounded shadow-sm" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Simpan Soal
                    </button>
                    <a href="{{ route('quizzes.index') }}" class="block text-center mt-3 text-sm text-gray-500 hover:text-gray-800">Kembali ke Daftar Kuis</a>
                </form>
            </div>

            <div class="w-full md:w-2/3 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Daftar Soal (Total: {{ $questions->count() }})</h3>
                
                @forelse($questions as $index => $q)
                    <div class="mb-4 p-4 border border-gray-200 rounded bg-gray-50">
                        <p class="font-bold mb-2">{{ $index + 1 }}. {{ $q->question }}</p>
                        <ul class="text-sm list-disc pl-5 mb-2">
                            <li class="{{ $q->correct_answer == 'A' ? 'text-green-600 font-bold' : '' }}">A. {{ $q->option_a }}</li>
                            <li class="{{ $q->correct_answer == 'B' ? 'text-green-600 font-bold' : '' }}">B. {{ $q->option_b }}</li>
                            <li class="{{ $q->correct_answer == 'C' ? 'text-green-600 font-bold' : '' }}">C. {{ $q->option_c }}</li>
                            <li class="{{ $q->correct_answer == 'D' ? 'text-green-600 font-bold' : '' }}">D. {{ $q->option_d }}</li>
                        </ul>
                        <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded">Kunci: {{ $q->correct_answer }}</span>
                        <div class="mt-3 flex justify-end">
    <form action="{{ route('quizzes.questions.destroy', ['quiz_id' => $quiz->id, 'question_id' => $q->id]) }}" method="POST" onsubmit="return confirm('Hapus soal ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
            🗑️ Hapus Soal
        </button>
    </form>
</div>
                    </div>
                @empty
                    <p class="text-gray-500 italic">Belum ada soal di kuis ini. Silakan tambahkan di form sebelah kiri.</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>