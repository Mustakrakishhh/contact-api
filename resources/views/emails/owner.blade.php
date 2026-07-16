<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .field { margin-bottom: 10px; }
        .label { font-weight: bold; }
        .ai-response { background: #f9f9f9; padding: 10px; border-left: 3px solid #4CAF50; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Новое обращение с сайта</h1>
    <p>Поступило новое сообщение от пользователя.</p>

    <div class="field">
        <span class="label">Имя:</span> {{ $contact->name }}
    </div>
    <div class="field">
        <span class="label">Телефон:</span> {{ $contact->phone }}
    </div>
    <div class="field">
        <span class="label">Email:</span> {{ $contact->email }}
    </div>
    <div class="field">
        <span class="label">Комментарий:</span><br>
        {{ $contact->comment }}
    </div>

    @if($contact->ai_response)
        <div class="ai-response">
            <strong>Ответ, сгенерированный AI:</strong><br>
            {{ $contact->ai_response }}
        </div>
    @endif

    <hr>
    <p style="color: #888;">С уважением,<br>Ваш сайт</p>
</div>
</body>
</html>
