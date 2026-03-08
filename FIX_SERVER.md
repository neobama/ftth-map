# Fix Server - File Tidak Ter-Pull dengan Benar

## Masalah
File `map.blade.php` di server hanya berisi wrapper kosong, padahal di local memiliki 618 baris konten lengkap.

## Solusi

### 1. Check Branch di Server
```bash
cd /var/www/ftth
git branch
# Pastikan di branch master atau main
```

### 2. Force Pull dari Remote
```bash
cd /var/www/ftth
git fetch origin
git reset --hard origin/main
# atau
git reset --hard origin/master
```

### 3. Check Apakah File Ter-Track
```bash
cd /var/www/ftth
git ls-files | grep map.blade.php
```

### 4. Jika File Tidak Ter-Track, Add Manual
```bash
cd /var/www/ftth
git add resources/views/filament/pages/map.blade.php
git commit -m "Add map.blade.php"
```

### 5. Atau Copy Manual dari Local
Jika git pull tidak bekerja, copy file manual:
```bash
# Di local machine
scp resources/views/filament/pages/map.blade.php neobama@website-blackjack:/var/www/ftth/resources/views/filament/pages/map.blade.php

# Atau di server, download dari GitHub
cd /var/www/ftth
wget -O resources/views/filament/pages/map.blade.php https://raw.githubusercontent.com/neobama/ftth-map/main/resources/views/filament/pages/map.blade.php
```

### 6. Setelah File Ter-Fix, Clear Cache
```bash
cd /var/www/ftth
rm -rf storage/framework/views/*
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

### 7. Verify File
```bash
cd /var/www/ftth
wc -l resources/views/filament/pages/map.blade.php
# Harus menunjukkan sekitar 618 baris
head -5 resources/views/filament/pages/map.blade.php
# Harus menunjukkan konten lengkap
```
