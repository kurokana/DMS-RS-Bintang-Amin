# DMS Middleware - Central Hub & WebSocket Server

Sub-project `middleware` berfungsi sebagai pusat komunikasi backend murni (API-only) pada sistem Display Management System (DMS) RS Bintang Amin. Sub-project ini mengelola state device display (STB), ketersediaan kamar inap, jadwal operasi, dan antrean poliklinik, serta menyediakan server WebSocket real-time berbasis Laravel Reverb.

---

## 🛠️ Tech Stack & Domain

- **Framework:** Laravel 12 (API Mode)
- **WebSocket Server:** Laravel Reverb (Port `8080`)
- **Database:** MySQL (`dms_tahap1`)
- **Autentikasi:** Laravel Sanctum (Bearer Token)
- **Domain Local:** `http://middlewaredms.test`

---

## 🔑 Fitur Utama & Kritis

1. **Management Display Device & Mapping (`app/Services/DisplayDeviceService.php`)**:
   - Mendaftarkan ID, IP Address, dan lokasi fisik monitor STB.
   - Mengatur target mapping tayangan (kamar inap / ruang operasi) menggunakan relasi *Polymorphic* Eloquent.
   - *Catatan Penting:* Pengambilan data device wajib menggunakan eager load `with('mappings.target')`.
2. **Real-time Event Broadcasting (Laravel Reverb)**:
   - Membroadcast perubahan data secara instan ke client STB via WebSocket.
   - Event kritis: `DeviceStatusChanged`, `DisplayMappingUpdated`, `WardAvailabilityChanged`, `OperatingRoomStatusChanged`.
   - Semua event meng-override `broadcastAs()` agar channel broadcast menggunakan nama singkat (misal `.MappingUpdated`).
3. **STB Kiosk Endpoints**:
   - `GET /display/{displayId}/state`: Endpoint publik untuk STB mengambil state data tayangan.
   - `POST /display/{displayId}/heartbeat`: Endpoint publik untuk STB mengirim signal status aktif (tiap 10s).
4. **Heartbeat Job Check (`app/Jobs/CheckDisplayHeartbeatJob.php`)**:
   - Background job yang otomatis mengecek device tanpa heartbeat >30 detik dan mengubah statusnya menjadi `offline`.

---

## 🚀 Cara Jalankan di Development

1. **Instalasi Dependencies & Config**:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```
2. **Setup Database**:
   Sesuaikan `DB_DATABASE=dms_tahap1` di `.env`, lalu jalankan:
   ```bash
   php artisan migrate:fresh --seed
   ```
3. **Jalankan WebSocket Server (Reverb)**:
   ```bash
   php artisan reverb:start --port=8080
   ```
4. **Running Web Server**:
   Gunakan Laravel Herd (`http://middlewaredms.test`) atau jalankan perintah CLI:
   ```bash
   php artisan serve --port=8001
   ```

---

## 📚 Referensi Arsitektur & Deployment

- 🗺️ **[PROJECT_MAP.md](file:///d:/Intern/RSBA%20-%20Kerja%20Praktik/DMS/Tahap%201/PROJECT_MAP.md)**: Detail rute API & relasi antar sistem.
- 🚀 **[HANDOVER_RUNNING_GUIDE.md](file:///d:/Intern/RSBA%20-%20Kerja%20Praktik/DMS/Tahap%201/HANDOVER_RUNNING_GUIDE.md)**: Panduan konfigurasi Nginx & Supervisor di Production.
