<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            👋 Selamat Datang, {{ Auth::user()->name }}!
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- WIDGET KARTU STATISTIK --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                
                {{-- Kartu 1: Total Materi --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-lg transition duration-300">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-600">Total E-Modul Anda</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $totalMaterials }} <span class="text-sm font-normal text-gray-500">Materi</span></p>
                        </div>
                    </div>
                </div>

                {{-- Kartu 2: Total Mahasiswa Mengerjakan --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500 hover:shadow-lg transition duration-300">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-600">Latihan Diselesaikan</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $totalScores }} <span class="text-sm font-normal text-gray-500">Kali</span></p>
                        </div>
                    </div>
                </div>

                {{-- Kartu 3: Rata-Rata Nilai --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-lg transition duration-300">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </div>
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-600">Rata-Rata Nilai Kelas</p>
                            <p class="text-3xl font-bold text-gray-800">{{ number_format($avgScore, 1) }}</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- JALAN PINTAS (QUICK ACTIONS) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6">🚀 Menu Navigasi Utama</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    {{-- 1. Tombol Kelola Materi --}}
                    <a href="{{ route('materials.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl shadow-md transition transform hover:-translate-y-1 flex flex-col items-center justify-center gap-3 text-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span>📚 Kelola Materi</span>
                    </a>

                    {{-- 2. Tombol Kelola Kuis & Soal --}}
                    <a href="{{ route('quizzes.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-6 rounded-xl shadow-md transition transform hover:-translate-y-1 flex flex-col items-center justify-center gap-3 text-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <span>📝 Kelola Kuis</span>
                    </a>

                    {{-- 3. Tombol Laporan Nilai --}}
                    <a href="{{ route('reports.index') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-xl shadow-md transition transform hover:-translate-y-1 flex flex-col items-center justify-center gap-3 text-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span>📊 Papan Skor</span>
                    </a>

                    {{-- 4. Tombol Kelola User (Hanya Muncul Jika Login Sebagai Admin) --}}
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('users.index') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl shadow-md transition transform hover:-translate-y-1 flex flex-col items-center justify-center gap-3 text-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span>👥 Kelola User</span>
                    </a>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>