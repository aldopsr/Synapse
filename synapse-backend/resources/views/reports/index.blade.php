<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('🏆 Laporan Nilai Mahasiswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-10 bg-white border-b border-gray-200">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Rekapitulasi Nilai Latihan</h3>
                        <button class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export Excel (Coming Soon)
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                            <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">No</th>
                                    <th class="px-6 py-4">Nama Mahasiswa</th>
                                    <th class="px-6 py-4">E-Modul / Materi</th>
                                    <th class="px-6 py-4 text-center">Nilai Akhir</th>
                                    <th class="px-6 py-4">Waktu Pengerjaan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($scores as $index => $score)
                                    <tr class="hover:bg-blue-50 transition duration-150">
                                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 font-semibold text-gray-800">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                                    {{ substr($score->user->name, 0, 1) }}
                                                </div>
                                                {{ $score->user->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-blue-600 font-medium">{{ $score->quiz->title }}</td>
                                        <td class="px-6 py-4 text-center">
                                            {{-- Indikator Warna Nilai --}}
                                            @if($score->score >= 80)    
                                                <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full font-bold text-md">{{ $score->score }}</span>
                                            @elseif($score->score >= 60)
                                                <span class="bg-yellow-100 text-yellow-800 py-1 px-3 rounded-full font-bold text-md">{{ $score->score }}</span>
                                            @else
                                                <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full font-bold text-md">{{ $score->score }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            {{ $score->created_at->format('d M Y, H:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                            Belum ada mahasiswa yang mengerjakan latihan soal ini. 📭
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>