# 🚌 RAGHD TMS — Transit Management System

A full-stack Transit Management System built with **Laravel** (backend/frontend) and a **Python FastAPI ML service** for delay prediction, all orchestrated with **Docker Compose**.

---

## 📦 Project Structure

```
transit-app/
├── transit-app/      # Laravel application (PHP + Vite/TailwindCSS)
├── ml_service/       # Python FastAPI microservice for ML delay prediction
├── docker-compose.yml
└── README.md
```

---

## ✨ Features

- 🔐 Authentication & role-based access control
- 🗺️ Real-time transit tracking and scheduling
- 🤖 AI-powered delay prediction (ML service via Groq LLM + scikit-learn)
- 📊 Dashboard with analytics and reporting
- 🐳 Docker-ready for easy deployment

---

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.2 & Composer
- Node.js >= 18 & npm
- Python >= 3.10 & pip
- MySQL
- Docker & Docker Compose (optional, for containerized setup)

---

### 🐳 Option A: Run with Docker Compose (Recommended)

```bash
# Copy and configure environment
cp transit-app/.env.example transit-app/.env
# Edit transit-app/.env with your database credentials and API keys

docker-compose up --build
```

The app will be available at `http://localhost:8000` and the ML service at `http://localhost:8001`.

---

### 🛠️ Option B: Run Manually

#### 1. Laravel App

```bash
cd transit-app

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Edit .env with your database credentials and API keys

# Run migrations and seeders
php artisan migrate --seed

# Build frontend assets
npm run dev

# Start the server
php artisan serve
```

#### 2. Python ML Service

```bash
cd ml_service

# Create and activate a virtual environment
python -m venv .venv
source .venv/bin/activate      # On Windows: .venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Start the FastAPI server
uvicorn main:app --host 0.0.0.0 --port 8001 --reload
```

---

## ⚙️ Environment Variables

Copy `transit-app/.env.example` to `transit-app/.env` and fill in the following:

| Variable | Description |
|---|---|
| `DB_DATABASE` | MySQL database name |
| `DB_USERNAME` | MySQL username |
| `DB_PASSWORD` | MySQL password |
| `GROQ_API_KEY` | Your [Groq API](https://console.groq.com/) key |
| `GROK_MODEL` | Groq model to use (default: `llama-3.1-8b-instant`) |
| `ML_API_URL` | URL of the Python ML service (default: `http://localhost:8001`) |

---

## 🧪 Running Tests

```bash
cd transit-app
php artisan test
```

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes (`git commit -m 'feat: add my feature'`)
4. Push to the branch (`git push origin feature/my-feature`)
5. Open a Pull Request

---

## 📄 License

This project is open-sourced under the [MIT License](LICENSE).
