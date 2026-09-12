@php
    $useName = $useNameAsValue ?? false;
    $exclude = $excludeId ?? null;
    $groupedClasses = collect($classList)->filter(function($c) use ($exclude) {
        return !$exclude || $c->id != $exclude;
    })->groupBy(function($c) {
        $name = strtoupper(trim($c->class_name));
        if (str_starts_with($name, 'XII') || str_contains($name, 'KELAS 12') || str_contains($name, 'KELAS XII')) return 'Tingkat XII (Fase F)';
        if (str_starts_with($name, 'XI') || str_contains($name, 'KELAS 11') || str_contains($name, 'KELAS XI')) return 'Tingkat XI (Fase F)';
        if (str_starts_with($name, 'X') || str_contains($name, 'KELAS 10') || str_contains($name, 'KELAS X')) return 'Tingkat X (Fase E)';
        return 'Kelas Lainnya';
    })->sortBy(function($val, $key) {
        if (str_contains($key, 'Tingkat X ')) return 1;
        if (str_contains($key, 'Tingkat XI ')) return 2;
        if (str_contains($key, 'Tingkat XII ')) return 3;
        return 4;
    });
@endphp

@foreach ($groupedClasses as $groupName => $classes)
    <optgroup label="{{ $groupName }}">
        @foreach ($classes as $class)
            @php
                $val = $useName ? $class->class_name : $class->id;
                $isSelected = false;
                if (isset($selected)) {
                    $isSelected = ($selected == $val);
                } elseif (isset($selectedClassId)) {
                    $isSelected = ($selectedClassId == $class->id);
                } elseif (isset($selectedClassName)) {
                    $isSelected = ($selectedClassName == $class->class_name);
                }
            @endphp
            <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }}>
                {{ $class->class_name }}
            </option>
        @endforeach
    </optgroup>
@endforeach
