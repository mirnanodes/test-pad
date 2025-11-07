# BroiLink Backend - Laravel 12

Backend API untuk sistem monitoring peternakan ayam broiler dengan IoT integration.
Backend ini sudah disesuaikan dengan database `broilink_db.sql` yang sudah ada.

## 🚀 Tech Stack
- Laravel 12
- PHP 8.2+
- MySQL 8.0.30
- Laravel Sanctum (API Authentication)

## 📋 Struktur Database Existing

Database `broilink_db` memiliki tabel:
- **users** - Data pengguna (admin, owner, peternak)
- **roles** - Roles sistem (Admin, Owner, Peternak)
- **farms** - Data kandang
- **farm_config** - Konfigurasi parameter kandang
- **iot_data** - Data sensor real-time dari IoT
- **manual_data** - Data input manual peternak
- **request_log** - Log permintaan pengguna
- **notification_log** - Log notifikasi sistem

## 🛠️ Setup Instructions

### 1. Install Laravel di Laragon

```bash
# Buka Laragon Terminal
cd C:\laragon\www

# Install Laravel 12 dengan Composer
composer create-project laravel/laravel broilink-backend

# Masuk ke folder project
cd broilink-backend
```

### 2. Import Database

1. Buka PHPMyAdmin (http://localhost/phpmyadmin)
2. Buat database baru: `broilink_db` (atau database sudah ada)
3. Import file `broilink_db.sql`

### 3. Konfigurasi Environment

Edit file `.env`:

```env
APP_NAME=BroiLink
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://broilink-backend.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=broilink_db
DB_USERNAME=root
DB_PASSWORD=

# CORS untuk React Frontend
FRONTEND_URL=http://localhost:5173

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
SESSION_DRIVER=database
```

### 4. Install & Setup Dependencies

```bash
# Generate Application Key
php artisan key:generate

# Install Laravel Sanctum
composer require laravel/sanctum

# Publish Sanctum config
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Install CORS package (jika belum include di Laravel 12)
composer require fruitcake/laravel-cors

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 5. Copy File Backend ke Laravel

Dari folder `broilink-backend` yang saya buat:

```bash
# Copy Models
app/Models/*.php → broilink-backend/app/Models/

# Copy Controllers
app/Http/Controllers/ → broilink-backend/app/Http/Controllers/

# Copy Middleware
app/Http/Middleware/*.php → broilink-backend/app/Http/Middleware/

# Copy Routes
routes/api.php → broilink-backend/routes/api.php

# Copy Config
config/cors.php → broilink-backend/config/cors.php
```

### 6. Update Kernel.php

Edit `app/Http/Kernel.php` atau `bootstrap/app.php` (Laravel 11+):

Tambahkan middleware:
```php
'auth:sanctum',
'role' => \App\Http\Middleware\CheckRole::class,
```

### 7. Jalankan Server

```bash
php artisan serve
# Atau gunakan Laragon (otomatis di http://broilink-backend.test)
```

### 8. Test API

Default admin account:
- Email: `admin@broilink.com`
- Password: `password` (sesuaikan di database)

```bash
# Test login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@broilink.com","password":"password"}'
```

## 📁 Struktur Backend

```
broilink-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── AccountRequestController.php
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── UserManagementController.php
│   │   │   │   ├── FarmConfigController.php
│   │   │   │   └── RequestLogController.php
│   │   │   ├── Owner/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── MonitoringController.php
│   │   │   │   └── AnalysisController.php
│   │   │   ├── Peternak/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ManualInputController.php
│   │   │   │   └── ProfileController.php
│   │   │   └── IoTController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Farm.php
│   │   ├── FarmConfig.php
│   │   ├── IotData.php
│   │   ├── ManualData.php
│   │   ├── RequestLog.php
│   │   └── NotificationLog.php
│   └── Services/
│       ├── FarmStatusService.php
│       └── AnalyticsService.php
├── config/
│   ├── cors.php
│   └── sanctum.php
├── routes/
│   └── api.php
└── .env
```

## 🔐 User Credentials

Default admin sudah ada di database:
- Username: `admin_utama`
- Email: `admin@broilink.com`
- Password: `password` (hash sudah ada, atau update sesuai kebutuhan)

## 🌐 API Endpoints

### Authentication
```
POST   /api/login                    # Login
POST   /api/logout                   # Logout
GET    /api/me                       # Get current user
POST   /api/request-account          # Request akun (guest)
GET    /api/request-account/status   # Check request status
```

### Admin Routes (prefix: /api/admin) [Role: Admin]
```
GET    /dashboard                    # Dashboard stats
GET    /users                        # List all users
POST   /users                        # Create user (owner/peternak)
GET    /users/{id}                   # Get user detail
PUT    /users/{id}                   # Update user
DELETE /users/{id}                   # Delete user

GET    /farms                        # List all farms
POST   /farms                        # Create farm
GET    /farms/{id}                   # Get farm detail
PUT    /farms/{id}                   # Update farm
DELETE /farms/{id}                   # Delete farm

GET    /farms/{id}/config            # Get farm config
PUT    /farms/{id}/config            # Update farm config

GET    /requests                     # List all requests
PUT    /requests/{id}/status         # Update request status
```

### Owner Routes (prefix: /api/owner) [Role: Owner]
```
GET    /dashboard                    # Dashboard overview
GET    /farms                        # List farms owned
GET    /farms/{id}/monitoring        # Real-time monitoring
GET    /farms/{id}/analytics         # Analytics & graphs
GET    /farms/{id}/reports           # Manual reports
GET    /farms/{id}/sensor-history    # Sensor data history
```

### Peternak Routes (prefix: /api/peternak) [Role: Peternak]
```
GET    /dashboard                    # Dashboard monitoring
GET    /farm                         # Farm detail (assigned farm)
POST   /manual-data                  # Submit daily report
GET    /manual-data                  # List own reports
GET    /manual-data/{id}             # Get report detail
GET    /profile                      # View profile
PUT    /profile                      # Update profile (phone, email)
POST   /profile/photo                # Update photo only
```

### IoT Endpoint (Public/Secured with API Key)
```
POST   /api/iot/sensor-data          # IoT device posts sensor data
```

### Monitoring (Real-time)
```
GET    /api/farms/{id}/status        # Get farm status (normal/waspada/bahaya)
GET    /api/farms/{id}/latest-sensor # Latest sensor readings
```

## 📊 Farm Status Logic

Status kandang ditentukan berdasarkan parameter sensor vs konfigurasi:

**NORMAL**:
- Semua parameter dalam range normal

**WASPADA**:
- 1-2 parameter mendekati batas kritis
- Amonia > batas_max (belum kritis)

**BAHAYA**:
- Ada parameter di luar batas kritis
- Suhu < kritis_rendah atau > kritis_tinggi
- Kelembapan < kritis_rendah atau > kritis_tinggi
- Amonia > batas_kritis

## 🔧 Farm Config Parameters

Setiap farm memiliki konfigurasi dengan `parameter_name` dan `value`:

```
suhu_normal_min         = 28.0
suhu_normal_max         = 32.0
suhu_kritis_rendah      = 26.0
suhu_kritis_tinggi      = 35.0
kelembapan_normal_min   = 60.0
kelembapan_normal_max   = 70.0
kelembapan_kritis_rendah= 50.0
kelembapan_kritis_tinggi= 80.0
amonia_max              = 20.0
amonia_kritis           = 30.0
pakan_min               = 50.0
minum_min               = 100.0
pertumbuhan_mingguan_min= 0.5
target_bobot            = 2.0
```

## 📈 Analytics Features

### Grafik yang tersedia:
1. **Suhu** - Time series (1 jam - 1 bulan)
2. **Kelembapan** - Time series (1 jam - 1 bulan)
3. **Amonia** - Time series (1 jam - 1 bulan)
4. **Konsumsi Pakan** - Daily/Weekly/Monthly
5. **Konsumsi Air** - Daily/Weekly/Monthly
6. **Bobot Ayam** - Tracking pertumbuhan
7. **Kematian** - Tracking mortalitas

### Perhitungan:
- **FCR (Feed Conversion Ratio)**: Total pakan / Pertumbuhan bobot
- **Mortality Rate**: (Total kematian / Populasi awal) × 100%
- **Growth Rate**: (Bobot akhir - Bobot awal) / Jumlah hari

## 🔒 Security

1. **Authentication**: Laravel Sanctum token-based
2. **Authorization**: Role-based middleware
3. **CORS**: Configured for React frontend
4. **Input Validation**: All inputs validated
5. **SQL Injection**: Protected by Eloquent ORM

## 🐛 Troubleshooting

### CORS Error
```bash
php artisan config:clear
```
Pastikan `FRONTEND_URL` di `.env` sesuai dengan URL React.

### Token Mismatch
```bash
php artisan config:clear
php artisan cache:clear
```

### Database Connection Error
Pastikan MySQL service di Laragon running dan credentials di `.env` benar.

### 500 Internal Server Error
Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

## 📝 Notes

1. Password admin default ada di SQL, atau buat baru:
```php
bcrypt('password123')
```

2. Untuk testing IoT endpoint, gunakan tools seperti Postman:
```json
POST /api/iot/sensor-data
{
  "farm_id": 1,
  "temperature": 30.5,
  "humidity": 65.0,
  "ammonia": 15.2
}
```

3. Frontend React harus connect ke backend API:
```javascript
// React .env
VITE_API_URL=http://localhost:8000/api
```

## 🚀 Next Steps

1. ✅ Setup Laravel di Laragon
2. ✅ Import database SQL
3. ✅ Copy semua file backend
4. ✅ Configure `.env`
5. ✅ Test API dengan Postman
6. ✅ Connect React frontend
7. ✅ Setup IoT device endpoint

## 📞 Support

Default admin email: admin@broilink.com
WhatsApp notifikasi bisa diintegrasikan dengan API seperti Fonnte atau Wablas.
