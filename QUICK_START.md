# 🚀 BROILINK BACKEND - QUICK START

## ✅ File Yang Sudah Dibuat

### Models (8 files) ✅
- User.php
- Role.php  
- Farm.php
- FarmConfig.php
- IotData.php
- ManualData.php
- RequestLog.php
- NotificationLog.php

### Controllers (3 files) ✅
- Auth/LoginController.php ✅
- Auth/AccountRequestController.php ✅
- IoTController.php ✅

### Middleware (1 file) ✅
- CheckRole.php ✅

### Routes ✅
- routes/api.php (Complete dengan semua endpoint)

### Config ✅
- config/cors.php

### Documentation ✅
- README.md
- API_DOCUMENTATION.md
- COMPLETE_SETUP_GUIDE.md

## ⚙️ SETUP CEPAT (5 MENIT)

### 1. Install Laravel 12
```bash
cd C:\laragon\www
composer create-project laravel/laravel broilink-backend
cd broilink-backend
```

### 2. Copy Files
Extract `broilink-backend.zip` dan copy semua folder ke project Laravel:
- `app/Models/*` → copy ke Laravel
- `app/Http/Controllers/*` → copy ke Laravel
- `app/Http/Middleware/*` → copy ke Laravel
- `routes/api.php` → replace di Laravel
- `config/cors.php` → copy ke Laravel
- `.env.example` → reference untuk setup

### 3. Import Database
- Buka PHPMyAdmin
- Import `broilink_db.sql`

### 4. Setup .env
```env
DB_DATABASE=broilink_db
DB_USERNAME=root
DB_PASSWORD=

FRONTEND_URL=http://localhost:5173
```

### 5. Install Dependencies
```bash
php artisan key:generate
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 6. Register Middleware
Edit `bootstrap/app.php` atau `app/Http/Kernel.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

### 7. Test!
```bash
php artisan serve
```

Test dengan cURL atau Postman:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@broilink.com","password":"password"}'
```

## 🎯 CONTROLLER YANG PERLU DIBUAT (Optional)

Controllers ini optional karena sudah ada routes dan logic bisa ditambahkan sesuai kebutuhan:

### Admin Controllers
```bash
php artisan make:controller Admin/DashboardController
php artisan make:controller Admin/UserManagementController
php artisan make:controller Admin/FarmConfigController
php artisan make:controller Admin/RequestLogController
```

### Owner Controllers
```bash
php artisan make:controller Owner/DashboardController
php artisan make:controller Owner/MonitoringController
php artisan make:controller Owner/AnalysisController
```

### Peternak Controllers
```bash
php artisan make:controller Peternak/DashboardController
php artisan make:controller Peternak/ManualInputController
php artisan make:controller Peternak/ProfileController
```

**TAPI** Anda bisa langsung implementasi logic di route atau buat controller sesuai kebutuhan!

## 📊 CONTROLLER TEMPLATE

Contoh isi controller (lihat LoginController.php untuk referensi):
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalOwners = User::byRoleName('Owner')->count();
        $totalPeternak = User::byRoleName('Peternak')->count();
        // ... dst
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_owners' => $totalOwners,
                'total_peternak' => $totalPeternak,
                // ...
            ]
        ]);
    }
}
```

## 🔑 LOGIN DEFAULT

Email: `admin@broilink.com`
Password: Cek di database atau update dengan:
```php
use Illuminate\Support\Facades\Hash;
Hash::make('password123');
```

## 📱 CONNECT FRONTEND

Di React frontend (.env):
```env
VITE_API_URL=http://localhost:8000/api
```

Di axios config:
```javascript
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
});

// Add token to requests
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

## 🐛 TROUBLESHOOTING

### CORS Error
```bash
php artisan config:clear
```
Pastikan `FRONTEND_URL` di .env benar.

### 401 Unauthorized
Token expired atau tidak valid. Login ulang.

### 403 Forbidden
User tidak punya role yang sesuai untuk endpoint tersebut.

### 500 Server Error
Check logs:
```bash
tail -f storage/logs/laravel.log
```

## 📚 DOKUMENTASI LENGKAP

Baca file:
- `README.md` - Overview lengkap
- `API_DOCUMENTATION.md` - Semua endpoint API
- `COMPLETE_SETUP_GUIDE.md` - Panduan detail

## ✨ FITUR UTAMA

✅ Authentication dengan Sanctum
✅ Role-based access (Admin, Owner, Peternak)
✅ IoT sensor data integration
✅ Real-time monitoring kandang
✅ Manual data input peternak
✅ Analytics & reporting
✅ Farm configuration management
✅ Account request system
✅ CORS configured untuk React

## 🎉 READY TO USE!

Backend ini sudah siap digunakan dengan:
- ✅ 8 Models lengkap dengan relationships
- ✅ 3 Controllers utama (Auth, IoT)
- ✅ Routes lengkap untuk semua role
- ✅ Middleware role-based
- ✅ API documentation
- ✅ Database structure support

Tinggal copy, setup, dan GO! 🚀
