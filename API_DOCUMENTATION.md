# BROILINK API DOCUMENTATION

Base URL: `http://localhost:8000/api` atau `http://broilink-backend.test/api`

## Authentication

Gunakan Laravel Sanctum token-based authentication.
Setelah login, include token di header:
```
Authorization: Bearer {your-token-here}
```

---

## 🔓 PUBLIC ENDPOINTS

### 1. Login
**POST** `/login`

Request:
```json
{
  "email": "admin@broilink.com",
  "password": "password"
}
```

Response Success (200):
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "user_id": 1,
      "username": "admin_utama",
      "name": "Admin Utama Broilink",
      "email": "admin@broilink.com",
      "role": "Admin",
      "role_id": 1
    },
    "farms": [],
    "token": "1|abc123..."
  }
}
```

### 2. Request Account (Guest)
**POST** `/request-account`

Request:
```json
{
  "name": "John Doe",
  "phone": "08123456789",
  "description": "Saya ingin mendaftar sebagai peternak"
}
```

Response Success (201):
```json
{
  "success": true,
  "message": "Permintaan akun berhasil dikirim...",
  "data": {
    "request_id": 1,
    "status": "menunggu"
  }
}
```

### 3. Check Request Status
**GET** `/request-account/status?phone=08123456789`

Response Success (200):
```json
{
  "success": true,
  "data": {
    "request_id": 1,
    "name": "John Doe",
    "phone": "08123456789",
    "status": "menunggu",
    "status_color": "yellow",
    "sent_time": "07 Nov 2025 10:30"
  }
}
```

### 4. Post IoT Sensor Data
**POST** `/iot/sensor-data`

Request:
```json
{
  "farm_id": 1,
  "temperature": 30.5,
  "humidity": 65.0,
  "ammonia": 15.2
}
```

Response Success (201):
```json
{
  "success": true,
  "message": "Data sensor berhasil disimpan",
  "data": {
    "id": 123,
    "farm_id": 1,
    "timestamp": "2025-11-07 10:30:00",
    "temperature": 30.5,
    "humidity": 65.0,
    "ammonia": 15.2,
    "farm_status": "normal",
    "is_critical": false,
    "parameter_status": {
      "temperature": "normal",
      "humidity": "normal",
      "ammonia": "normal"
    }
  }
}
```

---

## 🔒 PROTECTED ENDPOINTS (Require Authentication)

### Common Endpoints

#### Logout
**POST** `/logout`
Headers: `Authorization: Bearer {token}`

#### Get Current User
**GET** `/me`
Headers: `Authorization: Bearer {token}`

#### Get Farm Status
**GET** `/farms/{id}/status`
Headers: `Authorization: Bearer {token}`

---

## 👨‍💼 ADMIN ENDPOINTS (Role: Admin)

Prefix: `/admin`
Headers: `Authorization: Bearer {token}`

### Dashboard

#### Get Dashboard Stats
**GET** `/admin/dashboard`

Response:
```json
{
  "success": true,
  "data": {
    "total_users": 25,
    "total_owners": 10,
    "total_peternak": 15,
    "total_farms": 12,
    "pending_requests": 3,
    "recent_requests": [...]
  }
}
```

### User Management

#### List All Users
**GET** `/admin/users?role=Owner&status=active&search=john`

#### Create User
**POST** `/admin/users`
```json
{
  "username": "peternak001",
  "email": "peternak@example.com",
  "password": "password123",
  "name": "Peternak Satu",
  "role_id": 3,
  "phone_number": "08123456789",
  "status": "active"
}
```

#### Get User Detail
**GET** `/admin/users/{id}`

#### Update User
**PUT** `/admin/users/{id}`

#### Delete User
**DELETE** `/admin/users/{id}`

### Farm Management

#### List All Farms
**GET** `/admin/farms`

#### Create Farm
**POST** `/admin/farms`
```json
{
  "owner_id": 2,
  "farm_name": "Kandang A",
  "location": "Yogyakarta",
  "initial_population": 5000,
  "initial_weight": 0.04,
  "farm_area": 200
}
```

#### Update Farm
**PUT** `/admin/farms/{id}`

#### Delete Farm
**DELETE** `/admin/farms/{id}`

### Farm Configuration

#### Get Farm Config
**GET** `/admin/farms/{id}/config`

#### Update Farm Config
**PUT** `/admin/farms/{id}/config`
```json
{
  "suhu_normal_min": 28.0,
  "suhu_normal_max": 32.0,
  "suhu_kritis_rendah": 26.0,
  "suhu_kritis_tinggi": 35.0,
  "kelembapan_normal_min": 60.0,
  "kelembapan_normal_max": 70.0,
  "amonia_max": 20.0,
  "amonia_kritis": 30.0,
  "pakan_min": 50.0,
  "minum_min": 100.0
}
```

### Request Log

#### List All Requests
**GET** `/admin/requests?status=menunggu&type=akun_baru`

#### Update Request Status
**PUT** `/admin/requests/{id}/status`
```json
{
  "status": "selesai"
}
```

---

## 🏠 OWNER ENDPOINTS (Role: Owner)

Prefix: `/owner`
Headers: `Authorization: Bearer {token}`

### Dashboard

#### Get Owner Dashboard
**GET** `/owner/dashboard`

Response:
```json
{
  "success": true,
  "data": {
    "total_farms": 3,
    "farms_status": {
      "normal": 2,
      "waspada": 1,
      "bahaya": 0
    },
    "recent_reports": [...],
    "alerts": [...]
  }
}
```

#### Get Owner's Farms
**GET** `/owner/farms`

#### Get Farm Detail
**GET** `/owner/farms/{id}`

### Monitoring

#### Get Real-time Monitoring
**GET** `/owner/farms/{id}/monitoring`

Response:
```json
{
  "success": true,
  "data": {
    "farm": {...},
    "current_status": "normal",
    "latest_sensor": {
      "temperature": 30.5,
      "humidity": 65.0,
      "ammonia": 15.2,
      "timestamp": "2025-11-07 10:30:00"
    },
    "config": {...}
  }
}
```

#### Get Latest Sensor Data
**GET** `/owner/farms/{id}/latest-sensor`

#### Get Sensor History
**GET** `/owner/farms/{id}/sensor-history?period=1day`

Periods: `1hour`, `24hours`, `7days`, `30days`

### Analytics

#### Get Farm Analytics
**GET** `/owner/farms/{id}/analytics?period=7days`

Response:
```json
{
  "success": true,
  "data": {
    "sensor_trends": [...],
    "manual_data_summary": {
      "total_pakan": 350.5,
      "total_air": 700.2,
      "total_kematian": 15,
      "mortality_rate": 0.3,
      "fcr": 1.65
    },
    "growth_chart": [...]
  }
}
```

#### Get Manual Reports
**GET** `/owner/farms/{id}/reports?start_date=2025-11-01&end_date=2025-11-07`

---

## 🧑‍🌾 PETERNAK ENDPOINTS (Role: Peternak)

Prefix: `/peternak`
Headers: `Authorization: Bearer {token}`

### Dashboard

#### Get Peternak Dashboard
**GET** `/peternak/dashboard`

#### Get Assigned Farm
**GET** `/peternak/farm`

### Manual Data Input

#### List My Reports
**GET** `/peternak/manual-data?start_date=2025-11-01`

#### Submit Daily Report
**POST** `/peternak/manual-data`
```json
{
  "report_date": "2025-11-07",
  "konsumsi_pakan": 52.5,
  "konsumsi_air": 105.0,
  "jumlah_kematian": 2
}
```

Response:
```json
{
  "success": true,
  "message": "Laporan berhasil disimpan",
  "data": {
    "id": 45,
    "farm_id": 1,
    "report_date": "2025-11-07",
    "konsumsi_pakan": 52.5,
    "konsumsi_air": 105.0,
    "jumlah_kematian": 2,
    "warnings": []
  }
}
```

#### Get Report Detail
**GET** `/peternak/manual-data/{id}`

#### Update Report
**PUT** `/peternak/manual-data/{id}`

### Profile

#### Get My Profile
**GET** `/peternak/profile`

#### Update Profile
**PUT** `/peternak/profile`
```json
{
  "phone_number": "08123456789",
  "email": "newemail@example.com"
}
```

#### Update Profile Photo
**POST** `/peternak/profile/photo`
Form-data:
- `photo`: (file) image file

---

## 📊 Response Status Codes

- `200` OK - Request berhasil
- `201` Created - Resource berhasil dibuat
- `400` Bad Request - Request tidak valid
- `401` Unauthorized - Belum login
- `403` Forbidden - Tidak punya akses
- `404` Not Found - Resource tidak ditemukan
- `422` Validation Error - Data tidak valid
- `500` Server Error - Error di server

## 🔥 Farm Status Values

- `normal` - Semua parameter normal (green)
- `waspada` - Ada parameter mendekati batas (yellow)
- `bahaya` - Ada parameter critical (red)

## 📝 Notes

1. Semua timestamp menggunakan format ISO 8601
2. Semua endpoint protected memerlukan Bearer token
3. Validasi error akan return status 422 dengan detail error
4. File upload (foto profile) max 2MB
5. IoT endpoint bisa ditambahkan API key authentication untuk keamanan
