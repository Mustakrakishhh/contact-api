<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Документация Contact API">
    <title>API Docs — {{ config('app.name', 'Contact API') }}</title>

    @fonts
    @vite('resources/css/app.css')
</head>
<body>
    <header class="site-header">
        <div class="container header-row">
            <a class="brand" href="{{ route('home') }}">Contact API</a>
            <nav class="site-nav" aria-label="Навигация документации">
                <a href="{{ route('home') }}">На главную</a>
                <a href="{{ asset('openapi.yaml') }}">openapi.yaml</a>
            </nav>
        </div>
    </header>

    <main class="docs-main container">
        <section class="docs-intro">
            <p class="intro-label">OpenAPI 3.1</p>
            <h1>Документация API</h1>
            <p>
                API принимает обращения с формы, сохраняет их в базе данных,
                генерирует AI-ответ и отправляет email-уведомления.
            </p>
            <a class="button button-secondary" href="{{ asset('openapi.yaml') }}">Открыть исходную спецификацию</a>
        </section>

        <section class="docs-section">
            <div class="docs-section-title">
                <span class="method method-post">POST</span>
                <div>
                    <h2>/api/contact</h2>
                    <p>Создание обращения</p>
                </div>
            </div>

            <div class="docs-card">
                <h3>Поля запроса</h3>
                <div class="params-table">
                    <div class="params-row params-head">
                        <span>Поле</span><span>Тип</span><span>Описание</span>
                    </div>
                    <div class="params-row">
                        <code>name</code><span>string</span><span>Имя, до 100 символов</span>
                    </div>
                    <div class="params-row">
                        <code>phone</code><span>string</span><span>Телефон, 10–15 цифр</span>
                    </div>
                    <div class="params-row">
                        <code>email</code><span>string</span><span>Корректный email</span>
                    </div>
                    <div class="params-row">
                        <code>comment</code><span>string</span><span>Комментарий, 3–3000 символов</span>
                    </div>
                </div>
            </div>

            <div class="docs-columns">
                <div class="docs-card">
                    <h3>Пример запроса</h3>
                    <pre><code>{
  "name": "Иван",
  "phone": "+79991234567",
  "email": "ivan@example.com",
  "comment": "Хочу обсудить разработку API"
}</code></pre>
                </div>

                <div class="docs-card">
                    <h3>Успешный ответ · 201</h3>
                    <pre><code>{
  "message": "Обращение получено",
  "ai_reply": "Спасибо за обращение!",
  "email_status": "sent"
}</code></pre>
                </div>
            </div>

            <div class="docs-card">
                <h3>Коды ответа</h3>
                <ul class="response-list">
                    <li><code>201</code><span>Обращение сохранено, письма отправлены</span></li>
                    <li><code>202</code><span>Обращение сохранено, email временно недоступен</span></li>
                    <li><code>422</code><span>Ошибка валидации данных</span></li>
                    <li><code>429</code><span>Превышен лимит запросов</span></li>
                    <li><code>500</code><span>Непредвиденная ошибка сервера</span></li>
                </ul>
            </div>
        </section>

        <section class="docs-section">
            <div class="docs-section-title">
                <span class="method method-get">GET</span>
                <div>
                    <h2>/api/health</h2>
                    <p>Проверка доступности сервиса</p>
                </div>
            </div>
            <div class="docs-card docs-small-response">
                <pre><code>{ "status": "ok" }</code></pre>
            </div>
        </section>

        <section class="docs-section">
            <div class="docs-section-title">
                <span class="method method-get">GET</span>
                <div>
                    <h2>/api/metrics</h2>
                    <p>Общая статистика обращений</p>
                </div>
            </div>
            <div class="docs-card docs-small-response">
                <pre><code>{ "total": 42, "today": 3 }</code></pre>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-row">
            <span>Contact API · OpenAPI 3.1</span>
            <a href="{{ route('home') }}">Вернуться на главную</a>
        </div>
    </footer>
</body>
</html>
