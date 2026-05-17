## **SUBMITTED BY (GROUP OF 5 STUDENTS):**
| Student Name          |
| :-------------------- |
| **Moeed S-E** |
| **Haseeb** |
| **Mohsin** |
| **Abdul Hadi** |
| **Muhammad Shahmeer** | 

---

## 📂 Core Developer Architecture & File Map
To aid in editing or reviewing, use the absolute clickable directory map below to navigate the codebase:
* **Ingestion & Anomaly Engine:** `MetricController.php`
* **Mock Simulation Controller:** `AlertController.php`
* **Scheduled Telemetry Cron:** `IngestTelemetry.php`
* **Interactive Frontend Dashboard:** `dashboard.blade.php`
* **Vanilla JavaScript Actions:** `app.js`

---

## 🔬 Problem-Solving Characteristics & Depth Analysis
1. **Depth of Knowledge Required:**
   NetGuard bypasses generic, standard CRUD architectures by utilizing statistical window aggregation. It requires understanding databases, rolling windows in SQL (`id < current` filters), API integration lifecycle, real-time charting, and secure state handling (CSRF protection in vanilla JavaScript).
2. **Depth of Analysis Required:**
   Detecting security threat vectors requires conceptual models. While a simple static limit catches standard overflows, a slow-building memory leak or sudden volumetric DDoS requires analyzing the delta of activity relative to standard baselines. 
   
   The mathematical ratio comparison models real-world production setups similar to monitoring platforms like Prometheus Alertmanager and AWS CloudWatch:
   $$x > 4\mu$$
3. **Consequences & Application:**
   An improperly calibrated monitoring system generates false alarms (alert fatigue) or misses massive outages. Implemented as an asynchronous event logger, NetGuard models real industrial network environments, displaying high applicability in modern infrastructure operations.

---

## 📥 Clone & Run Guide

The system is designed to run on local environments with zero external database server requirements using **SQLite**. Follow the sequential steps below to get the project up and running.

### 1. Clone the Repository
```bash
git clone https://github.com/Moeed-S-E/NetGuard-Simulation.git

```

### 2. Move Into Project Directory

```bash
cd NetGuard-Simulation

```

### 3. Install PHP Dependencies

```bash
composer install

```

### 4. Install Frontend Dependencies

```bash
npm install

```

### 5. Create Environment File

#### Linux / macOS

```bash
cp .env.example .env

```

#### Windows (CMD)

```cmd
copy .env.example .env

```

### 6. Generate Laravel Application Key

```bash
php artisan key:generate

```

### 7. Create SQLite Database File

#### Linux / macOS

```bash
touch database/database.sqlite

```

#### Windows (CMD)

```cmd
type nul > database\database.sqlite

```

### 8. Configure `.env`

Open `.env` and update the database section:

```env
DB_CONNECTION=sqlite
# Remove/comment other DB lines if needed

```

### 9. Run Migrations & Seed Database

```bash
php artisan migrate:fresh --seed

```

This will:

* Create all tables
* Insert baseline telemetry data
* Seed nodes and alerts

### 10. Start Laravel Server (Terminal 1)

```bash
php artisan serve

```

Laravel will start on: `http://127.0.0.1:8000`

### 11. Start Telemetry Scheduler (Terminal 2)

```bash
php artisan schedule:work

```

This continuously generates simulated telemetry metrics in the background.

### 12. Build Frontend Assets (Terminal 3)

#### Development Mode

```bash
npm run dev

```

#### Production Build

```bash
npm run build

```

---

## ✅ Functional Demonstration

After opening the dashboard:

* Real-time charts update automatically
* Node cards refresh every 3 seconds
* Alerts appear dynamically
* Threat simulation buttons trigger anomalies

**Try simulating live issues:**

* ⚡ Simulate DDoS
* ⚠ Memory Leak
* ▲ CPU Spike

Then, resolve incidents using the UI **RESOLVE** button.

---

## 🛠️ Common Fixes

### Vite Manifest Error

Run either of the following commands to rebuild assets:

```bash
npm run build

```

or:

```bash
npm run dev

```

### SQLite Driver Missing

Enable the SQLite extensions in your system's active `php.ini` file:

```ini
extension=pdo_sqlite
extension=sqlite3

```

### Permission Errors (Linux)

If the application cannot write logs or cache files, grant the necessary permissions:

```bash
chmod -R 775 storage bootstrap/cache

```

---

## 📦 Tech Stack

* **Backend:** PHP 8.2+ / Laravel 11
* **Database:** SQLite
* **Frontend:** Vanilla JavaScript / Chart.js / Vite
