<?php

namespace App\Contracts;

interface TransactionalEmailProvider
{
    public function send(
        string $recipientEmail,
        string $recipientName,
        string $subject,
        string $htmlContent,
    ): string;
}
