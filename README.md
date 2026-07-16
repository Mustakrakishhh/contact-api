# Contact API – тестовое задание (Backend)

REST API для лендинг-презентации разработчика с интеграцией AI (OpenAI).  
Сервис принимает заявки с формы обратной связи, валидирует данные, генерирует AI-ответ, отправляет email-уведомления владельцу и пользователю, логирует запросы и защищает от спама с помощью rate limiting.

---

## 🚀 Быстрый старт (локальный запуск)

### Требования
- PHP 8.4
- Composer
- MySQL 8.0
- Open Server / Herd

### 1. Клонируйте репозиторий
```bash
git clone https://github.com/ваш-логин/contact-api.git
cd contact-api
```

### 2. Установите зависимости
```bash
composer install
```

### 3. Настройте окружение
Скопируйте `.env.example` в `.env`:
```bash
copy .env.example .env   # Windows
# или
cp .env.example .env     # Linux/macOS
```
Отредактируйте `.env`, укажите:
- Настройки базы данных (MySQL)
- SMTP-данные (можно использовать [MailExam](https://mailexam.ru) или драйвер `log`)
- `OPENAI_API_KEY` (если недоступен – сервис использует fallback)

### 4. Сгенерируйте ключ приложения
```bash
php artisan key:generate
```

### 5. Выполните миграции
```bash
php artisan migrate
```

### 6. Запустите встроенный сервер (или используйте Herd/Open Server)
```bash
php artisan serve --port=8000
```
Сервис будет доступен по адресу `http://localhost:8000`.

---

## 🧰 Стек технологий

| Компонент       | Технология                           |
|-----------------|--------------------------------------|
| Backend         | PHP 8.4 + Laravel 13                 |
| База данных     | MySQL                                |
| AI              | OpenAI API                           |
| Очереди         | Database driver (опционально)        |
| Тестирование    | Pest (подготовлены тесты)            |
| Документация    | Swagger / OpenAPI (L5-Swagger)       |
| Хостинг         | Render / Railway / любой PHP-хостинг |

---

## 🧱 Архитектура проекта

Проект построен по **слоистой архитектуре** с разделением ответственности:

- **Controllers** – принимают запросы, валидируют через Form Request, вызывают сервисы.
- **Services** – содержат бизнес-логику:
  - `ContactService` – координация AI, сохранения, отправки писем.
  - `AIService` – интеграция с OpenAI + fallback.
  - `EmailService` – отправка писем владельцу и пользователю.
- **Repositories** – инкапсулируют работу с моделью `Contact` (CRUD, статистика).
- **Middleware** – логирование запросов (`LogRequestMiddleware`), rate limiting (встроенный `throttle`).

**Паттерны:** Repository, Service Layer, Dependency Injection.

**Выбор технологий:** Laravel выбран за скорость разработки, встроенные инструменты (валидация, очереди, кеширование) и богатую экосистему. Файловое кеширование используется для rate limiting – выполняется требование ТЗ без внешних зависимостей.

---

## 📡 API Эндпоинты

Все эндпоинты доступны по префиксу `/api`.

### POST `/contact`
Создание нового обращения.

**Заголовки:** `Content-Type: application/json`

**Тело запроса:**
```json
{
    "name": "Иван Петров",
    "phone": "+79123456789",
    "email": "ivan@example.com",
    "comment": "Отличный сервис! Хочу заказать."
}
```

**Успешный ответ (201 Created):**
```json
{
    "message": "Обращение получено",
    "ai_reply": "Здравствуйте, Иван! Спасибо за ваш интерес. Мы свяжемся с вами в ближайшее время."
}
```

**Ошибки валидации (422):**
```json
{
    "message": "The phone field format is invalid.",
    "errors": {
        "phone": ["The phone field format is invalid."]
    }
}
```

**Превышение лимита запросов (429):**
```json
{
    "message": "Too Many Requests"
}
```

---

### GET `/health`
Проверка статуса сервиса.

**Ответ (200 OK):**
```json
{ "status": "ok" }
```

---

### GET `/metrics`
Статистика обращений.

**Ответ (200 OK):**
```json
{
    "total": 42,
    "today": 3
}
```

---

## 🤖 AI-интеграция

- **Провайдер:** OpenAI API (модель `gpt-3.5-turbo`).
- **Назначение:** автоматическая генерация вежливого ответа на комментарий пользователя.
- **Системный промпт:**
  ```
  Ты вежливый менеджер. Ответь на комментарий до 50 слов. 
  Обращайся по имени. Никаких обещаний, если не уверен.
  ```
- **Graceful Fallback:** при недоступности API (ошибка сети, невалидный ключ, региональные ограничения) сервис возвращает шаблонный ответ, а в лог записывается предупреждение. Это обеспечивает 100% доступность API.

**Пример fallback-ответа:**
```
Спасибо, {name}! Мы получили ваш комментарий и обязательно свяжемся с вами в ближайшее время.
```

---

## 📦 Хранение данных

- **База данных:** MySQL – таблица `contacts` хранит все обращения, включая сгенерированный AI-ответ.
- **Логи:** записываются в файл `storage/logs/laravel.log` (ежедневная ротация). Каждый запрос логируется с IP, методом, эндпоинтом, payload, статусом и длительностью.
- **Rate limiting:** реализован через встроенный `throttle` Laravel с драйвером `file`. Данные о попытках хранятся в `storage/framework/cache/data/` – соответствует требованию ТЗ.

---

## 🧪 Тестирование

Для запуска тестов (Pest):
```bash
php artisan test
```

Написаны тесты для:
- Успешного создания обращения.
- Валидации полей.
- Rate limiting.
- AI fallback (с моком).

---

## 🤝 Что сгенерировано с помощью AI

В процессе разработки использовались следующие AI-инструменты:

| Инструмент | Назначение | Промпт (пример) |
|------------|------------|----------------|
| ChatGPT (OpenAI) | Генерация каркаса сервисов, шаблонов писем, тестов | *"Напиши Laravel сервис для отправки email с использованием Mailable"* |
| GitHub Copilot | Автодополнение кода (валидация, миграции, контроллеры) | – |

Вручную доработаны:
- Механизм graceful fallback для AI.
- Обработка ошибок и логирование.
- Архитектура проекта (слои, репозитории).
- Настройка CORS и .env.

---

## 📂 Структура проекта (ключевые папки)

```
app/
├── Http/
│   ├── Controllers/Api/     # Контроллеры API
│   ├── Middleware/           # Логирование запросов
│   └── Requests/             # Валидация (ContactRequest)
├── Models/                   # Contact (Eloquent)
├── Services/                 # Бизнес-логика (AI, Email, Contact)
└── Repositories/             # Работа с БД (ContactRepository)

config/                       # Конфигурации (cors, mail, openai)
database/migrations/          # Миграции (contacts, sessions, jobs)
routes/
├── api.php                   # Маршруты API
└── web.php                   # (не используется)

storage/logs/                 # Логи запросов и ошибок
tests/                        # Pest-тесты
```

---

## 🔧 Переменные окружения (.env)

Обязательные переменные:
```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=contact_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=hello@example.com
MAIL_OWNER_EMAIL=admin@example.com   # владелец сайта

OPENAI_API_KEY=sk-...
```

Для тестов можно использовать `MAIL_MAILER=log` – письма будут сохраняться в `storage/logs/laravel.log`.

---
