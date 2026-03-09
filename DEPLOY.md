# Deploy Instructions

## SSH Login dan Deploy

```bash
# 1. Login ke server
ssh neobama@103.76.148.63 -p2222
# Password: L00kdown!~

# 2. Navigate ke project directory
cd /var/www/ftth

# 3. Pull latest changes
git pull origin main

# 4. Clear semua cache
rm -rf storage/framework/views/*
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear

# 5. Set permissions (jika perlu)
chmod 644 resources/views/filament/pages/map.blade.php
chmod 644 public/js/map.js
chown -R www-data:www-data storage/framework/views

# 6. Verify file sudah ter-update
wc -l resources/views/filament/pages/map.blade.php
# Harus menunjukkan sekitar 180 baris

# 7. Test
# Refresh browser dengan Ctrl+F5 atau Cmd+Shift+R
```

## Quick Deploy Script

Atau jalankan semua command sekaligus:

```bash
ssh neobama@103.76.148.63 -p2222 << 'EOF'
cd /var/www/ftth
git pull origin main
rm -rf storage/framework/views/*
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear
chmod 644 resources/views/filament/pages/map.blade.php
chmod 644 public/js/map.js
echo "Deploy selesai!"
EOF
```

## Troubleshooting

Jika map masih tidak muncul:

1. Check file sudah ter-pull:
```bash
head -5 resources/views/filament/pages/map.blade.php
tail -5 resources/views/filament/pages/map.blade.php
```

2. Check map.js exists:
```bash
ls -la public/js/map.js
```

3. Check browser console untuk errors

4. Clear browser cache (Ctrl+F5 atau Cmd+Shift+R)
