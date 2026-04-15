<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Materi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-700">📚 Daftar Materi Saya</h3>
                    <a href="{{ route('materials.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                        ➕ Tambah Materi Baru
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="py-3 px-4 border-b font-semibold">Judul</th>
                                <th class="py-3 px-4 border-b font-semibold">Deskripsi Singkat</th>
                                <th class="py-3 px-4 border-b font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Looping data materi dari Database! --}}
                            @forelse ($materials as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 border-b">{{ $item->title }}</td>
                                <td class="py-3 px-4 border-b text-sm text-gray-600">{{ $item->description }}</td>
                                <td class="py-3 px-4 border-b text-center flex justify-center gap-2">
                                    {{-- Tombol Kuis --}}
                                    <a href="{{ route('questions.index', $item->id) }}" class="bg-purple-500 hover:bg-purple-600 text-white text-sm font-bold py-1 px-3 rounded shadow">
                                        📝 Kuis
                                    </a>

                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('materials.edit', $item->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-bold py-1 px-3 rounded shadow">
                                        Edit
                                    </a>
                                    
                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('materials.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus materi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm font-bold py-1 px-3 rounded shadow">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500 italic">Belum ada materi. Silakan tambah materi baru.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>