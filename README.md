# Contact API

Backend-сервис для формы обратной связи на лендинге разработчика. API валидирует и сохраняет обращение в MySQL, генерирует ответ через OpenAI и отправляет два письма через Brevo HTTPS API: владельцу сайта и пользователю.

## Демо

- Лендинг: <https://contact-api-production-5283.up.railway.app>
- API-документация: <https://contact-api-production-5283.up.railway.app/docs>
- OpenAPI YAML: <https://contact-api-production-5283.up.railway.app/openapi.yaml>
- Health check: <https://contact-api-production-5283.up.railway.app/api/health>
- GitHub: <https://github.com/Mustakrakishhh/contact-api>

## 1. Как запустить проект

### Требования

- PHP 8.4.1 или новее;
- Composer;
- Node.js 20 или новее и npm;
- MySQL 8 или SQLite;
- расширение PHP `pdo_mysql` для MySQL.

### Установка

```bash
git clone https://github.com/Mustakrakishhh/contact-api.git
cd contact-api
composer install
npm install
```

Создать локальный `.env`.

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

Подготовить приложение и базу данных:

```bash
php artisan key:generate
php artisan migrate
npm run build
```

При использовании Laravel Herd проект доступен по адресу:

```text
https://contact-api.test
```

Без Herd приложение можно запустить командой:

```bash
composer run dev
```

### Настройка базы данных

Для простого локального запуска можно оставить SQLite из `.env.example`.

Пример настройки MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=contact_api
DB_USERNAME=root
DB_PASSWORD=
```

После изменения подключения выполнить:

```bash
php artisan migrate
```

### Основные переменные окружения

```env
APP_NAME="Contact API"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://contact-api.test

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug

BREVO_API_KEY=
BREVO_API_URL=https://api.brevo.com/v3
BREVO_TIMEOUT=10
BREVO_CONNECT_TIMEOUT=3
MAIL_FROM_ADDRESS=verified-sender@example.com
MAIL_FROM_NAME="Contact API"
MAIL_OWNER_EMAIL=owner@example.com
MAIL_OWNER_NAME="Владелец сайта"

OPENAI_API_KEY=
OPENAI_MODEL=gpt-5.6-luna
OPENAI_REQUEST_TIMEOUT=10

CONTACT_RATE_LIMIT=5
CORS_ALLOWED_ORIGINS=http://localhost:5173
```

`MAIL_FROM_ADDRESS` должен совпадать с подтверждённым отправителем в Brevo. Для отправки используется Brevo API key, а не SMTP key.

Настоящий `.env` не добавляется в Git. В Railway значения задаются в разделе `contact-api → Variables`. Для production необходимо установить:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://contact-api-production-5283.up.railway.app

LOG_CHANNEL=stack
LOG_STACK=single,stderr
LOG_LEVEL=info

CORS_ALLOWED_ORIGINS=https://contact-api-production-5283.up.railway.app
```

После изменения Railway Variables требуется redeploy, потому что Laravel кэширует production-конфигурацию.

## 2. Стек технологий

| Область | Технологии |
| --- | --- |
| Backend | PHP 8.4, Laravel 13 |
| API | REST, JSON, Laravel Form Request |
| База данных | MySQL, Eloquent ORM, migrations |
| AI | OpenAI Responses API, пакет `openai-php/laravel` |
| Email | Brevo Transactional Email API, Laravel HTTP Client |
| Frontend | Blade, Tailwind CSS 4, Vanilla JavaScript, Vite 8 |
| Логирование | Laravel Logging, Monolog |
| Rate limiting | Laravel RateLimiter, database cache |
| Тестирование | Pest 4, Laravel HTTP fakes |
| Документация | OpenAPI 3.1, Blade-страница `/docs` |
| Деплой | Railway, Railpack, FrankenPHP, MySQL service |

OpenAI используется для генерации короткого персонального ответа на комментарий пользователя. Brevo вызывается через HTTPS API, поэтому отправка писем не зависит от доступности SMTP-портов хостинга.

## 3. Архитектура

Проект использует слоистую структуру:

```text
app/
├── Contracts/
│   └── TransactionalEmailProvider.php
├── Http/
│   ├── Controllers/Api/
│   ├── Middleware/LogRequestMiddleware.php
│   └── Requests/ContactRequest.php
├── Models/Contact.php
├── Repositories/ContactRepository.php
└── Services/
    ├── AIService.php
    ├── ContactService.php
    ├── EmailService.php
    └── Email/BrevoEmailProvider.php
```

Цепочка обработки обращения:

```text
HTTP request
    → ContactRequest
    → ContactController
    → ContactService
        → AIService
        → ContactRepository
        → EmailService
            → TransactionalEmailProvider
            → BrevoEmailProvider
    → JSON response
```

Использованные подходы:

- **слоистая архитектура** — HTTP, бизнес-логика, данные и внешние интеграции разделены;
- **Service Layer** — `ContactService` управляет полным сценарием обращения;
- **Repository** — работа с `Contact` и статистикой находится в `ContactRepository`;
- **Dependency Injection** — зависимости передаются через конструкторы и методы контроллеров;
- **Dependency Inversion / Strategy** — `EmailService` зависит от интерфейса `TransactionalEmailProvider`, а не от Brevo напрямую;
- **Form Request** — авторизация и валидация HTTP-входа вынесены в `ContactRequest`;
- **Graceful degradation** — сбой AI или email-провайдера не приводит к потере обращения.

Laravel выбран из-за готовых механизмов валидации, dependency injection, Eloquent, middleware, rate limiting, логирования и глобальной обработки исключений. MySQL используется для демонстрации работы с БД. OpenAI и Brevo вынесены в сервисы, чтобы внешние интеграции можно было тестировать и заменять независимо от контроллера.

## 4. Реализация API

API работает с JSON. Для запросов рекомендуется передавать заголовок:

```http
Accept: application/json
```

### `POST /api/contact`

Полный цикл обработки:

```text
валидация → AI-ответ → сохранение в MySQL → два email → JSON-ответ
```

Пример запроса:

```bash
curl -X POST https://contact-api-production-5283.up.railway.app/api/contact \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Иван Петров",
    "phone": "+79991234567",
    "email": "ivan@example.com",
    "comment": "Хочу обсудить разработку API."
  }'
```

Успешный ответ — `201 Created`:

```json
{
  "message": "Обращение получено, копия отправлена на вашу почту.",
  "ai_reply": "Спасибо, Иван! Расскажите подробнее о задаче.",
  "email_status": "sent"
}
```

Если хотя бы одно письмо не принято Brevo, обращение остаётся в базе, а API возвращает `202 Accepted`:

```json
{
  "message": "Обращение сохранено, но почтовый сервис временно недоступен.",
  "ai_reply": "Спасибо, Иван! Мы получили ваше обращение.",
  "email_status": "deferred"
}
```

Правила валидации:

| Поле | Правила |
| --- | --- |
| `name` | обязательная строка, максимум 100 символов |
| `phone` | обязательное значение, необязательный `+` и 10–15 цифр |
| `email` | обязательный email, RFC- и DNS-проверка, максимум 255 символов |
| `comment` | обязательная строка, от 3 до 3000 символов |

Ошибка валидации — `422 Unprocessable Content`:

```json
{
  "message": "The email field must be a valid email address.",
  "errors": {
    "email": [
      "The email field must be a valid email address."
    ]
  }
}
```

Превышение лимита — `429 Too Many Requests`:

```json
{
  "message": "Слишком много обращений. Повторите попытку через минуту."
}
```

### `GET /api/health`

Проверяет доступность приложения:

```json
{
  "status": "ok"
}
```

### `GET /api/metrics`

Возвращает агрегированную статистику без персональных данных:

```json
{
  "total": 42,
  "today": 3
}
```

### Обработка ошибок

- `201` — обращение сохранено, оба письма приняты провайдером;
- `202` — обращение сохранено, но email временно не доставлен;
- `422` — данные не прошли валидацию;
- `429` — превышен rate limit;
- `500` — непредвиденная серверная ошибка.

Глобальный exception handler настроен в `bootstrap/app.php`. Для `api/*` ошибки всегда возвращаются в JSON. В production используется `APP_DEBUG=false`, поэтому stack trace и конфигурация не передаются клиенту. Исключения автоматически записываются в лог.

CORS настраивается через `CORS_ALLOWED_ORIGINS`. В production следует указывать конкретные frontend-домены, а не `*`.

Полная OpenAPI 3.1 спецификация находится в `public/openapi.yaml`. Человекочитаемая версия доступна по адресу `/docs`.

## 5. AI-интеграция

Класс `AIService` вызывает OpenAI Responses API через пакет `openai-php/laravel`. Модель задаётся переменной `OPENAI_MODEL`, поэтому её можно заменить без изменения бизнес-логики.

AI получает имя и комментарий пользователя и формирует короткий ответ для отображения в API и письме.

Используемая системная инструкция:

```text
Ты вежливый менеджер лендинга разработчика. Ответь по-русски, до 50 слов.
Обращайся по имени, не выдумывай сроки и не давай обещаний,
которых нет во входных данных.
```

Пользовательские данные передаются отдельно:

```text
Имя: {name}
Комментарий: {comment}
```

### Graceful fallback

`AIService` перехватывает сетевые ошибки, неверный ключ, превышение лимита и пустой ответ провайдера. В лог записывается только класс исключения, без комментария пользователя.

Fallback-ответ:

```text
Спасибо, {name}! Мы получили ваш комментарий и обязательно свяжемся с вами в ближайшее время.
```

После fallback обработка продолжается: обращение сохраняется в MySQL, затем выполняется попытка отправки email. Поэтому недоступность OpenAI не делает основной API неработоспособным.

## 6. Что сделано с помощью AI

AI использовался как вспомогательный инструмент, а не как замена самостоятельной разработки. Основные задачи, для которых он применялся:

- подготовка черновика README и формулировок для OpenAPI;
- заготовки отдельных Pest-тестов и вариантов тестовых сценариев;
- сверка готового проекта с требованиями задания;
- помощь при анализе deployment logs и поиске причины недоступности SMTP;
- подсказки по проверке переменных окружения, CORS и production-сборки.

Предложенные варианты кода не переносились без проверки: они адаптировались под структуру проекта, установленную версию Laravel и реальные ограничения Railway.

Примеры использованных промптов:

```text
Сверь готовый Laravel API с требованиями тестового задания и перечисли
обязательные пункты, которые ещё нужно проверить.
```

```text
Предложи тестовые сценарии для POST /api/contact:
успешный запрос, ошибка валидации, rate limit и fallback AI.
```

```text
По Railway logs объясни, почему SMTP-письма не отправляются
и какие безопасные альтернативы можно использовать.
```

Вручную проверялись код, конфигурация и реальные deployment logs. В процессе пришлось исправить:

- несовместимость PHP 8.3 с зависимостями Laravel 13 и Symfony 8;
- конфликт Apache MPM в старом Dockerfile;
- некорректное имя Dockerfile;
- отсутствие получателя email из-за незаполненных environment variables;
- недоступность SMTP-портов на бесплатном Railway;
- переход с SMTP на Brevo HTTPS API;
- настройку OpenAI key и проверку AI fallback;
- неработающий rate limiter;
- запись персональных данных в request logs;
- избыточный первоначальный frontend и API Docs;
- несоответствие README фактическим тестам и реализации.

Результаты подсказок проверялись Laravel Pint, Pest-тестами, парсером OpenAPI YAML и production-сборкой Vite.

## 7. Хранение данных

### Обращения

Обращения хранятся в таблице `contacts`:

| Поле | Назначение |
| --- | --- |
| `id` | идентификатор обращения |
| `name` | имя пользователя |
| `phone` | телефон |
| `email` | email |
| `comment` | текст обращения |
| `ai_response` | ответ OpenAI или fallback |
| `sent_to_user` | была ли копия письма принята email-провайдером |
| `created_at`, `updated_at` | время создания и изменения |

Схема создаётся миграцией `2026_07_16_114028_create_contacts_table.php`. Работа с данными выполняется через Eloquent-модель `Contact` и `ContactRepository`.

### Логи

`LogRequestMiddleware` логирует каждый API-запрос и ответ:

- request ID;
- HTTP-метод и endpoint;
- названия переданных полей;
- SHA-256 хеш IP;
- статус ответа;
- длительность обработки.

Телефон, email, комментарий и полный request body в лог не записываются.

Локальный файл логов:

```text
storage/logs/laravel.log
```

Для Railway используется:

```env
LOG_CHANNEL=stack
LOG_STACK=single,stderr
```

Это одновременно пишет в `storage/logs/laravel.log` и выводит записи в Railway Deployment Logs. Файловая система контейнера временная, поэтому Deployment Logs являются основным способом просмотра истории после redeploy.

### Rate limiting

Лимит задаётся переменной:

```env
CONTACT_RATE_LIMIT=5
```

Идентификатором клиента служит IP-адрес. `RateLimiter` хранит счётчики в стандартном Laravel cache store. При `CACHE_STORE=database` записи находятся в таблице `cache`, созданной стандартной миграцией Laravel.

При превышении лимита `POST /api/contact` возвращает `429`.

### Статистика

Отдельная таблица или JSON-файл статистики не используется. `GET /api/metrics` рассчитывает значения непосредственно из `contacts`:

- `total` — общее количество записей;
- `today` — количество записей с сегодняшней датой создания.

Так статистика всегда соответствует фактически сохранённым обращениям и не требует отдельной синхронизации.

## Тестирование

Запуск всех тестов:

```bash
php artisan test --compact
```

Тесты проверяют:

- успешное получение ответа через OpenAI Responses API;
- graceful fallback при недоступности OpenAI;
- успешный полный цикл обращения;
- сохранение записи в базе;
- отправку двух уведомлений;
- `202` при ошибке email-провайдера;
- валидацию `422`;
- rate limiting `429`;
- payload и заголовок авторизации Brevo;
- отсутствие реальных внешних HTTP-запросов в тестах;
- отображение лендинга и страницы документации.

Форматирование PHP:

```bash
vendor/bin/pint --format agent
```

## Деплой на Railway

1. Подключить GitHub-репозиторий как Application Service.
2. Добавить MySQL service в тот же Railway project.
3. Передать `DB_*` через Railway reference variables.
4. Добавить `APP_KEY`, Brevo, OpenAI, CORS и logging variables.
5. Выполнить deploy — Railpack установит PHP и Composer, выполнит migrations и соберёт Vite assets.
6. Проверить `/api/health`, `/docs`, `/openapi.yaml` и тестовую отправку формы.
