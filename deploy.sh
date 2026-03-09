#!/bin/bash
# Deploy script untuk FTTH Map

echo "🚀 Starting deployment..."

# SSH ke server dan jalankan deploy commands
sshpass -p 'L00kdown!~' ssh -p 2222 neobama@103.76.148.63 << 'EOF'
cd /var/www/ftth

echo "📥 Pulling latest changes..."
git pull origin main

echo "🧹 Clearing cache..."
rm -rf storage/framework/views/*
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear

echo "📝 Setting permissions..."
chmod 644 resources/views/filament/pages/map.blade.php
chmod 644 public/js/map.js
chown -R www-data:www-data storage/framework/views 2>/dev/null || true

echo "✅ Verifying files..."
wc -l resources/views/filament/pages/map.blade.php
ls -la public/js/map.js

echo "✅ Deployment completed!"
EOF

echo "🎉 Deployment finished!"
