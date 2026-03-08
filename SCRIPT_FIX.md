# Fix: Memindahkan Script ke External File

## Masalah
Livewire mendeteksi multiple root elements karena script tags di dalam template.

## Solusi
Memindahkan semua script ke external file `public/js/map.js` dan menggunakan Alpine.js `x-data` untuk inisialisasi.

## Perubahan

### 1. File Baru: `public/js/map.js`
Semua JavaScript code untuk Google Maps dipindahkan ke file ini.

### 2. Template: `resources/views/filament/pages/map.blade.php`
- Menghapus semua `<script>` tags
- Menggunakan Alpine.js `x-data` untuk inisialisasi
- Hanya ada 1 root element (`<div>`)

## Cara Kerja

1. Alpine.js `x-data` dengan `init()` method dipanggil saat component mount
2. Script external `/js/map.js` dimuat
3. Setelah loaded, `loadGoogleMapsAPI()` dipanggil
4. Google Maps API dimuat dengan callback `initGoogleMap`

## Setup di Server

```bash
cd /var/www/ftth

# 1. Pull perubahan
git pull origin main

# 2. Pastikan file map.js ada
ls -la public/js/map.js

# 3. Set permissions
chmod 644 public/js/map.js

# 4. Clear cache
rm -rf storage/framework/views/*
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# 5. Test
# Refresh browser dengan Ctrl+F5
```

## Catatan

- Script tags sekarang di external file, bukan di template
- Livewire tidak akan menghitung script tags sebagai root element
- Alpine.js `x-data` digunakan untuk inisialisasi
- `window.livewireComponent` digunakan untuk akses Livewire dari external script
