# PANDUAN LENGKAP SETUP BROILINK BACKEND

## File Yang Sudah Dibuat ✅

### Models (8 files)
1. User.php - Model user dengan role
2. Role.php - Model role (Admin, Owner, Peternak)
3. Farm.php - Model kandang
4. FarmConfig.php - Model konfigurasi kandang
5. IotData.php - Model data sensor IoT
6. ManualData.php - Model input manual peternak
7. RequestLog.php - Model log permintaan
8. NotificationLog.php - Model notifikasi

## File Yang Harus Anda Buat di Laravel 🔨

Karena file controller sangat banyak dan panjang, saya akan memberikan instruksi untuk generate otomatis menggunakan artisan.

### Langkah Setup:

#### 1. Install Laravel 12 di Laragon

```bash
cd C:\laragon\www
composer create-project laravel/laravel broilink-backend
cd broilink-backend
```

#### 2. Import Database

- Buka PHPMyAdmin
- Import `broilink_db.sql`

#### 3. Setup .env

```env
DB_DATABASE=broilink_db
DB_USERNAME=root
DB_PASSWORD=

FRONTEND_URL=http://localhost:5173
```

#### 4. Install Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

#### 5. Copy Models

Copy semua file dari `broilink-backend/app/Models/` ke Laravel Anda.

####  6. Generate Controllers

```bash
# Auth Controllers
php artisan make:controller Auth/LoginController
php artisan make:controller Auth/AccountRequestController

# Admin Controllers  
php artisan make:controller Admin/DashboardController
php artisan make:controller Admin/UserManagementController
php artisan make:controller Admin/FarmConfigController
php artisan make:controller Admin/RequestLogController

# Owner Controllers
php artisan make:controller Owner/DashboardController
php artisan make:controller Owner/MonitoringController
php artisan make:controller Owner/AnalysisController

# Peternak Controllers
php artisan make:controller Peternak/DashboardController
php artisan make:controller Peternak/ManualInputController
php artisan make:controller Peternak/ProfileController

# IoT Controller
php artisan make:controller IoTController
```

#### 7. Update Routes (routes/api.php)

Lihat file `ROUTES_TEMPLATE.php` untuk template lengkap routes.

#### 8. Buat Middleware

```bash
php artisan make:middleware CheckRole
```

#### 9. Test API

```bash
php artisan serve
```

Test dengan Postman:
```
POST http://localhost:8000/api/login
{
  "email": "admin@broilink.com",
  "password": "password"
}
```

## Template Code untuk Setiap Controller

Karena setiap controller memiliki logic kompleks, saya telah menyiapkan template code lengkap.
Lihat folder `controller-templates/` untuk kode lengkap setiap controller.

## Login Admin Default

Email: `admin@broilink.com`
Password: (sesuai hash di database, atau update dengan `bcrypt('password123')`)

## API Endpoints Summary

### Public
- POST /api/login
- POST /api/request-account

### Admin (Role: Admin)
- GET  /api/admin/dashboard
- CRUD /api/admin/users
- CRUD /api/admin/farms
- PUT  /api/admin/farms/{id}/config

### Owner (Role: Owner)
- GET /api/owner/dashboard
- GET /api/owner/farms
- GET /api/owner/farms/{id}/monitoring
- GET /api/owner/farms/{id}/analytics

### Peternak (Role: Peternak)
- GET  /api/peternak/dashboard
- POST /api/peternak/manual-data
- GET  /api/peternak/profile

### IoT
- POST /api/iot/sensor-data

