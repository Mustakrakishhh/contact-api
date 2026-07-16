<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Здравствуйте, {{ $contact->name }}!</h1>
    <p>Мы получили ваше обращение:</p>
    <blockquote style="background: #f5f5f5; padding: 10px; border-left: 3px solid #2196F3;">
        {{ $contact->comment }}
    </blockquote>
    <p>Наш менеджер скоро свяжется с вами.</p>
    <p>С уважением,<br>Команда сайта</p>
</div>
</body>
</html>
