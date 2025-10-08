@extends('layout.layout')

@section('content')
<div class="container mx-auto py-4">
    <h2 class="text-2xl font-bold mb-4">Kelola Libur Karyawan</h2>

    {{-- Filter --}}
    <div class="flex items-center justify-between mb-6">
        <form action="{{ route('libur.index', ['bulan' => request('bulan', $currentMonth), 'tahun' => request('tahun', $currentYear)]) }}" method="GET">
            <!-- Filter Bulan -->
            <div>
                <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                <select id="bulan" name="bulan" 
                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @foreach (range(1, 12) as $month)
                        <option value="{{ $month }}" {{ $currentMonth == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $month)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
    
            <!-- Filter Tahun -->
            <div>
                <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                <select id="tahun" name="tahun" 
                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @foreach (range(now()->year - 5, now()->year + 5) as $year)
                        <option value="{{ $year }}" {{ $currentYear == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
    
            <!-- Tombol Submit -->
            <div class="flex items-end">
                <button type="submit" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Filter
                </button>
            </div>
        </form>
    </div> 
    
    {{-- Tombol Tambah --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Kelola Libur Karyawan</h2>
        <a href="{{ route('libur.create') }}" 
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            Tambah Libur
        </a>
    </div>

    {{-- Kalender --}}
    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-2">Kalender Libur</h3>

        <div id="calendar" class="mt-2"></div>
    </div>

    {{-- Ringkasan --}}
    <div id="summary" class="mt-4">
        <h3 class="text-xl font-bold mb-4">Ringkasan Libur Karyawan</h3>
        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">Nama Karyawan</th>
                    <th class="border border-gray-300 p-2">Total Libur</th>
                    <th class="border border-gray-300 p-2">Tanggal Libur</th>
                    <th class="border border-gray-300 p-2">Keterangan</th>
                    <th class="border border-gray-300 p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary as $item)
                    <tr>
                        <td class="border border-gray-300 p-2">{{ $item['karyawan'] }}</td>
                        <td class="border border-gray-300 p-2 text-center">{{ $item['total'] }}</td>
                        <td class="border border-gray-300 p-2">
                            <ul>
                                @foreach ($item['dates'] as $date)
                                    <li>{{ $date }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <ul>
                                @foreach ($item['keterangan'] as $keterangan)
                                    <li>{{ $keterangan }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="border border-gray-300 p-2 flex flex-col">
                            @foreach ($item['ids'] as $id)
                                <div class="mb-2">
                                    <a href="{{ route('libur.edit', $id) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                                    <form action="{{ route('libur.destroy', $id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="border border-gray-300 p-2 text-center">Belum ada data libur</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const holidays = @json($holidays); // Data liburan dari backend

        const calendar = document.getElementById('calendar');
        const selectedMonth = {{ $currentMonth }};
        const selectedYear = {{ $currentYear }};

        const generateCalendar = () => {
            const monthNames = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];

            let firstDay = new Date(selectedYear, selectedMonth - 1, 1).getDay();
            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();

            let calendarHTML = `
                <div class="text-center mb-4">
                    <h4 class="text-xl font-bold">${monthNames[selectedMonth - 1]} ${selectedYear}</h4>
                </div>
                <table class="table-auto w-full border-collapse border border-gray-300">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 p-2">Minggu</th>
                            <th class="border border-gray-300 p-2">Senin</th>
                            <th class="border border-gray-300 p-2">Selasa</th>
                            <th class="border border-gray-300 p-2">Rabu</th>
                            <th class="border border-gray-300 p-2">Kamis</th>
                            <th class="border border-gray-300 p-2">Jumat</th>
                            <th class="border border-gray-300 p-2">Sabtu</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            let day = 1;
            for (let i = 0; i < 6; i++) {
                let row = "<tr>";

                for (let j = 0; j < 7; j++) {
                    if (i === 0 && j < firstDay) {
                        row += `<td class="border border-gray-300 p-2"></td>`;
                    } else if (day > daysInMonth) {
                        row += `<td class="border border-gray-300 p-2"></td>`;
                    } else {
                        const date = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        const holiday = holidays[date];

                        if (holiday) {
                            const employeeNames = holiday.map(h => h.name).join(', ');

                            row += `
                                <td class="border border-gray-300 p-2 bg-blue-100 text-sm text-center">
                                    <div class="font-bold">${day}</div>
                                    <ul class="mt-1 text-xs text-gray-700">
                                        ${employeeNames}
                                    </ul>
                                </td>`;
                        } else {
                            row += `
                                <td class="border border-gray-300 p-2 text-sm text-center">
                                    <div class="font-bold">${day}</div>
                                </td>`;
                        }
                        day++;
                    }
                }

                row += "</tr>";
                calendarHTML += row;

                if (day > daysInMonth) break;
            }

            calendarHTML += `
                    </tbody>
                </table>
            `;

            calendar.innerHTML = calendarHTML;
        };

        generateCalendar();
    });
</script>
@endsection
