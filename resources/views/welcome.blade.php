<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Тестовый Laravel backend-проект с REST API, AI-интеграцией и отправкой email через Brevo.">
    <title>{{ config('app.name', 'Contact API') }} — Laravel backend</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="container header-row">
            <a class="brand" href="#top">Contact API</a>
            <nav class="site-nav" aria-label="Основная навигация">
                <a href="#about">О проекте</a>
                <a href="{{ route('docs') }}">Документация</a>
                <a href="#contact">Форма связи</a>
            </nav>
        </div>
    </header>

    <main id="top">
        <section class="intro container">
            <p class="intro-label">Тестовое backend-задание</p>
            <h1>Contact API на Laravel</h1>
            <p class="intro-text">
                Небольшой сервис для обработки обращений с сайта. Запрос валидируется,
                сохраняется в MySQL, обрабатывается с помощью AI и отправляется по email.
            </p>
            <div class="intro-actions">
                <a class="button button-primary" href="#contact">Проверить форму</a>
                <a class="button button-secondary" href="{{ route('docs') }}">Открыть API Docs</a>
            </div>
        </section>

        <section class="section container" id="about">
            <h2>О проекте</h2>
            <div class="info-grid">
                <article class="card">
                    <div class="card-header">
                        <h3>Состояние сервиса</h3>
                        <span class="api-state" id="api-state">
                            <i aria-hidden="true"></i>
                            <span>проверка...</span>
                        </span>
                    </div>
                    <dl class="stats">
                        <div>
                            <dt id="metric-total">—</dt>
                            <dd>обращений сохранено</dd>
                        </div>
                        <div>
                            <dt>5 / мин</dt>
                            <dd>лимит запросов</dd>
                        </div>
                    </dl>
                </article>

                <article class="card">
                    <h3>Стек</h3>
                    <ul class="plain-list">
                        <li>PHP 8.4 и Laravel 13</li>
                        <li>MySQL для хранения обращений</li>
                        <li>OpenAI API с fallback-ответом</li>
                        <li>Brevo API для отправки писем</li>
                    </ul>
                </article>
            </div>

            <article class="card endpoints-card">
                <h3>Основные эндпоинты</h3>
                <div class="endpoint-list">
                    <div><code>POST</code><span>/api/contact</span><small>Отправка обращения</small></div>
                    <div><code>GET</code><span>/api/health</span><small>Проверка сервиса</small></div>
                    <div><code>GET</code><span>/api/metrics</span><small>Количество обращений</small></div>
                </div>
                <p class="architecture-note">
                    Архитектура: Controller → Service → Repository → MySQL / OpenAI / Brevo.
                </p>
            </article>
        </section>

        <section class="section section-muted" id="contact">
            <div class="container contact-layout">
                <div class="contact-description">
                    <p class="section-label">Демонстрация API</p>
                    <h2>Давайте обсудим задачу</h2>
                    <p>
                        Форма отправляет JSON в <code>POST /api/contact</code>. После обработки
                        здесь появится результат и ответ, сгенерированный AI.
                    </p>
                </div>

                <form
                    class="contact-form"
                    id="contact-form"
                    data-endpoint="{{ route('api.contact') }}"
                    data-health-url="{{ route('api.health') }}"
                    data-metrics-url="{{ route('api.metrics') }}"
                    novalidate
                >
                    <div class="form-row">
                        <label class="form-field">
                            <span>Имя</span>
                            <input name="name" type="text" maxlength="100" autocomplete="name" placeholder="Алексей" required>
                            <small class="field-error" data-error-for="name"></small>
                        </label>

                        <label class="form-field">
                            <span>Телефон</span>
                            <input name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+7 999 123-45-67" required>
                            <small class="field-error" data-error-for="phone"></small>
                        </label>
                    </div>

                    <label class="form-field">
                        <span>Email</span>
                        <input name="email" type="email" maxlength="255" autocomplete="email" placeholder="mail@example.com" required>
                        <small class="field-error" data-error-for="email"></small>
                    </label>

                    <label class="form-field">
                        <span>Комментарий</span>
                        <textarea name="comment" minlength="3" maxlength="3000" rows="5" placeholder="Кратко опишите задачу" required></textarea>
                        <small class="field-error" data-error-for="comment"></small>
                    </label>

                    <button class="button button-primary submit-button" type="submit">
                        <span class="submit-button__label">Отправить</span>
                        <span class="submit-button__loader" aria-hidden="true"></span>
                    </button>

                    <div class="form-result" id="form-result" role="status" aria-live="polite" hidden>
                        <strong id="form-result-title"></strong>
                        <p id="form-result-message"></p>
                        <blockquote id="form-result-ai" hidden></blockquote>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-row">
            <span>Contact API · Laravel 13</span>
            <div>
                <a href="https://github.com/Mustakrakishhh/contact-api" rel="noreferrer" target="_blank">GitHub</a>
                <a href="{{ route('docs') }}">API Docs</a>
            </div>
        </div>
    </footer>
</body>
</html>
