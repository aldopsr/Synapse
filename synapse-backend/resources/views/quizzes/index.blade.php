<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Kuis Utama') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-4">
                <a href="{{ route('quizzes.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    + Buat Kuis Baru
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b">
                                <th class="p-4 font-semibold text-gray-600">No</th>
                                <th class="p-4 font-semibold text-gray-600">Judul Kuis</th>
                                <th class="p-4 font-semibold text-gray-600">Waktu Pengerjaan</th>
                                <th class="p-4 font-semibold text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quizzes as $index => $quiz)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4">{{ $index + 1 }}</td>
                                <td class="p-4">{{ $quiz->title }}</td>
                                <td class="p-4">{{ $quiz->duration_minutes }} Menit</td>
                                <td class="p-4 flex gap-2">
    <a href="{{ route('quizzes.questions', $quiz->id) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
        Kelola Soal
    </a>
    
    <a href="{{ route('quizzes.edit', $quiz->id) }}" class="inline-flex items-center px-3 py-1 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600">
        Edit
    </a>

    <form action="{{ route('quizzes.destroy', $quiz->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kuis ini beserta semua soalnya?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
            Hapus
        </button>
    </form>
</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500">Belum ada Kuis Utama.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>