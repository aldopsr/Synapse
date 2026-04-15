<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-700">👥 Daftar Pengguna SYNAPSE</h3>
                    
                    <a href="{{ route('users.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition">
                        + Tambah Pengguna
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p class="font-bold">Akses Ditolak!</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="py-3 px-4 border-b font-semibold">Nama</th>
                                <th class="py-3 px-4 border-b font-semibold">Email</th>
                                <th class="py-3 px-4 border-b font-semibold">Jabatan</th>
                                <th class="py-3 px-4 border-b font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 border-b font-bold">{{ $user->name }}</td>
                                <td class="py-3 px-4 border-b">{{ $user->email }}</td>
                                <td class="py-3 px-4 border-b">
                                    {{-- Beri warna berbeda untuk setiap Role --}}
                                    @if ($user->role === 'admin')
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-sm font-bold">Admin</span>
                                    @elseif ($user->role === 'dosen')
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-bold">Dosen</span>
                                    @else
                                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm font-bold">Mahasiswa</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 border-b text-center">
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Peringatan: Menghapus user ini bersifat permanen. Lanjutkan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm font-bold py-1 px-3 rounded shadow">
                                            Buang
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>