# Instruksi Fix Multiple Root Elements di Server

## Masalah
Error: `Livewire only supports one HTML element per component. Multiple root elements detected`

## Penyebab
File di server kemungkinan belum ter-pull dengan benar atau ada cache lama.

## Langkah Perbaikan

### 1. Check File di Server
```bash
cd /var/www/ftth

# Check apakah file sudah ter-pull
git status
git log --oneline -3

# Check jumlah baris file
wc -l resources/views/filament/pages/map.blade.php
# Harus menunjukkan sekitar 621 baris, BUKAN 2 baris!

# Check struktur file
head -1 resources/views/filament/pages/map.blade.php
tail -1 resources/views/filament/pages/map.blade.php
```

### 2. Jika File Hanya 2 Baris (Kosong)
```bash
cd /var/www/ftth

# Force pull dari GitHub
git fetch origin
git reset --hard origin/main

# Verify file sudah ter-pull
wc -l resources/views/filament/pages/map.blade.php
# Harus menunjukkan sekitar 621 baris
```

### 3. Clear SEMUA Cache
```bash
cd /var/www/ftth

# Hapus compiled views
rm -rf storage/framework/views/*

# Clear semua cache
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear
```

### 4. Verify Struktur File
```bash
cd /var/www/ftth

# Upload verify_structure.php ke server, lalu jalankan:
php verify_structure.php

# Atau manual check:
head -1 resources/views/filament/pages/map.blade.php
# Harus: <x-filament-panels::page><div class="relative...

tail -1 resources/views/filament/pages/map.blade.php
# Harus: </div></x-filament-panels::page>
```

### 5. Set Permissions
```bash
cd /var/www/ftth
chmod 644 resources/views/filament/pages/map.blade.php
chown -R www-data:www-data storage/framework/views
```

### 6. Test
- Refresh browser dengan Ctrl+F5 atau Cmd+Shift+R
- Error seharusnya sudah hilang

## Jika Masih Error

### Check File yang Sebenarnya di Server
```bash
cd /var/www/ftth
cat resources/views/filament/pages/map.blade.php | head -5
cat resources/views/filament/pages/map.blade.php | tail -5
```

### Download Langsung dari GitHub (Alternatif)
```bash
cd /var/www/ftth
wget -O resources/views/filament/pages/map.blade.php https://raw.githubusercontent.com/neobama/ftth-map/main/resources/views/filament/pages/map.blade.php

# Verify
wc -l resources/views/filament/pages/map.blade.php
# Harus sekitar 621 baris
```

### Check Laravel Log
```bash
cd /var/www/ftth
tail -50 storage/logs/laravel.log | grep -A 10 "MultipleRootElements"
```

## Catatan Penting

1. **File harus memiliki ~621 baris**, bukan 2 baris
2. **Clear semua cache** setelah pull
3. **Pastikan file ter-pull dari branch `main`**, bukan `master`
4. **Check branch di server**: `git branch` - harus di `main` atau `master`
