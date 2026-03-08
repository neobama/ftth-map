# Fix Multiple Root Elements Error

## Masalah
Error: `Livewire only supports one HTML element per component. Multiple root elements detected for component: [app.filament.pages.map]`

## Solusi yang Sudah Diterapkan

### 1. Memindahkan Script ke @push
Semua script tags dipindahkan ke luar komponen Livewire menggunakan `@push('scripts')` agar tidak dihitung sebagai root element.

### 2. Struktur File
File sekarang memiliki struktur:
```blade
<x-filament-panels::page>
    <div class="relative w-full h-screen" style="height: calc(100vh - 4rem);">
        <!-- Semua konten HTML -->
    </div>
</x-filament-panels::page>

@push('scripts')
    <!-- Semua script tags di sini -->
@endpush
```

### 3. Perubahan yang Dilakukan
- ✅ Memindahkan semua `<script>` tags ke `@push('scripts')`
- ✅ Memastikan hanya ada 1 root element (`<div class="relative...">`)
- ✅ Menghapus `@php` directive dari template (sudah dipindahkan ke method `getGoogleMapsKey()`)
- ✅ Menggunakan `x-show` untuk conditional rendering (bukan `@if`)

## Verifikasi

### Di Local (sudah benar):
```bash
cd /Users/neorafa/Documents/projects/ftth-map
python3 << 'EOF'
import re
with open('resources/views/filament/pages/map.blade.php', 'r') as f:
    content = f.read()
match = re.search(r'<x-filament-panels::page>(.*?)</x-filament-panels::page>', content, re.DOTALL)
if match:
    inner = match.group(1).strip()
    lines = inner.split('\n')
    top_level = []
    base_indent = None
    for i, line in enumerate(lines):
        stripped = line.lstrip()
        if not stripped or stripped.startswith('<!--') or stripped.startswith('@'):
            continue
        if stripped.startswith('<') and not stripped.startswith('</'):
            current_indent = len(line) - len(stripped)
            if base_indent is None:
                base_indent = current_indent
            if current_indent == base_indent:
                top_level.append(f"Line {i+1}")
    print(f"Top-level elements: {len(top_level)}")
    if len(top_level) == 1:
        print("✅ Structure is correct")
    else:
        print(f"⚠️ Found {len(top_level)} elements!")
EOF
```

### Di Server (setelah pull):
```bash
cd /var/www/ftth

# 1. Pull perubahan
git pull origin main

# 2. Verify file
wc -l resources/views/filament/pages/map.blade.php
# Harus menunjukkan sekitar 626 baris

head -5 resources/views/filament/pages/map.blade.php
# Harus menunjukkan konten lengkap

# 3. Clear cache
rm -rf storage/framework/views/*
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# 4. Test
# Refresh browser dengan Ctrl+F5
```

## Catatan Penting

1. **File di server harus ter-pull dengan benar** - pastikan file memiliki ~626 baris, bukan hanya 2 baris
2. **Clear semua cache** setelah pull
3. **Jika masih error**, check apakah `@push('scripts')` didukung di Filament layout Anda

## Jika @push Tidak Bekerja

Jika `@push('scripts')` tidak bekerja di Filament, alternatifnya adalah menggunakan method di Page class:

```php
// app/Filament/Pages/Map.php
public function getFooterScripts(): string
{
    $key = $this->getGoogleMapsKey();
    if (empty($key)) {
        return '';
    }
    
    return view('filament.pages.map-scripts', ['apiKey' => $key])->render();
}
```

Tapi untuk sekarang, `@push` seharusnya sudah bekerja.
