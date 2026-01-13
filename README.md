# ArtExpo – Domain-Driven Exhibition Management with AI-Assisted Social Media

ArtExpo is a Laravel-based system originating from real-world museum work.
Its core idea is to model an **exhibition as a structured domain object**
— not as a single website — and distribute its content consistently across
multiple channels such as websites, press, social media, and external displays.

This repository demonstrates how **AI features can be integrated safely and
production-oriented into a Laravel application**, using real exhibition data
as context.

---

## 🏛 Project Background

In a museum context, an exhibition is more than a web page.

An exhibition consists of:
- a title, dates, artists, galleries
- curated texts and metadata
- images and views with credits
- press and communication material
- multiple output targets (web, print, social media, signage)

The goal of this project is to treat an exhibition as a **central data object**
and generate different representations from it — instead of maintaining
separate, disconnected content silos.

---

## 🤖 AI-Assisted Social Media Posts (Main Focus)

On top of this domain model, the project includes an **AI-assisted social media
post generator**.

It uses structured exhibition and image data to generate **draft posts** for
different social networks.

### Key characteristics

- AI generates **drafts only** (human-in-the-loop, no auto-publishing)
- Posts are created via **queued background jobs** (`ShouldQueue`)
- Network-specific constraints are enforced in Laravel
  - e.g. X/Twitter ≤ 280 characters
- Domain guards ensure posts are only generated for eligible images
- Generated posts are stored and reviewable before publishing

This reflects a production-ready mindset:
> AI proposes — the application decides.

---

## 🧪 Experimental RAG Work (Separate Branch)

This repository also contains an **experimental branch**: experiment/rag-fun-facts


In this branch, the project explores **Retrieval-Augmented Generation (RAG)**
to enrich social media posts with factual “fun facts” about artists and artworks.

Characteristics of the RAG approach:
- SQLite + FTS5 based retrieval
- Whitelisted sources only
- Clear separation between retrieval and generation
- Explicit anti-hallucination rules

The RAG work is intentionally **not part of `main`** and serves as an
exploratory and learning-oriented prototype.
The `main` branch represents the stable, production-oriented implementation.

---

## 🎨 Core Features

### Exhibition as a Domain Object
- Structured modeling of exhibitions, images and metadata
- Central source of truth for multiple output channels

### AI-Assisted Social Media Drafts
- Context-aware post generation for exhibition views
- Network-specific rules and constraints
- Queue-based processing for scalability

### CMS & Administration
- Exhibition and image management
- Secure authentication (Laravel Fortify)
- Admin-only workflows where required

---

## 🛠 Tech Stack

- **Framework**: Laravel 12
- **AI Integration**: OpenAI API
- **Background Jobs**: Laravel Queues
- **Frontend**: Livewire + Flux UI Components
- **Database**: SQLite (development), MySQL/PostgreSQL (production)
- **Authentication**: Laravel Fortify
- **Styling**: Tailwind CSS
- **Testing**: Pest PHP

---

## 🧠 Architectural Principles

- Domain-driven thinking over page-driven CMS logic
- AI as an assistant, not an authority
- Clear separation between:
  - data retrieval
  - AI generation
  - business rules
- Human review before external publication

---

## 🚀 Installation (Development)

```bash
git clone https://github.com/DEIN-USERNAME/artexpo.git
cd artexpo

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate --seed

php artisan serve
npm run dev


