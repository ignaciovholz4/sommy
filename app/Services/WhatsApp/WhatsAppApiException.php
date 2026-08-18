<?php

namespace App\Services\WhatsApp;

use Exception;

class WhatsAppApiException extends Exception
{
    public string $errorCode;
    public array $errorData;

    public function __construct(string $message, string $errorCode = '', array $errorData = [])
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->errorData = $errorData;
    }

    /**
     * 131047: fuera de la ventana de 24 h (requiere plantilla).
     */
    public function isSessionExpired(): bool
    {
        return $this->errorCode === '131047';
    }
}
