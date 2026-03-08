# Checklist Debug Multiple Root Elements Error

## 1. Check File Structure di Server
```bash
cd /var/www/ftth
cat resources/views/filament/pages/map.blade.php | head -1
cat resources/views/filament/pages/map.blade.php | tail -1
```

## 2. Check Apakah File Sudah Ter-Pull
```bash
cd /var/www/ftth
git status
git log --oneline -5
```

## 3. Clear Semua Cache
```bash
cd /var/www/ftth
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear
```

## 4. Check Apakah Ada Compiled Views
```bash
cd /var/www/ftth
ls -la storage/framework/views/
rm -rf storage/framework/views/*
```

## 5. Check File Permissions
```bash
cd /var/www/ftth
ls -la resources/views/filament/pages/map.blade.php
chmod 644 resources/views/filament/pages/map.blade.php
```

## 6. Check Apakah Ada Whitespace/Characters Tersembunyi
```bash
cd /var/www/ftth
head -1 resources/views/filament/pages/map.blade.php | od -c | head -3
tail -1 resources/views/filament/pages/map.blade.php | od -c | head -3
```

## 7. Check Livewire Version
```bash
cd /var/www/ftth
composer show livewire/livewire
```

## 8. Check Apakah Ada Multiple Root Elements
```bash
cd /var/www/ftth
# Count opening tags at root level
grep -c "^<" resources/views/filament/pages/map.blade.php
# Should be 1 (only <x-filament-panels::page>)
```

## 9. Check Laravel Log untuk Error Detail
```bash
cd /var/www/ftth
tail -100 storage/logs/laravel.log | grep -A 20 "MultipleRootElements"
```

## 10. Test dengan File Sederhana
Buat file test untuk memastikan Livewire bekerja:
```bash
cd /var/www/ftth
# Backup file asli
cp resources/views/filament/pages/map.blade.php resources/views/filament/pages/map.blade.php.backup
```

## 11. Check Apakah Masalahnya di @php Directive
File saat ini menggunakan `@php` di dalam template yang mungkin menyebabkan masalah.
