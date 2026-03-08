# FTTH Network Management System

Aplikasi web mapping untuk manajemen jaringan FTTH (Fiber-To-The-Home) menggunakan Laravel Filament. Aplikasi ini digunakan oleh ISP lokal untuk memetakan seluruh infrastruktur jaringan fiber optik mereka secara visual di atas peta Google Maps interaktif.

## Fitur Utama

### 1. Dashboard
- Statistik ringkasan: Total Router, Total ODP, Total Client
- Jumlah client online vs offline
- Persentase client online

### 2. Peta Interaktif
- Google Maps full-screen dengan tema gelap
- Toolbar dengan tombol: Add POP, Add ODP, Add Kabel, Add Tiang
- Visualisasi marker untuk semua komponen jaringan

### 3. Router/POP Management
- Tambah router dengan koordinat dari peta
- Form: Nama, IP Address, Username, Password, Port
- Marker hijau dengan label "POP"
- Koneksi API untuk check status PPPoE client

### 4. ODP Management
- Tambah ODP dengan parent router
- Form: Nama, Router Parent, Kapasitas Port
- Marker dinamis berdasarkan kapasitas:
  - Biru: < 50%
  - Kuning: 50-80%
  - Merah: > 80%
- Panel detail dengan informasi lengkap

### 5. Cable Management
- Tiga tipe jalur:
  - **Point-to-Point**: Garis lurus
  - **Ikut Jalan**: Mengikuti rute jalan (Google Directions API)
  - **Manual**: User klik waypoint secara manual
- Auto-calculate panjang kabel (Haversine formula)
- Edit kabel yang sudah ada

### 6. Client Management
- Tambah client dari panel detail ODP
- Form: Nama, Alamat, PPPoE Username/Password, SN ONT, Paket Layanan
- Marker dengan animasi:
  - Hijau pulse: Online
  - Merah: Offline
- Kabel dari ODP ke client dengan animasi
- InfoWindow dengan ringkasan data

### 7. Tiang (Pole) Management
- Tambah tiang di sepanjang kabel
- Auto-snap ke titik terdekat di jalur kabel
- Marker pin coklat

## Instalasi

### Requirements
- PHP 8.1+
- Composer
- Node.js & NPM
- Database (MySQL/PostgreSQL/SQLite)

### Setup

1. Clone repository
```bash
git clone <repository-url>
cd ftth-map
```

2. Install dependencies
```bash
composer install
npm install
```

3. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ftth_map
DB_USERNAME=root
DB_PASSWORD=

GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

5. Run migrations
```bash
php artisan migrate
```

6. Create admin user
```bash
php artisan make:filament-user
```

7. Build assets
```bash
npm run build
```

8. Start server
```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000/admin`

## Struktur Database

### Tables
- `routers`: Data router/POP
- `odps`: Data ODP (Optical Distribution Point)
- `cables`: Data kabel dengan waypoints
- `clients`: Data client dengan status online/offline
- `tiangs`: Data tiang di sepanjang kabel

## API Service

### RouterApiService
Service untuk koneksi ke router MikroTik dan check status PPPoE client.

**Note**: Implementasi saat ini adalah placeholder. Untuk production, gunakan library MikroTik API yang proper seperti:
- RouterOS API client library
- Atau implementasi RouterOS API protocol langsung

### Penggunaan
```php
use App\Services\RouterApiService;

$service = new RouterApiService();
$isOnline = $service->checkClientStatus($router, $client);
```

## Development Notes

### Google Maps API
- Pastikan API key sudah dikonfigurasi di `.env`
- Enable Google Maps JavaScript API di Google Cloud Console
- Enable Directions API untuk fitur "Ikut Jalan"

### MikroTik Router API
- Default port: 8728
- Implementasi API connection masih placeholder
- Perlu library atau implementasi RouterOS API protocol

### Status Check Client
- Saat ini menggunakan mock data untuk development
- Untuk production, implementasikan koneksi ke RouterOS API
- Bisa dijadwalkan dengan Laravel Scheduler untuk check otomatis

## TODO

- [ ] Implementasi lengkap RouterOS API connection
- [ ] Auto-check client status dengan scheduled job
- [ ] Export/Import data
- [ ] Reporting dan analytics
- [ ] Multi-user dengan permission
- [ ] Audit log
- [ ] Backup/restore data

## License

MIT License
