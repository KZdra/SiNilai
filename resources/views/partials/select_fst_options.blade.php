@php
    $groupedFst = collect($fstList)->groupBy(function($fst) {
        $fase = strtoupper(trim($fst->fase ?? ''));
        if ($fase === 'E') return 'Fase E (Tingkat X / Kelas 10)';
        if ($fase === 'F') return 'Fase F (Tingkat XI & XII / Kelas 11 & 12)';
        return 'Fase ' . ($fase ?: 'Lainnya');
    })->sortBy(function($val, $key) {
        return str_contains($key, 'Fase E') ? 1 : 2;
    });
@endphp

@foreach ($groupedFst as $faseName => $items)
    <optgroup label="{{ $faseName }}">
        @foreach ($items as $fst)
            @php
                $isSelected = false;
                if (isset($selected)) {
                    $isSelected = ($selected == $fst->id);
                } elseif (isset($selectedFstId)) {
                    $isSelected = ($selectedFstId == $fst->id);
                }
                $lockIcon = !empty($fst->is_locked) ? ' [🔒 Terkunci]' : '';
            @endphp
            <option value="{{ $fst->id }}" data-locked="{{ $fst->is_locked ?? 0 }}" {{ $isSelected ? 'selected' : '' }}>
                Fase {{ $fst->fase }} • Semester {{ $fst->semester }} • T.A {{ $fst->tahun_ajaran }} ({{ ucfirst($fst->ta) }}){{ $lockIcon }}
            </option>
        @endforeach
    </optgroup>
@endforeach
