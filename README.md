# EnergexAI Full-Stack Screening Assessment Solution

This repository implements the full-stack assessment with:

- **Lumen API (PHP)** – REST API with JWT authentication, MySQL, and Redis caching.
- **Node.js Cache Service (TypeScript)** – cache-first API layer backed by Redis + MySQL.
- **MySQL** – relational database for users and posts.
- **Redis** – caching layer for performance.
- **Frontend (Vite + React)** – simple UI that consumes the API.
- **Docker Compose** – orchestrates the full stack.
- **CI/CD** – GitHub Actions pipeline to run automated tests.

---

## 🚀 Installation and Setup

### 1. Clone the repository
```bash
git clone https://github.com/yourusername/energex-assessment.git
cd energex-assessment
```

### 2. Start the stack with Docker
```bash
docker compose up -d --build
```

This launches **MySQL**, **Redis**, **Lumen API**, **Node.js cache service**, and the **Frontend**.

---

## 🔗 Services

- **Frontend**  
  URL: [http://localhost:5173](http://localhost:5173)  
  React app for registration, login, and posts UI.

- **Lumen API (PHP)**  
  URL: [http://localhost:8000](http://localhost:8000)  
  Endpoints:
  - `POST /api/register` → register new user  
  - `POST /api/login` → login, get JWT token  
  - `GET /api/posts` → list posts (cached in Redis)  
  - `POST /api/posts` → create post (JWT required)  
  - `PUT /api/posts/:id` → update post (JWT required, owner only)  
  - `DELETE /api/posts/:id` → delete post (JWT required, owner only)  

- **Node.js Cache Service (TypeScript)**  
  URL: [http://localhost:4000](http://localhost:4000)  
  Endpoints:
  - `GET /cache/posts` → fetch all posts (Redis first, then DB)  
  - `GET /cache/posts/:id` → fetch post by ID (Redis first, then DB)  

- **MySQL**  
  Host: `localhost`  
  Port: `3308`  
  Database: `energex`  
  User: `app`  
  Password: `app`  

- **Redis**  
  Host: `localhost`  
  Port: `6379`  

---

## 🧪 Usage Demo (with curl)

### 1. Register a user
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Tester","email":"test@example.com","password":"secret123"}'
```

### 2. Login to get JWT
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"secret123"}'
```

Response contains `"token": "<JWT>"`.

### 3. Create a post (requires JWT)
```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <JWT>" \
  -d '{"title":"Hello","content":"World"}'
```

### 4. Fetch posts via Node.js cache
```bash
curl http://localhost:4000/cache/posts
curl http://localhost:4000/cache/posts/1
```

First call queries MySQL + stores in Redis, subsequent calls are instant from cache.

---

## 🧪 Running Tests

Inside the **Lumen API**:

```bash
cd lumen-api
composer install
vendor/bin/phpunit
```

Tests cover:
- Registration & login
- Auth-protected post creation
- Validation errors
- Ownership checks for update/delete
- Cache invalidation

---

## ⚙️ CI/CD with GitHub Actions

This repo includes a GitHub Actions workflow under `.github/workflows/ci.yml` that:

- Spins up MySQL service
- Installs PHP + dependencies
- Runs PHPUnit tests automatically on push/PR

You can adapt it for GitLab CI if needed.

---

## 📝 Notes

- JWT secret is set in `.env`:  
  ```env
  JWT_SECRET=change_me
  ```
  Replace with a secure value before deploying.

- Cache TTL defaults:
  - Lumen API: `CACHE_TTL=60` seconds
  - Node.js Cache: `CACHE_TTL=120` seconds

- In CI (GitHub Actions/GitLab), you can set `CACHE_DRIVER=array` to skip Redis and keep pipelines fast.

---

## 🎥 Loom Video Guide

When recording your Loom demo:
1. Show containers starting with `docker compose up`.
2. Walk through the **frontend** at [http://localhost:5173](http://localhost:5173).
3. Demo registration, login, creating a post in the frontend.
4. Show cached results via Node service (`/cache/posts`).
5. Run tests (`vendor/bin/phpunit`) and explain CI/CD setup.
