# ArtExpo - Laravel Exhibition Management System

Ein modernes Laravel-basiertes Content Management System als Microservice für die Verwaltung von Kunstausstellungen und Bildern.

## 🎨 Features

- **Ausstellungsverwaltung**: Erstellen, bearbeiten und verwalten von Kunstausstellungen
- **Bildverwaltung**: Upload und Organisation von Kunstwerken mit Metadaten
- **Benutzerauthentifizierung**: Sichere Anmeldung mit Laravel Fortify
- **Admin-Bereich**: Administrativer Zugang für die Verwaltung
- **Responsive Design**: Optimiert für Desktop und mobile Geräte
- **Livewire Integration**: Dynamische Benutzeroberflächen ohne komplexes JavaScript

## 🛠 Tech Stack

- **Framework**: Laravel 12
- **Frontend**: Livewire + Flux UI Components
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Fortify
- **Styling**: Tailwind CSS (via Flux)
- **Build Tool**: Vite
- **Testing**: Pest PHP

## 📋 Voraussetzungen

- PHP >= 8.2
- Composer
- Node.js >= 16
- MySQL/PostgreSQL
- Git

## 🚀 Installation

1. **Repository klonen**
   ```bash
   git clone https://github.com/DEIN-USERNAME/artexpo.git
   cd artexpo
   ```

2. **Dependencies installieren**
   ```bash
   composer install
   npm install
   ```

3. **Environment konfigurieren**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Datenbank konfigurieren**
   - Bearbeite `.env` und setze deine Datenbankverbindung
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=artexpo
   DB_USERNAME=dein_username
   DB_PASSWORD=dein_password
   ```

5. **Datenbank migrieren und seeden**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Storage Link erstellen**
   ```bash
   php artisan storage:link
   ```

7. **Frontend Assets kompilieren**
   ```bash
   npm run build
   # Oder für Development:
   npm run dev
   ```

8. **Server starten**
   ```bash
   php artisan serve
   ```

Die Anwendung ist nun unter `http://localhost:8000` erreichbar.

## 📊 Datenbank Schema

### Exhibitions (Ausstellungen)
- `id` - Primary Key
- `title` - Titel der Ausstellung
- `description` - Beschreibung
- `start_date` - Startdatum
- `end_date` - Enddatum
- `location` - Veranstaltungsort
- `created_at` / `updated_at` - Timestamps

### Images (Bilder)
- `id` - Primary Key
- `exhibition_id` - Referenz zur Ausstellung
- `title` - Bildtitel
- `description` - Bildbeschreibung
- `artist` - Künstlername
- `url` - Bild-URL
- `created_at` / `updated_at` - Timestamps

## 🧪 Tests

Das Projekt verwendet Pest PHP für Tests:

```bash
# Alle Tests ausführen
./vendor/bin/pest

# Spezifische Test-Suite
./vendor/bin/pest --filter ExhibitionTest
```

## 📁 Projektstruktur

```
app/
├── Http/Controllers/     # HTTP Controller
├── Http/Requests/       # Form Request Validation
├── Livewire/           # Livewire Components
├── Models/             # Eloquent Models
└── Providers/          # Service Providers

resources/
├── views/              # Blade Templates
│   ├── exhibitions/    # Ausstellungsansichten
│   ├── images/         # Bildansichten
│   └── livewire/       # Livewire Views
└── css/               # Stylesheets

database/
├── factories/         # Model Factories
├── migrations/        # Database Migrations
└── seeders/          # Database Seeders

tests/
├── Feature/          # Feature Tests
└── Unit/            # Unit Tests
```

## 🔐 Standard-Benutzer

Nach dem Seeding sind folgende Testbenutzer verfügbar:

- **Admin**: admin@example.com / password
- **User**: user@example.com / password

## 🚀 Deployment

Für Production-Deployment:

```bash
# Optimierungen
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Assets für Production
npm run build
```

## 🤝 Contributing

1. Fork das Projekt
2. Erstelle einen Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Committe deine Änderungen (`git commit -m 'Add some AmazingFeature'`)
4. Push zum Branch (`git push origin feature/AmazingFeature`)
5. Öffne einen Pull Request

## 📄 License

Dieses Projekt ist unter der MIT License lizenziert. Siehe [LICENSE](LICENSE) Datei für Details.

## 🐛 Bug Reports & Feature Requests

Bitte nutze die [GitHub Issues](https://github.com/DEIN-USERNAME/artexpo/issues) für Bug Reports und Feature Requests.

---

Entwickelt mit ❤️ und Laravel