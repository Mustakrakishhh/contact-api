const form = document.querySelector('#contact-form');

if (form) {
    const submitButton = form.querySelector('button[type="submit"]');
    const result = document.querySelector('#form-result');
    const resultTitle = document.querySelector('#form-result-title');
    const resultMessage = document.querySelector('#form-result-message');
    const resultAi = document.querySelector('#form-result-ai');
    const apiState = document.querySelector('#api-state');
    const totalMetric = document.querySelector('#metric-total');

    const clearErrors = () => {
        form.querySelectorAll('.form-field').forEach((field) => field.classList.remove('has-error'));
        form.querySelectorAll('[data-error-for]').forEach((error) => {
            error.textContent = '';
        });
    };

    const showErrors = (errors = {}) => {
        Object.entries(errors).forEach(([field, messages]) => {
            const error = form.querySelector(`[data-error-for="${field}"]`);

            if (!error) {
                return;
            }

            error.textContent = Array.isArray(messages) ? messages[0] : messages;
            error.closest('.form-field')?.classList.add('has-error');
        });
    };

    const showResult = ({ type = 'success', title, message, aiReply = '' }) => {
        result.hidden = false;
        result.className = `form-result${type === 'success' ? '' : ` is-${type}`}`;
        resultTitle.textContent = title;
        resultMessage.textContent = message;
        resultAi.hidden = aiReply === '';
        resultAi.textContent = aiReply === '' ? '' : `AI-ответ: ${aiReply}`;
    };

    const readJson = async (response) => {
        try {
            return await response.json();
        } catch {
            return {};
        }
    };

    const updateServiceStatus = async () => {
        try {
            const [healthResponse, metricsResponse] = await Promise.all([
                fetch(form.dataset.healthUrl, { headers: { Accept: 'application/json' } }),
                fetch(form.dataset.metricsUrl, { headers: { Accept: 'application/json' } }),
            ]);

            if (!healthResponse.ok) {
                throw new Error('Health check failed');
            }

            apiState.classList.add('is-online');
            apiState.querySelector('span').textContent = 'online';

            if (metricsResponse.ok) {
                const metrics = await metricsResponse.json();
                totalMetric.textContent = new Intl.NumberFormat('ru-RU').format(metrics.total ?? 0);
            }
        } catch {
            apiState.classList.add('is-offline');
            apiState.querySelector('span').textContent = 'offline';
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();
        result.hidden = true;
        submitButton.disabled = true;
        submitButton.classList.add('is-loading');

        const payload = Object.fromEntries(new FormData(form).entries());
        payload.phone = payload.phone.replace(/[\s()-]/g, '');

        try {
            const response = await fetch(form.dataset.endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await readJson(response);

            if (response.status === 422) {
                showErrors(data.errors);
                showResult({
                    type: 'error',
                    title: 'Проверьте поля формы',
                    message: 'Некоторые данные заполнены неверно.',
                });

                return;
            }

            if (response.status === 429) {
                showResult({
                    type: 'warning',
                    title: 'Слишком много попыток',
                    message: data.message ?? 'Повторите отправку через минуту.',
                });

                return;
            }

            if (!response.ok) {
                throw new Error(data.message ?? 'Не удалось отправить обращение.');
            }

            showResult({
                type: response.status === 202 ? 'warning' : 'success',
                title: response.status === 202 ? 'Обращение сохранено' : 'Готово — обращение отправлено',
                message: data.message,
                aiReply: data.ai_reply,
            });
            form.reset();
            updateServiceStatus();
        } catch (error) {
            showResult({
                type: 'error',
                title: 'Сервис временно недоступен',
                message: error.message || 'Попробуйте ещё раз немного позже.',
            });
        } finally {
            submitButton.disabled = false;
            submitButton.classList.remove('is-loading');
        }
    });

    updateServiceStatus();
}
